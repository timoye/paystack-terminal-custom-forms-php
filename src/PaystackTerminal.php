<?php

namespace Timoye\Paystack;


use Carbon\Carbon;

class PaystackTerminal
{

    private $path;
    private $secret_key;
    private $reference;
    private $student_id;
    private $student;
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


    //process form request
    public function setData($request)
    {
        $this->student_id = $request->fields['student_id'];
        $this->amount = $request->fields['amount'] ?? 5000; //you may decide not to collect amount from form and pass it here
        $this->amount_kobo = $this->amount * 100;
        $this->reference = $request->request_id;

        //$student=$this->getStudentDetailsFromDB();
        //if no student exist with $this->student_id, throw exception
        $this->student=['id'=>$this->student_id,'name'=>'Timothy Soladoye','level'=>'400 Level'];//sample student data
        return $this;
    }

    public function processFormResponse()
    {
        return [
            'action' => 'process',
            'fields' => [[
                'title' => 'Student ID',
                'value' => $this->student_id
            ], [
                'title' => 'Student Name',
                'value' => $this->student['name']
            ], [
                'title' => 'Student Current Level',
                'value' => $this->student['level']
            ], [
                'title' => 'Amount',
                'value' => number_format($this->amount, 2)
            ]],
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