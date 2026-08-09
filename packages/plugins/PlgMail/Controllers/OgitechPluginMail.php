<?php
namespace Lt\Plugins\PlgMail\Controllers;

use Lt\Plugins\PlgMail\Services\TbMailQueueService;

use Lt\Plugins\PlgMail\Models\TbMailQueue;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
           
class OgitechPluginMail
{
    
    public function store(){
        $service = new TbMailQueueService();
        $queueModel = new TbMailQueue();
        $queueModel->processRequest();
        
        $recipientEmail = $queueModel->email;
        $recipientName = $queueModel->name;
        $recipientToken = $queueModel->token;
        $subject = $queueModel->subject;
        $electionType = $queueModel->electionType;
        
        if($recipientEmail === '' && $recipientName === ''){
            return LtResponse::json('Recipient Name and Recipient Email is required', 3004, 100);
        }
        
        /// temporary
        if(empty($subject)){
            $pollSeasonTitle = "SUG2026_ELECTION";
            $subject = $recipientName.", Your Voting OTP For ".$pollSeasonTitle;
        }
        $payloadData = json_encode(['data' => $recipientToken, 'electionType' => $electionType]);
        $htmlBody = "
            <html>
            <head>
            <title>".$subject."</title>
            </head>
            <body>
            <p><b>Dear ".$recipientName."</b></p>
            
            <p>Your One-Time Password (OTP) for voting is <strong>". $recipientToken . "</strong></p> 
            <p>This code is valid for the next 10 minutes. Please use it to complete your <strong>". $electionType . "</strong> verification.</p>

            <p>If you did not request this code, please disregard this email.</p>
            <p><p>Note: Replies sent from your email will not be received, this is an automatically generated operational email forwarded by OGITECH.</p><br></p>
            
            <p><b>©OGITECH Student Affairs Office ".date("Y")."</b></p>
            </body>
            </html>
        ";
        $queueData = [
            'toEmail' => $recipientEmail,
            'toName' => $recipientName,
            'payloadJson' => $payloadData,
            'subject' => $subject,
            'bodyText' => $htmlBody,
            ];
        $response = $service->store($queueData);
        return $response;

    }

}

