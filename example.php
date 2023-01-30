<?php

require_once 'vendor/autoload.php';

use Timoye\Paystack\PaystackTerminal;

function formFields(Request $request){
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

function processForm(Request $request){
    $secret_key='xxxx -xxx';//get key from env or config
    try{
        $process_form_response = (new PaystackTerminal($secret_key))
            ->authCheck($request)
            ->setData($request)
            ->processFormResponse();
    }catch(\Exception $e){
        return [
            'status'=>'fail',
            'details'=>$e->getMessage()
        ];
    }
    return response()->json($process_form_response)
        ->withHeaders((new PaystackTerminal($secret_key))->getHeaderArray($process_form_response));

}

