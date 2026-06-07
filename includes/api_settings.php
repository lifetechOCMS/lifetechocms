<?php 

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
// You could also whitelist here instead of allowing all
header("Access-Control-Allow-Origin: $origin");
header("Vary: Origin");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-Auth-Token, Origin");
header("Access-Control-Max-Age: 86400");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}




/* 





header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE,PATCH, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With,Origin, Content-Type, X-Auth-Token , Authorization");
header("Access-Control-Allow-Credentials: true");
header('Access-Control-Max-Age: 1000'); 
//   header("Access-Control-Allow-Origin: *"); 
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) &&
       $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET')  {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers:Role,Origin , Content-Type');
  }
  exit;
}




if you dont want some user agent to connect to your api you can use this format sample of postman
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

if (stripos($userAgent, 'Postman') !== false) {
    http_response_code(403);
    exit('Access denied');
}
*/

?>