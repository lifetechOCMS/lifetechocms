<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbResponse;
use Lt\Modules\MdLt\Services\TbResponseService;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtService;

// error_reporting(E_ALL);       // Report all errors and warnings
// ini_set('display_errors', 1); // Show errors in the browser
// ini_set('display_startup_errors', 1); // Show startup errors too

// Example: a deliberate error
// echo $undefined_variable;
// error_reporting(1); 
class TbResponseController
{
    
    public function index(){

        $responseModel = new TbResponse();
        $responseModel->select()->get();

        return $responseModel->responseJson();
    }

        
    public function store(){
        
        $responseModel = new TbResponse();  
        $responseModel->processRequest();
        
        $responseGroup = $responseModel->responseGroup;
        $responseCode = $responseModel->responseCode;
        $grouping = explode('-', $responseGroup);
        $start = $grouping[0];
        $end = $grouping[1];
        
        if($responseCode < $start || $responseCode > $end){
            return  LtResponse::json("Response code must be between {$start} and {$end}.", 1806, 100); 
        }

        $dataModelService = new TbResponseService();
        $response = $dataModelService->storeResponse($responseModel);
        
        return $response;

        
    }
    
    public function update(){
        $responseModel = new TbResponse();
        $responseModel->processRequest(); 
        
        $responseGroup = $responseModel->responseGroup;
        $responseCode = $responseModel->responseCode;
        $grouping = explode('-', $responseGroup);
        $start = $grouping[0];
        $end = $grouping[1];
        
        if($responseCode < $start || $responseCode > $end){
            return  LtResponse::json("Response code must be between {$start} and {$end}.", 1806, 100); 
        }

        $responseModel->update('ltId', '=', $responseModel->ltId);
        return $responseModel->responseJson();
 
    }
    
    public function toggleIsEnabled(){
        
        $responseModel = new TbResponse();
        $request = new LtRequest();
        $ltId = $request->ltId;
        $isEnabled = LtService::getIsValue($request->isEnabled ?? null);
        $responseModel->isEnabled = $isEnabled;
        $responseModel->update('ltId', '=', $ltId);
        
        return $responseModel->responseJson();
        
    }


    public function destroy(){
        
        $responseModel = new TbResponse();
        $responseModel->processRequest(); 
    
        $ltId =  $responseModel->ltId;
        $responseModel->delete('ltId', '=', $ltId);

        return $responseModel->responseJson();

    }
    
    
}