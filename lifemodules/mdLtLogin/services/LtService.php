 <?php
 
 ltImport('mdLtLogin', 'TbRegistrations.php');

class LtService {
    
    protected $dataModel;
    protected $request;
    
         public function __construct(){
             
            $this->dataModel = new TbRegistrations;
            $this->request = new LtRequest;
            
        }
    
    public function register(){
        
         $this->dataModel->processRequest();
         $regId = ltId();
         $dateTime = date('Y-m-d, H:s:i');
          
          $password = trim(htmlspecialchars($this->request->lifetechPassword, ENT_QUOTES, 'UTF-8'));
          $togetPasswordDetails = LtDdm::createPassword($password);
          $salt = $togetPasswordDetails->salt;
          $encrypt = $togetPasswordDetails->hash;
         
        //Loading of form Role 
          $getformRoleData = ltAuth::getFormRole('lifetechUserData');
          $roleEncryption = $getformRoleData->encryptRole;
          $activateStatus = $getformRoleData->loginStatus;
              
          $username = trim(htmlspecialchars($this->request->lifetechUsername, ENT_QUOTES, 'UTF-8'));
          $email =    trim(htmlspecialchars($this->request->lifetechEmail, ENT_QUOTES, 'UTF-8'));
          
          $this->dataModel->lifetechGeneralId = $regId;
          $this->dataModel->userId = $regId;
          $this->dataModel->lifetechUsername = $username;
          $this->dataModel->salt = $salt;
          $this->dataModel->lifetechPassword = $encrypt;
          $this->dataModel->formType = 'TbRegistrations';
          $this->dataModel->activationStatus = $activateStatus;
          $this->dataModel->roleEncrypt = $roleEncryption;
          $this->dataModel->createdAt = $dateTime;
          
            $querySingleUser = $this->dataModel->select()->where('lifetechUsername', '=' , $username)->orWhere('lifetechEmail', '=' , $email)->get();
            if (count($querySingleUser) > 0) {
                $response = LtResponse::json("Account Already Available, Please Use Another Username or Email.......", "108", "100");
              
            }else {
                $response = LtResponse::json("Account Not Available, registered", "108", "100");
                $this->dataModel->insert();
                 $response = $this->dataModel->responseJson();
               
            }

            return $response;
    }
    
    
    public function change(){
        
        $userId = empty(LtSession::get('user_id')) ? LtSession::get('user_uid') : LtSession::get('user_id');

        $current = trim(htmlspecialchars($this->request->currentPassword, ENT_QUOTES, 'UTF-8'));
        $newPassword = trim(htmlspecialchars($this->request->newPassword, ENT_QUOTES, 'UTF-8'));
        $confirmPassword = trim(htmlspecialchars($this->request->confirmPassword, ENT_QUOTES, 'UTF-8'));
        $dateNow = date("F j, Y, g:i a");
        
        if($newPassword !== $confirmPassword){
          $msg = "The passwords you entered don't match. Please make sure they match.";
          $response = LtResponse::json('failed', "104","100", $msg);
        }else{
            $queryRegTable = $this->dataModel->select()->where('lifetechGeneralId', '=', $userId)->orWhere('userId', '=', $userId)->get()[0];
            $newUserId = $queryRegTable->lifetechGeneralId;
            $verifyPass =   LtAuth::loginWithId($newUserId, $current);
            $verifyPass = json_decode($verifyPass);
        
            if($verifyPass->responseCategory == "200"){
                
                if (!empty($verifyPass->responseResult->lifetechSurname)) {
                        $surname = $verifyPass->responseResult->lifetechSurname;
                    } elseif (!empty($verifyPass->responseResult->lifetechUsername)) {
                        $surname = $verifyPass->responseResult->lifetechUsername;
                    } else {
                        $surname = 'User ';
                    }            
                
                    $email = $verifyPass->responseResult->lifetechEmail;
                     //proceed to change the password
                     $togetPasswordDetails = LtDdm::createPassword($newPassword);
                     $salt = $togetPasswordDetails->salt;
                     $encrypt = $togetPasswordDetails->hash;
                     
                     $this->dataModel->salt = $salt;
                     $this->dataModel->lifetechPassword = $encrypt;
                     
                     $this->dataModel->update("lifetechGeneralId", "=", $newUserId);
                 
                    $subject = 'Your Password Has Been Successfully Changed';
                 
                    $message = "
                    <html>
                    <head>
                    <title>". $subject ."</title>
                    </head>
                    <body>
                    <p><b>Dear ".$surname."</b></p>
                    
                    <p>We would like to inform you that your account password was successfully updated on ". $dateNow ."</p> 
                    <p>If you made this change, no further action is required. However, if you did not initiate this update, please contact us immediately to secure your account</p>
        
                    <p>For your security, avoid sharing your password with anyone and ensure it meets strong password standards.</p>
                    <p>This notification is sent as part of our commitment to account security. If you suspect any unauthorized activity, please report it to our support team promptly.</p>
                    <p>If you have any questions or need assistance, feel free to reach out to us.</p>
                    <p><p>Note: Replies sent from your email will not be received, this is an automatically generated operational email forwarded by LIFETECH OCMS.</p><br> <br>
                    <p> Best regards </p>
                    
                    <p><b>©lifetech community ".date("Y")."</b></p>
                    </body>
                    </html>
                    ";
                    
                    // Always set content-type when sending HTML email
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    
                    // More headers
                    $headers .= 'From: Lifetech OCMS <noreply@lifetech.host>' . "\r\n";
                    
                    mail($email,$subject,$message,$headers);
                 
    
                $response = LtResponse::json("Password Changed Successfully","204", "200");
                    
            }else{
                
                 $response = LtResponse::json("Current Password is incorrect", "104", "100");
            }
        }
     
        return $response;
      
    }
    
    public function forgotPassword(){

            $lifetechEmail = trim(htmlspecialchars($this->request->email, ENT_QUOTES, 'UTF-8'));
            
            
            $sqlCheck = $this->dataModel->select()->where('lifetechEmail', '=', $lifetechEmail)->get();
            
            
            if(count($sqlCheck) > 0){
                
                $userInfo = $sqlCheck[0];
                $lifetechUsername = $userInfo->username;
                
                
                $token = $this->generateRandomToken();
                $expirationTime = time() + (5 * 60); // Current time + expiration time of 5 minutes in seconds
                $authTokenExpiry = date('Y-m-d H:i:s', $expirationTime);
               // $emailEncode = $this->ltEncryptId($lifetechEmail);
                //$url = lifetech_site_host_address() . "/verify_token?sn=" . $emailEncode;
                
                function ltSendMail($to, $subject, $recipientName, $token, $type){
                    
                            $message = "
                            <html>
                            <head>
                            <title>".$subject."</title>
                            </head>
                            <body>
                            <p><b>Dear ".$recipientName."</b></p>
                            
                            <p>Your One-Time Password (OTP) is <strong>". $token . "</strong></p> 
                            <p>This code is valid for the next 5 minutes. Please use it to complete your ". $type ." </p>
                
                            <p>If you did not request this code, please disregard this email.</p>
                            <p><p>Note: Replies sent from your email will not be received, this is an automatically generated operational email forwarded by LIFETECH.</p><br></p>
                            <p><b>©lifetech community ".date("Y")."</b></p>
                            </body>
                            </html>
                            ";
                            
                            // Always set content-type when sending HTML email
                            $headers = "MIME-Version: 1.0" . "\r\n";
                            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                            
                            // More headers
                            $headers .= 'From: Lifetech OCMS <noreply@lifetech.host>' . "\r\n";
                            // $headers .= 'Cc: ' . $to . "\r\n";
                            
                            mail($to,$subject,$message,$headers);
                                
                        }
                        // $this->dataModel->authToken = $token;
                        $this->dataModel->authToken = md5($token);
                        $this->dataModel->authTokenExpiry = $authTokenExpiry;
                        $this->dataModel->update('lifetechEmail', '=', $lifetechEmail);
                        
                        ltSendMail($lifetechEmail, 'Your One-Time Password (OTP) from Lifetech', $lifetechUsername, $token, "forgot password process");
                        
                        
                    $response = LtResponse::json('Token Sent Successfully', "201", "200");
                
            }else{
                
                 $response = LtResponse::json("Invalid Email Address. Not a registered email", "103", "100");
                
            }
            
            return $response;
    }
    
    
    public function verifyToken(){

        $token = trim(htmlspecialchars($this->request->token, ENT_QUOTES, 'UTF-8'));
        $lifetechEmail = trim(htmlspecialchars($this->request->email, ENT_QUOTES, 'UTF-8'));
        // $tokenEncypt = $token;
        $tokenEncypt = md5($token);
      
        $querySingle = $this->dataModel->select()->where('authToken', '=' , $tokenEncypt)->andWhere('lifetechEmail', '=' , $lifetechEmail)->get();
        
        if(count($querySingle) > 0){
            $user = $querySingle[0];
            
            $userId = $user->lifetechGeneralId;
            $username = $user->lifetechUsername;
            $expirationTime = strtotime($user->authTokenExpiry);
            
            if(time() > $expirationTime){
                
                $response = LtResponse::json("Token Expired. Please Generate another Token","107","100");
                
            }else{
                
                 $verifiedToken = $this->generateRandomToken();
                 $expirationTime = time() + (10 * 60); // Current time + expiration time of 5 minutes in seconds
                 $authTokenExpiry = date('Y-m-d H:i:s', $expirationTime);
                 
                //  $this->dataModel->authToken = $verifiedToken;
                 $this->dataModel->authToken = md5($verifiedToken);
                 $this->dataModel->authTokenExpiry = $authTokenExpiry;
                 $this->dataModel->update('lifetechGeneralId', '=', $userId);
                 
                 $data = ['verifiedToken' => $verifiedToken, 'userId' => $userId, 'email' => $lifetechEmail, 'username' => $username];
                 
                 $response = LtResponse::json('Token Verifed', "201", "200", $data);
            }
            
            
        }else{
            $response = LtResponse::json("Incorrect Token", "103", "100");
           
        }
        
             return $response;
            
    }
    
    
    public function reset(){

         $dateNow = date("F j, Y, g:i a");
         $userId = trim(htmlspecialchars($this->request->userId, ENT_QUOTES, 'UTF-8'));
         $token = trim(htmlspecialchars($this->request->tokenValue, ENT_QUOTES, 'UTF-8'));
         $password = trim(htmlspecialchars($this->request->password, ENT_QUOTES, 'UTF-8'));
         $confirm = trim(htmlspecialchars($this->request->confirm, ENT_QUOTES, 'UTF-8'));
         $tokenEncypt = md5($token);
        //  $tokenEncypt = $token;

        if(empty($password)){
            $response = LtResponse::json("Password field cannot be empty", "107", "100");
            return $response;
        }

        if(empty($userId)){
            $response = LtResponse::json("Invalid User", "107", "100");
            return $response;
        }
        
         if($password !== $confirm){
            $response = LtResponse::json("Password does not match. Password and Confirm Password must be of the same value", "107", "100");
            return $response;
        }
        
        $querySingle = $this->dataModel->select()->where('authToken', '=' , $tokenEncypt)->andWhere('lifetechGeneralId', '=' , $userId)->get();
        
            if(count($querySingle) > 0){
                $user = $querySingle[0];
                
                $userId = $user->lifetechGeneralId;
                $username = $user->lifetechUsername;
                $expirationTime = strtotime($user->authTokenExpiry);
            
                if(time() > $expirationTime){
                    
                    $response = LtResponse::json("Token Expired. Please Generate another Token","107","100");
                    
                }else{
                
                    $togetPasswordDetails = LtDdm::createPassword($password);
                    $salt = $togetPasswordDetails->salt;
                    $encrypt = $togetPasswordDetails->hash;

                        if (!empty($user->lifetechSurname)) {
                            $surname = $user->lifetechSurname;
                        } elseif (!empty($username)) {
                            $surname = $username;
                        } else {
                            $surname = 'User ';
                        }  
        
                      /////////////////    for registration table
                    
                        $this->dataModel->salt = $salt;
                        $this->dataModel->lifetechPassword = $encrypt;
                        $this->dataModel->authToken = '';
                        $this->dataModel->authTokenExpiry = '';
                    
                        $this->dataModel->update('lifetechGeneralId', '=', $userId);
                        
                            $subject = 'Password Reset Complete – You Can Now Log In';
                         
                            $message = "
                            <html>
                            <head>
                            <title>". $subject ."</title>
                            </head>
                            <body>
                            <p><b>Dear ".$surname."</b></p>
                            
                            <p>We would like to inform you that your account password was successfully updated on ". $dateNow ."</p> 
                            <p>If you made this change, no further action is required. However, if you did not initiate this update, please contact us immediately to secure your account</p>
                
                            <p>For your security, avoid sharing your password with anyone and ensure it meets strong password standards.</p>
                            <p>This notification is sent as part of our commitment to account security. If you suspect any unauthorized activity, please report it to our support team promptly.</p>
                            <p>If you have any questions or need assistance, feel free to reach out to us.</p>
                            <p><p>Note: Replies sent from your email will not be received, this is an automatically generated operational email forwarded by LIFETECH OCMS.</p><br> <br>
                            <p> Best regards </p>
                            
                            <p><b>©lifetech community ".date("Y")."</b></p>
                            </body>
                            </html>
                            ";
                            
                            // Always set content-type when sending HTML email
                            $headers = "MIME-Version: 1.0" . "\r\n";
                            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                                
                            // More headers
                            $headers .= 'From: Lifetech OCMS <noreply@lifetech.host>' . "\r\n";
                            mail($email,$subject,$message,$headers);
                        
                        $response = LtResponse::json("Password Reset Successfully", "203", "200");
            
                }
            
            
            }else{
                $response = LtResponse::json("Incorrect Token", "103", "100");
               
            }
                
             return $response;

    }
    
    public function generateRandomToken($length = 6) {
        
        $token = '';
        for ($i = 0; $i < $length; $i++) {
            $token .= random_int(1, 9); // Generates a random digit between 0 and 9
        }
        return $token;
    }
    
    
    public function ltEncryptId($id, $key = 'mySecretKey12345678901234567890', $iv = null) {
        if (is_null($iv)) {
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('AES-256-CBC'));
        }
    
        $encrypted = openssl_encrypt($id, 'AES-256-CBC', $key, 0, $iv);
    
        // Combine encrypted data and iv as base64 strings
        return urlencode(base64_encode($encrypted) . '::' . base64_encode($iv));
    }
    
    public function ltDecryptId($encryptedId, $key = 'mySecretKey12345678901234567890') {
        // Decode URL and split the parts
        $decodedUrl = rawurldecode($encryptedId);
        list($encodedEncryptedData, $encodedIv) = explode('::', $decodedUrl, 2);
    
        $encryptedData = base64_decode($encodedEncryptedData);
        $iv = base64_decode($encodedIv);
    
        return openssl_decrypt($encryptedData, 'AES-256-CBC', $key, 0, $iv);
    }


   public  function maskEmail($email) {
        // Split the email into username and domain
            $em = explode("@", $email);
            $name = $em[0];
            $domain = $em[1];
            
            // Calculate half the length of the username
            $length = floor(strlen($name) / 2);
            $total_length = floor($length / 2);
            if($length > 6){
               $length = 8;
            }
            
            // Mask half of the username
            $maskEmail = substr($name, 0, $total_length) . str_repeat('*', $length) . "@" . $domain;
            return $maskEmail;
    }
    
}
 



?> 
      
      