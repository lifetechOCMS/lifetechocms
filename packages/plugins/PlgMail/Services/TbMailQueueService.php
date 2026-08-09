<?php
namespace Lt\Plugins\PlgMail\Services;

use Lt\Plugins\PlgMail\Models\TbMailQueue;
use Lt\Plugins\PlgMail\Services\PlgMailService;

use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use DbConnect;

class TbMailQueueService
{
    protected $queueModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->queueModel = new TbMailQueue();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
    }

    public function index(){
        $status = $this->request->status;

        $query = $this->queueModel->select();

        if($status !== null && $status !== ''){
            $query->where('status','=', $status);
        }

        return $query->get();
    }
    
    public function sendMail(){

        $mailId = $this->request->mailId;
        $row = $this->queueModel->select()->where('ltId','=', $mailId)->get();
        if(count($row) < 1){
            return LtResponse::json('Mail record not found in the queue', 3004, 100);
        }
        
        $data = [
            'oldCount' => $row[0]->retryCount,
            'recipientName' => $row[0]->toName,
            'email' => $row[0]->toEmail,
            'subject' => $row[0]->subject,
            'htmlBody' => $row[0]->bodyText,
            'mailId'  => $mailId
            ];
        
        
        $result = $this->mailSender($data);
        return LtResponse::json('success', 201, 200, $result);
        // return $result;

    }

    
    public function store(array $queueData){
        $queueData = (object)$queueData;
        $recipientEmail = $queueData->toEmail;
        
        $row = $this->queueModel->select()->where('toEmail','=', $recipientEmail)->get();
        
        
        $this->queueModel->toEmail = $queueData->toEmail;
        $this->queueModel->toName =  $queueData->toName;
        $this->queueModel->subject = $queueData->subject;
        $this->queueModel->bodyText = $queueData->bodyText;
        $this->queueModel->payloadJson = $queueData->payloadJson;
        $this->queueModel->createdAt = $this->ltNow;
        $this->queueModel->updatedAt = $this->ltNow;
        
        if(count($row) < 1){
            $this->queueModel->ltId = $this->ltId;
            $this->queueModel->createdAt = $this->ltNow;
            $this->queueModel->insert();
        }else{
            $mailId = $row[0]->ltId;
            $updateResponse = $this->queueModel->update('ltId', '=', $mailId);
            // return LtResponse::json('Mail record exist already', 3004, 100);
        }
        
       

        
        return $this->queueModel->responseJson();
    }


    
    // public function sendMultipleMail(){
    //     $sqlConnect = DbConnect::dbDriver();
    //     $selectIds = $this->request->selectedIds;
        
    //     $oldCount = $row[0]->retryCount;
    //     $recipientName = $row[0]->toName;
    //     $email = $row[0]->toEmail;
    //     $subject = $row[0]->subject;
    //     $htmlBody = $row[0]->bodyText;
    //     $ids = implode(',', $selectIds);
    //     $sqlMail = "SELECT lt_id AS ltId, to_email AS toEmail, to_name AS toName, subject, body_text AS bodyText, retry_count AS retryCount
    //                             FROM tb_mail_queue
    //                             WHERE lt_id IN ($ids)";
        
    //                 $stmtMail = $sqlConnect->prepare($sqlMail);
    //                 $stmtMail->execute($params);
    //                 $mailList = $stmtMail->fetchAll(\PDO::FETCH_ASSOC);
    //     $resultArr = [];
    //     // foreach($mailList AS $mail){
    //     //     $mail = (object)$mail;
    //     //     $data = [
    //     //     'oldCount' => $mail->retryCount,
    //     //     'recipientName' => $mail->toName,
    //     //     'email' => $mail->toEmail,
    //     //     'subject' => $mail->subject,
    //     //     'htmlBody' => $mail->bodyText,
    //     //     'mailId' => $mail->ltId,
    //     //     ];
            
                   
    //     //     $result = $this->mailSender($data);
    //     //     // $resultArr[] = $result;
            
    //     // }
    //     // return LtResponse::json('success', 201, 200, $response);
    //   return json_encode($resultArr);
      
    // }
    
    
    
        
    private function mailSender(array $mailData){
        $mailData = (object)$mailData;
        
        $mail = new PlgMailService();
        $template = [
            "subject" => $mailData->subject,
            'bodyText' => $mailData->htmlBody
            ];
        $receiver = [
            'toEmail' => $mailData->email
            // 'toEmail' => null
            ];
        $sender = [];
        
        $response = $mail->send($sender, $template, $receiver);
        
          $this->queueModel->retryCount = $mailData->oldCount + 1;            
        if($response->responseCategory == 100){
          $this->queueModel->errorMessage = $response->responseData;            
          $this->queueModel->failedAt = $this->ltNow;            
          $this->queueModel->status = 'failed';
        }else{
            $this->queueModel->sentAt = $this->ltNow;
            $this->queueModel->status = 'sent';
        }
        
        $updateResponse = $this->queueModel->update('ltId', '=', $mailData->mailId);
        // return $updateResponse;
        return $response;
        
    }
    


   
    public function destroy(){
        $ltId = $this->request->ltId;
        $exist = $this->queueModel->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Mail record not found in the queue', 3004, 100);
        }
        $this->queueModel->delete('ltId','=',$ltId);
        return LtResponse::json('Deleted', 201, 200);
    }
    
    
        // public function update($ltId){
        //     $exist = $this->queueModel->select()->where('ltId','=', $ltId)->get();
        //     if(count($exist) < 1){
        //         return LtResponse::json('School not found', 3004, 100);
        //     }
            
        //     $this->queueModel->processRequest();
    
        //     $this->queueModel->updatedBy = $this->userId;
        //     $this->queueModel->updatedAt = $this->ltNow;
    
        //     $this->queueModel->update('ltId','=',$ltId);
        //     return $this->queueModel->responseJson();
    //}
}

