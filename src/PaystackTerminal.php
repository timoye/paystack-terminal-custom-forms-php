<?php

namespace Timoye\Paystack;


class PaystackTerminal
{

    private $path;
    private $secret_key;
    private $reference;

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
        /*        $to_encode = "$method
$this->path
$date
$hashed_body";*/
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

    public function formFieldResponse(){
        return [
            'action' => 'collect',
            'request_id' => $this->reference,
            'title' => 'Get Student Details',
            'fields' => [[
                'type' => 'numeric',
                'id' => 'student_id',
                'title' => 'Student ID',
            ], [
                'type' => 'numeric',
                'id' => 'amount',
                'title' => 'Amount',]
            ]
        ];
    }
}