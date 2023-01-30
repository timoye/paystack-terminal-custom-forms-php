<?php

namespace Timoye\PaystackTerminal;


use Carbon\Carbon;

class PaystackTerminal
{

    private $path;
    private $secret_key;
    private $reference;
    private $amount;
    private $amount_kobo;

    public function __construct($secret_key)
    {
        $this->secret_key=$secret_key;
        $this->path="/paystack/biller";
    }

    public function authCheck($request)
    {
        $header_bearer_auth = $request->header('Authorization');
        $date = $request->header('date');
        $method = $request->method();

        $header_array = explode(' ', $header_bearer_auth);
        $header_auth = $header_array[1] ?? 'token_from_paystack';

        $request_body = json_encode($request->all(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $body_to_hash = $method != 'GET' ? $request_body : '';
        $hashed_body = $this->createMD5Hash($body_to_hash);

        $hashed=$this->createHMACHash([$method,$this->path,$date,$hashed_body]);

        if ($hashed != $header_auth) {
            throw new \Exception('Auth Check failed');
        }
        return $this;
    }

    public function createHMACHash($array)
    {
        $to_encode=implode("\n", $array);
        $to_encode = trim($to_encode);
        return hash_hmac('sha512', $to_encode, $this->secret_key, false);
    }

    public function createMD5Hash($body_to_hash){
        return md5($body_to_hash);
    }

    public function generateReference()
    {
        $this->reference = ReferenceGenerator::getHashedToken();
        return $this;
    }

    public function formFieldResponse($fields, $title){
        return [
            'action' => 'collect',
            'request_id' => $this->reference,
            'title' => $title,
            'fields' => $fields
        ];
    }


    //process form request

    public function setAmount($naira_amount){
        $this->amount = $naira_amount;
        $this->amount_kobo = $this->amount * 100;
        return $this;
    }
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    public function processFormResponse($fields)
    {
        return [
            'action' => 'process',
            'fields' => $fields,
            'amount' => $this->amount_kobo,
            'metadata' => [
                'reference' => $this->reference
            ]
        ];
    }

    public function getHeaderArray($body)
    {
        $date = Carbon::now()->toRfc7231String();
        $new_body = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $hashed_body = $this->createMD5Hash($new_body);
        $hashed=$this->createHMACHash([$date,$hashed_body]);
        return [
            "date" => $date,
            'authorization' => "Bearer $hashed",
            'Content-Type' => 'application/json'
        ];
    }

}