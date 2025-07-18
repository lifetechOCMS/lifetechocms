{! import('mdLtLogin', 'LtService.php') !}

<?php

class TbRegistrationsController{
    
    public function login(){
        ob_end_clean();
            $request = new LtRequest;
            $userN = trim(htmlspecialchars($request->username, ENT_QUOTES, 'UTF-8'));
            $passW = trim(htmlspecialchars($request->password, ENT_QUOTES, 'UTF-8'));
            $loginUser = LtAuth::login($userN, $passW);
            return $loginUser;
        exit();
    }
    
    
    public function signup(){
        
        ob_end_clean();
         
        $dataModelService = new LtService();
        $response = $dataModelService->register();
    
        return $response;

        exit();
    }
    
    public function changePassword(){
        
        ob_end_clean();
         
        $dataModelService = new LtService();
        $response = $dataModelService->change();
    
        return $response;

        exit();
    }
    
    public function sendToken(){
        
        ob_end_clean();
         
        $dataModelService = new LtService();
        $response = $dataModelService->forgotPassword();
    
        return $response;

        exit();
    }
    public function validateToken(){
        
        ob_end_clean();
         
        $dataModelService = new LtService();
        $response = $dataModelService->verifyToken();
    
        return $response;

        exit();
    }
    
    public function setPassword(){
        
        ob_end_clean();
         
        $dataModelService = new LtService();
        $response = $dataModelService->reset();
    
        return $response;

        exit();
    }
  
} // End of class



?> 