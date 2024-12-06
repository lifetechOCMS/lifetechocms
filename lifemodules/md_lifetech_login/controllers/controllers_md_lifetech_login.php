<?php    

if(Controller::post('userLogins', 'LoginUser')){
     ob_end_clean();
    // $userTable = new Tb_registrations;
    $request = new Request;
      $userN = $request->username;
      $passW =    $request->password;
      $loginUser = Auth::login($userN, $passW);
      echo $loginUser;
     exit(); 
}

if(Controller::post('userReg', 'RegUser')){
    ob_end_clean();
     
    //  instantiating tb_registration class
     $register_user = new Tb_registrations;
     $request = new Request;
     $reg_id = lifetech_general_id();
    
    //creating of Password salt and hash for security login
        $Password = trim(htmlspecialchars($request->lifetech_password, ENT_QUOTES, 'UTF-8'));
        $togetPasswordDetails =Auth::createPassword($Password);
        $salt = $togetPasswordDetails->salt;
        $encrypt = $togetPasswordDetails->hash;
     
    //Loading of form Role 
         $getformRoleData = Auth::getFormRole('lifetech_user_data');
           $roleEncryption = $getformRoleData->encryptRole;
           $activate_status = $getformRoleData->loginStatus;
           
     $Username = trim(htmlspecialchars($request->lifetech_username, ENT_QUOTES, 'UTF-8'));
     $Email =    trim(htmlspecialchars($request->lifetech_email, ENT_QUOTES, 'UTF-8'));
    
    //   for registration table
      $register_user->lifetech_general_id = $reg_id;
      $register_user->lifetech_surname = trim(htmlspecialchars($request->lifetech_surname, ENT_QUOTES, 'UTF-8'));
      $register_user->lifetech_firstname = trim(htmlspecialchars($request->lifetech_firstname, ENT_QUOTES, 'UTF-8')); 
      $register_user->lifetech_phone_number = trim(htmlspecialchars($request->lifetech_phone, ENT_QUOTES, 'UTF-8'));
      $register_user->lifetech_email = trim(htmlspecialchars($request->lifetech_email, ENT_QUOTES, 'UTF-8'));
      $register_user->lifetech_username = $Username;
      $register_user->salt = $salt;
      $register_user->lifetech_password = $encrypt;
      $register_user->form_type = 'tb_registration';
      $register_user->activation_status = $activate_status;
      $register_user->role_encrypt = $roleEncryption;
      //$register_user->lifetech_role = $roleText;
        $querySingleUser = $register_user->select()->where('lifetech_username', '=' , $Username)->orWhere('lifetech_email', '=' , $Email)->get();
        if (count($querySingleUser) > 0) {
            $response = Response::json($response_result="Account Already Available, Please Use Another Username or Email.......", $response_code="108", $response_category="100");
        }else {
            $register_user->insert();
            $response = $register_user->response_json;
        }
            echo $response;
     exit(); 
}



?>
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      
      