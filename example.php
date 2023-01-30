<?php

require_once 'vendor/autoload.php';

use Timoye\Paystack\PaystackTerminal;

function formFields(Request  $request){
    $secret_key='xxxx -xxx';//get key from env or config
    try{
        $form_fields_response = (new PaystackTerminal($secret_key))
            ->authCheck($request)
            ->generateReference()
            ->formFieldResponse();
    }catch(\Exception $e){
        return [
            'status'=>'fail',
            'details'=>$e->getMessage()
        ];
    }
    return response()->json($form_fields_response)
        ->withHeaders((new PaystackTerminal($secret_key))->getHeaderArray($form_fields_response));

}

