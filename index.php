<?php  
  
  // Remote file url 
require_once("includes/connect2.php");
$rFile = 'includes/setup.php';
  // Open the file
$check = @fopen($rFile, 'r');
  
// Check if the file exists
if(!$check){

include("bootstrap.php");

$urlAccess = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'],'/')) : '/';
include("includes/url_dispatcher.php");

//
//$urlAccessInfo = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//echo $urlAccessInfo;
if ($urlAccess == '/')  {
        //this is the index page   
        $pageRoutingReturn = toCheckRoute('index.php','Yes'); 
        if ($pageRoutingReturn) {
             $pageRouting = $pageRoutingReturn->urlRoute;
            $pageRoutingInApi = $pageRoutingReturn->urlRouteInApi;
            
            $get_page_module_nameEEEE = $pageRouting->module_name;
            $current_file_name = trim($pageRouting->page_name); 
            $current_file_pagetype= $pageRouting->pagetype; 
            $current_file_name2 = trim($current_file_name);
        }

        session_start();
        include("includes/controller.php"); 
}else{  
$urlAccessInfo = substr($_SERVER['PATH_INFO'],1); 
    // Check for potential URL vulnerabilities
    $tocheck_url_vul = 'good';
    $vulnerable_patterns = ["'", "*", "=", "**", "script"];
    
    foreach ($vulnerable_patterns as $pattern) {
        if (strstr($urlAccessInfo, $pattern)) {
            $tocheck_url_vul = 'bad';
            break;
        }
    }
    // If a vulnerability is detected, show the error page
    if($tocheck_url_vul === "bad"){
        if ($_SERVER['HTTP_ACCEPT'] === 'application/json') {
                echo json_encode(["responseResult"=>"Url / Route has vulnerable characters", "responseCode"=>"199","responseCategory"=>"100"]);
            } else {                
                require_once 'lifepage/Error_419.html';
            }     
        
        exit();
    }

    try{
        $pageRoutingReturn = urlDispatcher($urlAccessInfo);

        if ($pageRoutingReturn) {
            $pageRouting = $pageRoutingReturn->urlRoute;
            $pageRoutingInApi = $pageRoutingReturn->urlRouteInApi;
             
            $get_page_module_nameEEEE = $pageRouting->module_name;
            $current_file_name = trim($pageRouting->page_name);
            $current_file_pagetype = $pageRouting->pagetype;
            $current_file_name2 = trim($current_file_name);

            session_start(); 
            include("includes/controller.php");
        } else {
            if ($_SERVER['HTTP_ACCEPT'] === 'application/json') {//return error for api
                echo json_encode(["responseResult"=>"Urls / Route Not Found", "responseCode"=>"199","responseCategory"=>"100"]);
            } else {                
                require_once 'lifepage/Error_404.html';
            }      
        }
    }catch(PDOException $e){echo'No '.$e->getMessage(); exit;}

}
}else{
    echo'<script>window.location="includes/setup.php";</script>';
} 

?>  