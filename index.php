<?php  
error_reporting(0);
include_once("includes/app.identity.php"); 
session_start(); 


// Remote file url s
$rFile = 'includes/setup.php';

// Open the file
$check = @fopen($rFile, 'r');
  
// Check if the file exists
if(!$check){
require_once("includes/DbConnect.php");
include("bootstrap.php");
include_once 'includes/api_settings.php';

$urlAccess = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'],'/')) : '/';
include("includes/url_dispatcher.php");

 
//$urlAccessInfo = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
//echo $urlAccessInfo;
if ($urlAccess == '/')  {
        //this is the index page   
        $pageRoutingReturn = toCheckRoute('index.php','Yes'); 
        if ($pageRoutingReturn) {
             $pageRouting = $pageRoutingReturn->urlRoute;
            $pageRoutingInApi = $pageRoutingReturn->urlRouteInApi;
            
            $get_page_module_nameEEEE = $pageRouting->package_name;
            $current_file_name = trim($pageRouting->page_name); 
            $current_file_pagetype= $pageRouting->pagetype; 
            $current_file_name2 = trim($current_file_name);
        }

        session_start();
        include("includes/controller.php"); 

}else if(end($urlAccess) === "force-logout"){ 
    // Clear session here...
    session_destroy();
    array_pop($urlAccess); 
    $redirectUrl = '/' . implode('/', $urlAccess);
     
    $fullUrl = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . $_SERVER['REQUEST_URI'];

    // extract path only
    $path = parse_url($fullUrl, PHP_URL_PATH);

    // convert to array
    $segments = explode('/', trim($path, '/'));

    // remove last segment
    array_pop($segments);

    // rebuild URL
    $baseUrl = (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : 'http')
        . '://' . $_SERVER['HTTP_HOST']
        . '/' . implode('/', $segments);
 
    header("Location: $baseUrl");
    exit;
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
                echo json_encode(["responseResult"=>"Url / Route has vulnerable characters", "responseCode"=>"1220","responseCategory"=>"100"]);
            } else {                
                require_once 'includes/error_pages/Error_419.html';
            }             
        exit();
    }

 
    try{

        if (str_contains($urlAccessInfo, 'admin-end')) {
            //echo "Admin-end detected in URL".$urlAccessInfo;
            $urlAccessInfo = '/'.$urlAccessInfo;
            $pageRoutingReturn = toCheckAdminRoute($urlAccessInfo); 
            if ($pageRoutingReturn) {
                $pageRouting = $pageRoutingReturn->urlRoute;
                $pageRoutingInApi = $pageRoutingReturn->urlRouteInApi;      
                $backendContentData =    $pageRoutingReturn->urlRoute; 
                $backendPageContent = $pageRoutingReturn->urlRoute->content;
                //echo       $pageRoutingReturn->urlRoute->content;
                //exit();
                //var_dump($pageRoutingReturn);
                //$get_page_module_nameEEEE = $pageRouting->package_name;
                //$current_file_name = trim($pageRouting->page_name); 
                $current_file_pagetype= "Backend"; 
                //$current_file_name2 = trim($current_file_name); 
                //session_start(); 
                include("includes/controller.php");
            }
    
        } else{
             $urlAccessInfo = '/'.$urlAccessInfo;
            $pageRoutingReturn = urlDispatcher($urlAccessInfo);
  
            if ($pageRoutingReturn) {

                $pageRoutingToRem = $pageRoutingReturn->urlRouteInApiRemaining?? null;
                if($pageRoutingToRem){
                    $urlAccessInfo = $pageRoutingToRem;
                    goto notfoud; 
                }else{
                     $pageRouting = $pageRoutingReturn->urlRoute;
                $pageRoutingInApi = $pageRoutingReturn->urlRouteInApi;
                 
                $get_page_module_nameEEEE = $pageRouting->package_name;
                $current_file_name = trim($pageRouting->page_name);
                $current_file_pagetype = $pageRouting->page_type;
                $current_file_name2 = trim($current_file_name);
    
                session_start(); 
                include("includes/controller.php");
                }
               
            } else {
                notfoud:
                if ($_SERVER['HTTP_ACCEPT'] === 'application/json') {//return error for api
                    echo json_encode(["responseResult"=>"".$urlAccessInfo." Route Not Found", "responseCode"=>"1218","responseCategory"=>"100"]);
                } else {                
                    require_once 'includes/error_pages/Error_404.html';
                }      
            }  
        }


    }catch(PDOException $e){echo'No '.$e->getMessage(); exit;}

}
}else{ //not yet installed
    
    if(getenv('LT_RUNTIME') === "docker"){
        //docker run time 
        include_once "bootstrap.php";
        include_once "includes/setup_docker_endpoint.php";
        unlink("includes/setup.php");
        unlink("includes/setup_docker_endpoint.php"); 
        echo '<script>window.location.reload();</script>';
    }else{       
        echo'<script>window.location="includes/setup.php";</script>';
    }
} 

?>  