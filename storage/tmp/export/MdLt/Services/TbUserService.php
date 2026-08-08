<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbUser;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtAuth;

// for sending Mail to be updated to plugin later
use Lt\Plugins\PlgMail\Services\PlgMailService;
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
        
        public function requestPasswordReset() {
            $mail = new PlgMailService();
            $email = $this->request->email;
            
            // 1. Fetch user
            $user = $this->userModel->select()->where('email', '=', $email)->get()[0];
            
            // 2. Anti-Enumeration: Always return success even if user doesn't exist
            if (!$user) {
                return LtResponse::json("If an account is associated with: {$email}, You will receive a 6-digit code", "3015", "200");
            }
            
            $username = $user->username;
            $token = $this->generateRandomToken(6); // Your 6-digit function
            
            // 3. Set expiration (10 minutes)
            $expirationTime = time() + (10 * 60); 
            $authTokenExpiry = date('Y-m-d H:i:s', $expirationTime);
            
            // 4. Send Email
            $htmlBody = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OTP Verification</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="padding:20px 0; background-color:#f4f6f8;">
        <tr>
            <td align="center">

                <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; padding:30px;">

                    <tr>
                        <td style="color:#333; font-size:15px;">

                            <p style="margin:0 0 15px;">
                                <strong>Dear ' . htmlspecialchars($username) . ',</strong>
                            </p>

                            <p style="margin:0 0 20px;">
                                Your One-Time Password (OTP) is:
                            </p>

                            <p style="margin:0 0 25px; text-align:center;">
                                <span style="display:inline-block; font-size:24px; letter-spacing:4px; font-weight:bold; color:#059669; background:#ecfdf5; padding:12px 20px; border-radius:6px;">
                                    ' . htmlspecialchars($token) . '
                                </span>
                            </p>

                            <p style="margin:0 0 15px;">
                                This code is valid for the next <strong>10 minutes</strong>. Use it to complete your password reset.
                            </p>

                            <p style="margin:0 0 20px;">
                                If you did not request this, please ignore this email or contact support immediately.
                            </p>

                            <hr style="border:none; border-top:1px solid #eee; margin:25px 0;">

                            <p style="font-size:12px; color:#777; margin:0 0 10px;">
                                This is an automated message. Replies are not monitored.
                            </p>

                            <p style="font-size:12px; color:#777; margin:0;">
                                © Lifetech Community ' . date("Y") . '
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>
';
            $template = [
                "subject" => 'Your One-Time Password (OTP) from Lifetech',
                'bodyText' => $htmlBody
                ];
            $receiver = [
                'toEmail' => $email
                ];
            $sender = [];
        
            $mail->send($sender,$template, $receiver);
            
            // 5. Update Database (Using SHA-256 instead of MD5)
            $this->userModel->authToken = hash('sha256', $token);
            $this->userModel->authTokenExpiry = $authTokenExpiry;
            $this->userModel->update('email', '=', $email);
            
            return LtResponse::json("If an account is associated with: {$email}, You will receive a 6-digit code", "3015", "200");
        }
        
        public function generateRandomToken($length = 6) {
    
            $token = '';
            for ($i = 0; $i < $length; $i++) {
                $token .= random_int(1, 9); // Generates a random digit between 0 and 9
            }
            return $token;
        }
        
        public function verifyOtp(){
            $email = $this->request->email;
            $otp = $this->request->otp;
            
            $hashOtp = hash('sha256', $otp);
            
           
            $exist = $this->userModel->select()->where('authToken', '=' , $hashOtp)->andWhere('email', '=' , $email)->get();
            
            
            if(count($exist) < 1) {
               return LtResponse::json("Incorrect Token", "30019", "100");
            }

            $userId = $exist[0]->ltId;
            $username = $exist[0]->username;
            $expirationTime = strtotime($exist[0]->authTokenExpiry);
            
            if(time() > $expirationTime){
                
                return LtResponse::json("Token Expired. Please Generate another Token","30017","100");
                
            }
                
                $verifiedToken = bin2hex(random_bytes(32));
                $newExpiry = date('Y-m-d H:i:s', time() + (10 * 60));
                 
                  // 5. Update Database (Using SHA-256 instead of MD5)
                $this->userModel->authToken = hash('sha256', $verifiedToken);
                $this->userModel->authTokenExpiry = $newExpiry;
                $this->userModel->update('email', '=', $email);
                 
                 return LtResponse::json('Token Verifed', "201", "200",  ['validationToken' => $verifiedToken, 'email'=> $email]);
        }
        
        public function reset(){
            $mail = new PlgMailService();
            $model = $this->userModel;
            $model->processRequest();
            $email = $model->email;
            $token = $model->token;
            $password = $model->password;
            
            $hashToken = hash('sha256', $token);
            
            $exist = $model->select()->where('authToken', '=' , $hashToken)->andWhere('email', '=' , $email)->get();
            
            if(count($exist) < 1) {
               return LtResponse::json("Incorrect Token", "30019", "100");
            }
            
            $expirationTime = strtotime($exist[0]->authTokenExpiry);
            
            if(time() > $expirationTime){
                
                return LtResponse::json("Token Expired. Please Generate another Token","30017","100");
            }
            
            $model->ltId = $exist[0]->ltId;
            $username = $exist[0]->username;
            $model->authToken = null;
            $model->authTokenExpiry = null;
            
            $response = $this->resetPassword($model, $password);
            $dateNow = date('l, F j, Y g:i A'); // "Monday, May 4, 2026 7:36 AM"
            // if($response->responseCategory == '200'){
                $htmlBody = '
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Your Password Has Been Reset Successfully</title>
                </head>
                <body style="margin:0; padding:0; font-family: Arial, sans-serif; background-color:#f4f6f8;">
                    
                    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:20px 0;">
                        <tr>
                            <td align="center">
                                
                                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; padding:30px;">
                                    
                                    <tr>
                                        <td style="font-size:16px; color:#333;">
                                            <p style="margin:0 0 15px;"><strong>Dear ' . htmlspecialchars($username) . ',</strong></p>
                
                                            <p style="margin:0 0 15px;">
                                                This is to inform you that your account password was successfully updated on 
                                                <strong>' . htmlspecialchars($dateNow) . '</strong>.
                                            </p>
                
                                            <p style="margin:0 0 15px;">
                                                If you made this change, no further action is required. However, if you did not initiate this update,
                                                please contact our support team immediately to secure your account.
                                            </p>
                
                                            <p style="margin:0 0 15px;">
                                                For your security, never share your password with anyone and ensure it meets strong password standards.
                                            </p>
                
                                            <p style="margin:0 0 20px;">
                                                If you suspect any unauthorized activity, report it promptly so we can assist you.
                                            </p>
                
                                            <p style="margin:20px 0 5px;">Best regards,</p>
                                            <p style="margin:0 0 20px;"><strong>LifetechOCMS Team</strong></p>
                
                                            <hr style="border:none; border-top:1px solid #eee; margin:20px 0;">
                
                                            <p style="font-size:12px; color:#777; margin:0 0 10px;">
                                                Note: This is an automated message. Replies to this email are not monitored.
                                            </p>
                
                                            <p style="font-size:12px; color:#777; margin:0;">
                                                © Lifetech Community ' . date("Y") . '
                                            </p>
                                        </td>
                                    </tr>
                
                                </table>
                
                            </td>
                        </tr>
                    </table>
                
                </body>
                </html>
            ';
            
            $template = [
                "subject" => 'Security Alert: Your Password Has Been Updated',
                'bodyText' => $htmlBody
                ];
            $receiver = [
                'toEmail' => $email
                ];
            $sender = [];
        
            $mail->send($sender,$template, $receiver);
            
            return $response;
        
        }
        
        public function resetPassword($model, $newPassword){
            
            $togetPasswordDetails = LtDdm::createPassword($newPassword);
            $salt = $togetPasswordDetails->salt;
            $encrypt = $togetPasswordDetails->hash;
    
            $model->updatedBy = LtSession::get('ltUid');
            $model->password = $encrypt;
            $model->salt = $salt;
            
            $model->update('ltId', '=', $model->ltId);
            return $model->responseJson(); 
            
        }
        
 
    }
    
    