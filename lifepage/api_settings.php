<?php

 header("Access-Control-Allow-Origin: *");
 //header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE,PATCH, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With,Origin, Content-Type, X-Auth-Token , Authorization");
    header("Access-Control-Allow-Credentials: true");
  //  header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
    header('Access-Control-Max-Age: 1000');
    //header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token , Authorization');
    
    

//   header("Access-Control-Allow-Origin: *"); 
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) &&
       $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET')  {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers:Role,Origin , Content-Type');
  }
  exit;
}

?>