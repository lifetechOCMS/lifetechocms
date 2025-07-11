<?php

class LtResponse {
    public static $responseResult;
    public static $responseCode;
    public static $responseCategory;
    public static $responseData;
    protected static $allErrors=array();
    
    static function json($responseResult=array() ,$responseCode="101" ,$responseCategory="100",$responseData=array(),$responseStatus ="fail",$responseOperationType="others"){ //this function is used to update    all the response messages        
     static::$responseResult  =  $responseResult;
     static::$responseCode  =  $responseCode;
     static::$responseCategory  =  $responseCategory;
     if(empty($responseResult)){ 
            static::$responseResult  =  "Unknown";
        } 
      if(empty($responseData)){    
        return json_encode(['responseResult'=>$responseResult,'responseCode'=>$responseCode,'responseCategory'=>$responseCategory]);
    }else{
        return json_encode(['responseResult'=>$responseResult,'responseCode'=>$responseCode,'responseCategory'=>$responseCategory,'responseData'=>$responseData]);
    }
      
    }
} 

?>
      