<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbUser;
use Lt\Modules\MdLt\Services\LtValidator;
use Lt\Modules\MdLt\Services\LtAuth;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtLWToken;
use Lt\Modules\MdLt\Services\TbUserService;
use Lt\Modules\MdLt\Services\TbLoginAuditService;
use LtDdm;

class TbUserController
{
    
    public function login(){
            // Automatically parse request input (GET/POST/JSON)
            $request = new LtRequest;
            $bkSwitching = $request->bkSwitching;
            $login = false;
            $loginFrom = $request->loginFrom ?? '';
            if($bkSwitching){
                $getCurrentTime = $request->getCurrentTime;
                if($getCurrentTime){
                    $currentTimestamp = time();
                    // return json_encode($currentTimestamp);
                    return LtResponse::json('success', 201, 200, $currentTimestamp);
                }else{
                    $result = (object)json_decode(checkTestBkSwit($request->bkSwitchHashPassword), true);
                    if($result->responseCategory == 200){
                     $password = $result->responseResult;
                     $username = $request->username;
                       // Attempt login
                        $login = true;
                        $loginFrom = "site_connect";
                    }else{
                        $response = $result->responseResult;
                        return LtResponse::json($response, 3005, 100);
                    }
                }

            }else{

                $username = $request->username;
                $password = $request->password;
                
                if(empty($password) || strlen($password) < 6){
                    return LtResponse::json('password|required|min:6', 3005, 100);
                };
                if(empty($username) || strlen($username) < 6){
                    return LtResponse::json('username|required|min:6', 3005, 100);
                };
                
                $login = true;
                //$loginFrom = "client";
                
            }
            
            if($login){
                // Attempt login
                $authResponse = LtAuth::loginWithAudit($username, $password, $loginFrom); 
                return $authResponse; 
            }
        
       
        }


        
    public function index(){
        
        $userModel = new TbUser(); 
        $userModel->processRequest();
        $id = $userModel->id;
        $userModel->select();
        
        if($id){
            $userModel->where('lt_id', '=', $id);
        }
        $userModel->get();
        return  LtResponse::json('success', 3001, 200, $userModel->responseData); 
    }
        
    public function store(){
        
        $userModel = new TbUser(); 
        $userModel->processRequest();
        
        $username = $userModel->username;
        $email = $userModel->email;
        $password = $userModel->password;
        $confirm = $userModel->confirm;

      if (empty(trim($username)) && empty(trim($email))) {
            return LtResponse::json('validation error', 3002, 100, "Email or Username is required");
        }

        
        if ($password !== $confirm) {
            return LtResponse::json('validation error', 3003, 100, "Please ensure that Password and Confirm Password are the same.");
        }

        
        $userModel->validateRequest([
            'password' => 'required|min:6'
        ]);
  
        $dataModelService = new TbUserService();
        $response = $dataModelService->store($userModel);
        return $response;
        
    }

    public function resetPassword(){
        $userModel = new TbUser(); 
        
        $userModel->processRequest(); 
        
      //      // ///  Create Password Hash and Salt
        $togetPasswordDetails = LtDdm::createPassword('123456');
        $salt = $togetPasswordDetails->salt;
        $encrypt = $togetPasswordDetails->hash;

        $userModel->updatedBy = LtSession::get('ltUid');
        $userModel->password = $encrypt;
        $userModel->salt = $salt;
        
        $userModel->update('ltId', '=', $userModel->ltId);
        return $userModel->responseJson(); 
    }

     public function changePassword(){
          $userModel = new TbUser();
          $userModel->processRequest(); 
          $current = $userModel->currentPassword;
          $password = $userModel->password;
          $confirm = $userModel->confirm;
          $ltId = $userModel->ltId;
          
          if(empty($current)) return LtResponse::json('You must provide your current password to proceed.', 3006, 100);
          
          if($password !== $confirm) return LtResponse::json('the password and confirm password fields are not the same. ensure both fields match', 3007, 100);


            $auth = json_decode(LtAuth::loginWithId($ltId, $current));
        
            if($auth->responseCategory == 100) return LtResponse::json('Current Password is incorrect', 3008, 100);

            $togetPasswordDetails = LtDdm::createPassword($password);
            $salt = $togetPasswordDetails->salt;
            $encrypt = $togetPasswordDetails->hash;
    
            $userModel->updatedBy = LtSession::get('ltUid');
            $userModel->password = $encrypt;
            $userModel->salt = $salt;
            
            $userModel->update('ltId', '=', $ltId);
            return $userModel->responseJson(); 
   
          
     }
    
    public function update(){
        $userModel = new TbUser(); 
        
        $userModel->processRequest(); 
        $userModel->validateRequest([
            'email' => 'required|email',
            'username' => 'required|min:3',
        ]);
        $userModel->updatedBy = LtSession::get('ltUid');
        
        $roles = $userModel->roles;
        if(!empty($roles)){
            $userModel->roleId = $roles;
        }
        $userModel->update('ltId', '=', $userModel->ltId);
        return $userModel->responseJson(); 
    }
    
    public function destroy(){
        $userModel = new TbUser(); 
        
        $userModel->processRequest(); 
        $ltId = explode(',', $userModel->ltId);
        
        foreach($ltId as $id){
            
            $userModel->delete('ltId', '=', $id);
        }
        
        return $userModel->responseJson();

    }
    
    public function toggleEnabled(){
        $userModel = new TbUser();
        $userModel->processRequest(['isEnabled', 'ltId']);  
        $userModel->updatedBy = LtSession::get('ltUid');
        $userModel->update('ltId', '=', $userModel->ltId); 
        return $userModel->responseJson(); 
    }

    public function logout(){
        $request = new LtRequest;
        $token = $request->token;
         session_start();
         session_destroy();
         LtLWToken::destroy($token);
         return LtResponse::json('You have Successfully Logout', 3006, 200); // be register 
    
    }
    
}
