<?php
ltImport('mdLt','TbUserService.php');

class TbUserController{
    
public function login()
{
    // Automatically parse request input (GET/POST/JSON)
    $request = new LtRequest;

    // Validate input
    LtValidator::validate((array) $request, [
        'username' => 'required|min:3',
        'password' => 'required|min:6'
    ]);

    // Attempt login
    $auth = LtAuth::login($request->username, $request->password);

    // Return JSON response
    return LtResponse::json([
        'success' => true,
        'user' => $auth,
    ]);
}
    
}
?>
      
      