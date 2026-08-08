<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbUser;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtAuth;
use LtDdm;

class TbUserService
{
        protected $userModel;
        protected $request;
        protected $ltId;
        protected $userId;
        protected $ltNow;
        
        public function __construct(){
            $this->userModel = new TbUser();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->ltId = ltId();
            $this->userId = LtSession::get('ltUid');
        }
        
        public function store($model){
            
             
            $username = $model->username;
            $email = $model->email;
            $roles = $model->roles ?: 'c81e728d9d4c2f';
  
            $exist = $model->select()->where('username', '=', $username)->orWhere('email', '=', $email)->get();
            
            if(count($exist) > 0){
                return  LtResponse::json('User record already exist', 3004, 100); 
            }
        

        // //      // ///  Create Password Hash and Salt
            $togetPasswordDetails = LtDdm::createPassword($model->password);
            $salt = $togetPasswordDetails->salt;
            $encrypt = $togetPasswordDetails->hash;
              
           //   Loading of form Role 
            $getformRoleData = LtAuth::getFormRole('UserRegForm');
            $roleEncryption = $getformRoleData->encryptRole;
            $activateStatus = $getformRoleData->loginStatus;
            
            $model->ltId = $this->ltId;
            $model->roleId = $roles;
            $model->updatedBy = $this->userId;
            $model->password = $encrypt;
            $model->salt = $salt;
            $model->formType = 'userForm';
            $model->activationStatus = $activateStatus;
            $model->roleEncrypt = $roleEncryption;
            $model->createdAt = $this->ltNow;
            $model->updatedAt = $this->ltNow;
            
            $model->insert();
            return $model->responseJson();
        }
        
        public function dashboardAnalysis(){
            $users = $this->userModel->select()->get();
            
            $active = $notActive = [];
            foreach($users as $user){
                if($user->isEnabled == '1'){
                    $active[] = $users;
                }
                if($user->isEnabled == '0'){
                    $notActive[] = $users;
                }
            }
            $result = ['active' => count($active), 'notActive' => count($notActive), 'totalUsers' => count($users)];
            return $result;
        }

        
        
    }