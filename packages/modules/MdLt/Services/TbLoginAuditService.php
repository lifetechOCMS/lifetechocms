<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbLoginAudit;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
        
class TbLoginAuditService
{
        protected $loginAuditModel;
        protected $request;
        protected $ltId;
        protected $userId;
        
        public function __construct(){
            $this->loginAuditModel = new TbLoginAudit();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->userId = LtSession::get('ltUid');
        }
        
        public function logAudit($loginDetails){
            // Get the user's IP address safely
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            // Get the user agent (browser/device info)
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $this->loginAuditModel->userId = $loginDetails['userId'];
            $this->loginAuditModel->username= $loginDetails['username'];
            $this->loginAuditModel->statusReason = $loginDetails['reason'];
            $this->loginAuditModel->ipAddress = $ip_address;
            $this->loginAuditModel->userAgent = $user_agent;
            $this->loginAuditModel->loginStatus = $loginDetails['status'];
            $this->loginAuditModel->attemptedAt = $this->ltNow;
            $this->loginAuditModel->loginFrom = $loginDetails['loginFrom'];
            $this->loginAuditModel->insert();
            
            $this->loginAuditModel->responseJson();
        }

}

