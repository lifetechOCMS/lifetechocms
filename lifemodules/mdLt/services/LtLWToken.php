<?php

class LtLWToken
{
  public static function insertLwtToken($userId, $user_role = "", $expireTime = "1100")
    {
        $token = bin2hex(random_bytes(16));

        $tokenModel = new Tb_lwt_tokenization;
        $tokenModel->token = $token;
        $tokenModel->user_id = $userId;
        $tokenModel->lifetech_table_status = '1';
        $tokenModel->user_role = $user_role;
        $tokenModel->expireTime = date("Y-m-d H:i:s", time() + $expireTime);
        $tokenModel->created_at = date("Y-m-d H:i:s");
        $tokenModel->lifetech_general_id = lifetech_general_id();
        $tokenModel->insert();

        LtSession::set('lw_token', $token);
        return LtResponse::json("Insert successfully", "203", "200");
    }
    

    public static function isValid($token, $expireTime = "1100")
    { 
        $tokenization_instance = new Tb_lwt_tokenization;
    
        $sql_query = $tokenization_instance->select()
            ->where('token', '=', $token)
            ->andWhere('lifetech_table_status', '=', '1')
            ->get();
    
        if (count($sql_query) === 0) {
            return LtResponse::json("Incorrect LWT Token", "196", "100");
        }
    
        $tokenization = $sql_query[0];
        $tokenId = $tokenization->lifetech_general_id;
        $expirationTime = strtotime($tokenization->expireTime);
    
        if (time() > $expirationTime) {
            // Optional: Delete expired token if needed
            // $tokenization_instance->delete('lifetech_general_id', '=', $tokenId);
            return LtResponse::json("LWT Token Expired", "197", "100");
        }
    
        // Extend expiration
        $newExpirationTime = date("Y-m-d H:i:s", time() + (int)$expireTime);
        $tokenization_instance->expireTime = $newExpirationTime;
        $tokenization_instance->last_updated = date("Y-m-d H:i:s");
        $tokenization_instance->update('lifetech_general_id', '=', $tokenId);
    
        $responseData = [
            'user_role' => $tokenization->user_role,
            'user_id' => $tokenization->user_id
        ];
    
        return LtResponse::json("LW_Token updated", "201", "200", $responseData);
    
    }


   

}

?>
      
      
      
      