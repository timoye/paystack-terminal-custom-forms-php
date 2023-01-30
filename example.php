<?php

require_once 'vendor/autoload.php';

use Timoye\PaystackTerminal\PaystackTerminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

function formFields(Request $request){
    $secret_key='xxxx -xxx';//get key from env or config
    try{
        $fields=getFields();
        $title='Get Student Details';
        $form_fields_response = (new PaystackTerminal($secret_key))
            ->authCheck($request)
            ->generateReference()
            ->formFieldResponse($fields,$title);
    }catch(\Exception $e){
        return [
            'status'=>'fail',
            'details'=>$e->getMessage()
        ];
    }
    return Response::json($form_fields_response)
        ->withHeaders((new PaystackTerminal($secret_key))->getHeaderArray($form_fields_response));

}

function getFields(){
    return [[
        'type' => 'numeric',
        'id' => 'student_id',
        'title' => 'Student ID',
    ], [
        'type' => 'numeric',
        'id' => 'amount',
        'title' => 'Amount',]
    ];
}

function processForm(Request $request){
    $secret_key='xxxx -xxx';//get key from env or config
    try{
        $process_form_response = (new PaystackTerminal($secret_key))
            ->authCheck($request)
            ->setData($request)
            ->setAmount($naira)
            ->setData($request)
            ->processFormResponse();
    }catch(\Exception $e){
        return [
            'status'=>'fail',
            'details'=>$e->getMessage()
        ];
    }
    return Response::json($process_form_response)
        ->withHeaders((new PaystackTerminal($secret_key))->getHeaderArray($process_form_response));

}

