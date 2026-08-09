<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbFormRole;
use Lt\Modules\MdLt\Models\TbUser;
use Lt\Modules\MdLt\Services\TbLoginAuditService;
use ApiTokenDetails;
use LtDdm;

 

//global class declaration

class LtAuth
{
    private static $life_user;

    public function __construct()
    {
        self::$life_user = new TbUser;
    }

    public static function hidInitiate()
    {
        self::$life_user = new TbUser;
    }

    public static function activateUser($lifetech_general_id)
    {
        self::hidInitiate();
        $result = self::$life_user->select()->where('ltId', '=', $lifetech_general_id)->get();
        $user = $result[0] ?? null;

        if (!$user) {
            return LtResponse::json("Record Not Found", "1001", "100");
        }

        $id = $user->ltId;
        self::setLtSession($user, $id);
        //get api tokens timeout parameter
        LtLWToken::destroyExpiredToken();
        $toGetapiTokenExpiredTimeOut = ApiTokenDetails::apiTokenExpiredTimeOut();
        LtLWToken::insertLwtToken($id, $user->roleId,$toGetapiTokenExpiredTimeOut);
    }

    public static function login($username, $password = "")
    {
        self::hidInitiate();

        if ($password === "") {
            $result = self::getUserByUsernameOrEmail($username);
            $response = count($result) === 1 ? $result[0]->ltId : null;
            return LtResponse::json($response ?? "Record Not Found", $response ? "1002" : "1001", $response ? "200" : "100");
        }

        return self::handleLoginAttempt($username, $password, 'getUserByUsernameOrEmail');
    }
    
    public static function loginWithAudit($username, $password = "null", $loginFrom="null")
    {
        $loginProcess = self::login($username, $password);
          //Login audit for trachking (optional)
                $response = json_decode($loginProcess);
                if($response->responseCategory == '100'){
                    $status = 'failed';
                    $reason = $response->responseResult;
                }else{
                     $status = 'success';
                     $reason = $response->responseResult;
                     $userId = $response->responseData->ltId;
                }
                $loginAuditInstance = new TbLoginAuditService();
                $loginDetails = [
                        "username" => $username,
                        "reason" => $reason,
                        "status" => $status,
                        "loginFrom" => $loginFrom,
                        "userId" => $userId
                     ];
                     
                $result = $loginAuditInstance->logAudit($loginDetails); 
                //end of login audit
                return $loginProcess;
         
    }

    public static function loginWithEmail($email, $password = "")
    {
        self::hidInitiate();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return LtResponse::json("Email Not Valid", "1003", "100");
        }

        if ($password === "") {
            $result = self::getUserByEmail($email);
            $response = count($result) === 1 ? $result[0]->ltId : null;
            return LtResponse::json($response ?? "Record Not Found", $response ? "1002" : "1001", $response ? "200" : "100");
        }

        return self::handleLoginAttempt($email, $password, 'getUserByEmail');
    }

    public static function loginWithUsername($username, $password = "")
    {
        self::hidInitiate();

        if ($password === "") {
            $result = self::getUserByUsername($username);
            $response = count($result) === 1 ? $result[0]->ltId : null;
            return LtResponse::json($response ?? "Record Not Found", $response ? "1002" : "1001", $response ? "200" : "100");
        }

        return self::handleLoginAttempt($username, $password, 'getUserByUsername');
    }

    private static function handleLoginAttempt($input, $password, $getter)
    {
        $input = trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
        $password = trim(htmlspecialchars($password, ENT_QUOTES, 'UTF-8'));

        $users = self::$getter($input);

        if (!$users) {
            return LtResponse::json("Record Not Found", "1001", "100");
        } 
         
        foreach ($users ?? [] as $user) {
            if ($user->isEnabled != 1) {
                $sdksal = "Account Deactivated";
                return LtResponse::json($sdksal, "1004", "100");
            }

            //just to destroy those expired token
            LtLWToken::destroyExpiredToken();

            if (LtDdm::verifyPassword($user, $password)) {
                $getActiveRoles = self::getActiveUserRole($user->roleId);
                if ( $getActiveRoles === "100"){
                    return LtResponse::json("No Role Activated for this Account", "1007", "100");
                }

                $user->roleId = $getActiveRoles;
                self::internalActivateUser($user);
                $togetTokenName = ApiTokenDetails::tokenName();
                $user->tokenName = ApiTokenDetails::tokenName();
                $user->$togetTokenName = LtSession::get('lwToken');
                LtSession::set('ltUrid', $user->roleId);
                return LtResponse::json("Login Succesful", "1002", "200",$user);
            }
        }
        return LtResponse::json("Incorrect Password", "1005", "100");
    }

    private static function getUserByUsernameOrEmail($username)
    {
        return self::$life_user->select()->where('username', '=', $username, '(')
            ->orWhere('email', '=', $username, '', ')')->get();
    }

    private static function getUserByEmail($email)
    {
        return self::$life_user->select()->where('email', '=', $email)
            ->andWhere('isEnabled', '=', '1')->get();
    }

    private static function getUserByUsername($username)
    {
        return self::$life_user->select()->where('username', '=', $username)
            ->andWhere('isEnabled', '=', '1')->get();
    }
    public static function getFormRole($formName)
    {
        $formRoleModel = new TbFormRole();
        $query = $formRoleModel->select()->where('formName', '=', $formName)->get();

        if (count($query) > 0) {
            return json_decode(json_encode([
                'encryptRole' => $query[0]->formRole
               // 'loginStatus' => $query[0]->loginStatus
            ]));
        }

        //assing anonymous defaul role
        $encryptRole = 'c81e728d9d4c2f';

        $formRoleModel->ltId = ltId();
        $formRoleModel->formName = $formName;
        $formRoleModel->formRole = $encryptRole;
        //$categories->loginStatus = 1;
        $formRoleModel->insert();

        return json_decode(json_encode([
            'encryptRole' => $encryptRole
            //'loginStatus' => 1
        ]));
    }

    public function loginAnalysis() {}

    public static function user()
    {
        self::hidInitiate();

        if (!LtSession::has('ltId')) {
            return LtResponse::json("Record not found", "1001", "100");
        }

        $id = LtSession::get("ltId");
        return LtResponse::json([
            'ltId' => $id,
            'surname' => LtSession::get("{$id}_surname"),
            'firstname' => LtSession::get("{$id}_firstname"),
            'othername' => LtSession::get("{$id}_othername"),
            'email' => LtSession::get("{$id}_email"),
            'phone' => LtSession::get("{$id}_phone"),
            'role' => LtSession::get("{$id}_role"),
            'username' => LtSession::get("{$id}_username"),
            'hash' => LtSession::get("{$id}_hash"),
            'salt' => LtSession::get("{$id}_salt")
        ], "1002", "200");
    }

    public static function check()
    {
        return LtSession::has('user_id');
    }

    public static function logout()
    {
        if (LtSession::has('user_id')) {
            LtSession::forget('user_id');
            LtSession::destroy();
            return true;
        }
        return false;
    }

    private static function setLtSession($user, $id)
    {
        LtSession::set('user_id', $id);
        LtSession::set("{$id}_email", $user->email);
        LtSession::set("{$id}_firstname", $user->firstName);
        LtSession::set("{$id}_othername", $user->otherName);
        LtSession::set("{$id}_phone", $user->phoneNumber);
        LtSession::set("{$id}_role", $user->roleId);
        LtSession::set("{$id}_surname", $user->surname);
        LtSession::set("{$id}_username", $user->username);
        LtSession::set("{$id}_salt", $user->salt);
        LtSession::set("{$id}_hash", $user->password);
    }

    static function internalActivateUser($user)
    {
        $user_id = $user->ltId ?: $user->ltId;
        $key = $user->ltId ? 'user_id' : 'ltId';

        self::setLtSession($user, $user_id);
        LtSession::set('ltUrid', $user->roleId);
        $toGetapiTokenExpiredTimeOut = ApiTokenDetails::apiTokenExpiredTimeOut();
        LtLWToken::insertLwtToken($user_id, $user->roleId,$toGetapiTokenExpiredTimeOut);
        self::updateLastAccess($user_id, $key);
    }

     static function getActiveUserRole($user)
    {
        $toTestRole = new TbRoleService();
        $enabledRole = $toTestRole ->roleUserPermissionStatus($user);
        $slkds =   json_decode($enabledRole,true);

        if($slkds['responseCategory'] == "100"){
            $dataToReturn = "100";
        } else{
            $dataToReturn =  implode(',', $slkds['responseData']);
        }
        return $dataToReturn;
    }

    public static function updateLastAccess($id, $key="")
    {
        $record = new TbUser;
        $record->updatedAt = date("Y-m-d H:i:s");
        $record->update($id, $key);
    }

    public static function loginWithId($id, $password)
    {
        self::hidInitiate();

        $id = trim(htmlspecialchars($id, ENT_QUOTES, 'UTF-8'));
        $password = trim(htmlspecialchars($password, ENT_QUOTES, 'UTF-8'));

        $user = self::$life_user->findId($id);
        if (LtDdm::verifyPassword($user, $password)) {
            LtLWToken::destroyExpiredToken();
            self::internalActivateUser($user);
            return LtResponse::json($user, "1002", "200");
        }

        return LtResponse::json("Incorrect Password", "1005", "100");
    }

    
} 