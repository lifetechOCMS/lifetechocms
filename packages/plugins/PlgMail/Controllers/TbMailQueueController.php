<?php
// namespace Lt\Plugins\PlgMail\Controllers;
use Lt\Plugins\PlgMail\Services\TbMailQueueService;


use Lt\Modules\MdLt\Services\LtResponse;

class TbMailQueueController
{
    
    public function index(){
        
        $service = new TbMailQueueService();
        $rows = $service->index();
        return LtResponse::json('success', 201, 200, $rows);
    }
    
    public function sendMail(){
        $service = new TbMailQueueService();
        $response = $service->sendMail();
        return $response;

    }
    
    public function deleteMail(){
        $service = new TbMailQueueService();
        $response = $service->sendMail();
        return $response;

    }
    
    public function destroy(){
        $service = new TbMailQueueService();
        $result = $service->destroy();
        return $result;
    }
    
    public function sendMultipleMail(){
        $service = new TbMailQueueService();
        $result = $service->sendMultipleMail();
        return $result;
    }
    
    // public function store(){
        
    //     $service = new TbMailQueueService();
    //     $response = $service->store();
    //     return $response;

    // }
    
}

