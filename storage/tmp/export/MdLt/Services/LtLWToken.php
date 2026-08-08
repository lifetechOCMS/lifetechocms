<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbLwtTokenization;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;




class LtLWToken
{
  public static function insertLwtToken($userId, $user_role = "", $expireTime = "1100")
    {
        $token = bin2hex(random_bytes(16));

        $tokenModel = new TbLwtTokenization;
        $tokenModel->token = $token;
        $tokenModel->userId = $userId;
        $tokenModel->isEnabled = '1';
        $tokenModel->userRole = $user_role;
        $tokenModel->expireTime = date("Y-m-d H:i:s", time() + $expireTime);
        $tokenModel->createdAt = date("Y-m-d H:i:s");
        $tokenModel->ltId = lifetech_general_id();
        $tokenModel->insert();

        LtSession::set('lwToken', $token);
        return LtResponse::json("Insert successfully", "1101", "200");
    }
    

    public static function isValid($token, $expireTime = "1100")
    { 
        if(empty($token)) return LtResponse::json("Token must not be empty", "1105", "100");
        $tokenization_instance = new TbLwtTokenization;
    
        $sql_query = $tokenization_instance->select()
            ->where('token', '=', $token) 
            ->get();
    
        if (count($sql_query) === 0) {
            return LtResponse::json("LWT Token Not Found", "1102", "100");
        }
    
        $tokenization = $sql_query[0];
        $tokenId = $tokenization->ltId;
        $expirationTime = strtotime($tokenization->expireTime);
    
        if (time() > $expirationTime) {
            // Optional: Delete expired token if needed
            // $tokenization_instance->delete('lifetech_general_id', '=', $tokenId);
            return LtResponse::json("LWT Token Expired", "1103", "100");
        }
        if ($tokenization->isEnabled != "1") {
            // Optional: Delete expired token if needed
            // $tokenization_instance->delete('lifetech_general_id', '=', $tokenId);
            return LtResponse::json("Token Disabled", "1106", "100");
        }
    
        // Extend expiration
        $newExpirationTime = date("Y-m-d H:i:s", time() + (int)$expireTime);
        $tokenization_instance->expireTime = $newExpirationTime;
        $tokenization_instance->lastUpdated = date("Y-m-d H:i:s");
        $tokenization_instance->update('lt_id', '=', $tokenId);
    
        $responseData = [
            'userRole' => $tokenization->userRole,
            'userId' => $tokenization->userId
        ];
    
        return LtResponse::json("LWT Token updated", "1104", "200", $responseData);
    
    }
    
    
     public static function destroy($token)
    { 
        if(empty($token)) return LtResponse::json("Token must not be empty", "1105", "100");
        $tokenization_instance = new TbLwtTokenization;
        $tokenization_instance->token = $token;
        $sql_query = $tokenization_instance->delete('token',$token); 
    
        return LtResponse::json("LWT Token deleted", "1107", "200", $responseData);
    
    }
     
    
    public static function destroyExpiredToken( ){
        $tokenization_instance = new TbLwtTokenization;
        $currentTime = date("Y-m-d H:i:s");
         $tokenization_instance->delete('expireTime', '<', $currentTime); 
    
        return LtResponse::json("LWT Token deleted", "1107", "200");
    }

   

}