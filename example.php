<?php

require_once 'vendor/autoload.php';

use Timoye\PaystackTerminal\PaystackTerminal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

/*
 * This method will return the response with the list of input fields to be displayed for the customer to fill
 * GET /paystack/biller
 */
function formFields(Request $request){
    $secret_key='xxxx -xxx';//get key from env or config
    try{
        $fields=getFormFields();
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

function getFormFields(){
    //you can add as many inputs to collect data from customer using the POS Terminal
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

/*
 * This method will receive the response of customer inputs to the input fields displayed above
 * It will also return a Review page, You can pass any details you want to the review page.
 * You can pass records from your database to display on review, for customer to confirm his records
 * POST /paystack/biller
 */
function processForm(Request $request){
    $secret_key='xxxx -xxx';//get key from env or config
    try{
        $data_entered_in_fields=$request->fields;
        $student=getStudentDetails();
        $amount=getAmount($data_entered_in_fields,$student);
        $fields=buildFields($student,$amount);

        $process_form_response = (new PaystackTerminal($secret_key))
            ->authCheck($request)
            ->setAmount($amount)
            ->setReference($request->request_id)
            ->processFormResponse($fields);
    }catch(\Exception $e){
        return [
            'status'=>'fail',
            'details'=>$e->getMessage()
        ];
    }
    return Response::json($process_form_response)
        ->withHeaders((new PaystackTerminal($secret_key))->getHeaderArray($process_form_response));

}

function getStudentDetails(){
    //$student=$this->getStudentDetailsFromDB();
    //if no student exist with $this->student_id, throw exception
    //select * from students where id = $data_entered_in_fields['student_id'];
    //return Student::where('id',$data_entered_in_fields['student_id'])->first();
    return ['id'=>$this->student_id,'name'=>'Timothy Soladoye','level'=>'400 Level','fees'=>35000];//sample student data
}

function getAmount($data,$student){
    $amount=$data['amount']; //if you passes amount in the form_field
    $amount=$student['fees']; //if you want to get the amount from your database
    //this is the amount that will be charged to the customer
    return $amount;
}

function buildFields($student,$amount){
    //you can add as many fields as you want, this will be displayed on the POS Terminals for confirmation before payment
   return [[
        'title' => 'Student ID',
        'value' => $student['student_id']
    ], [
        'title' => 'Student Name',
        'value' =>  $student['name']
    ], [
        'title' => 'Student Current Level',
        'value' =>  $student['level']
    ], [
        'title' => 'Amount',
        'value' => number_format($amount, 2)
    ]
   ];
}

