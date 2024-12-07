<?php  
  
  // Remote file url
  $rFile = 'setup.php';
  // Open the file
  $check = @fopen($rFile, 'r');
  // Check if the file exists
  if(!$check){
 /* 
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
*/


   $url_access = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'],'/')) : '/';

    if ($url_access == '/')
    {
        //this is the index page

session_start();
//session_destroy();
$current_file_name2 = 'index.php';
$current_file_name3 = 'lifepage/index.php'; 
 

require_once $current_file_name3; 
//include('includes/controller.php'); 
    }else{    
        

include('includes/connect_config.php'); 
try{$connect2db_new = new PDO("mysql:dbname=$dbname; host=$host; charset=latin1", $username, $password);$connect2db_new->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);$connect2db_new->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);if($connect2db_new){global $connect2db_new;
try{
    
$rfg = substr($_SERVER['PATH_INFO'],1);


$myString = 'Hello, there!';
$tocheck_url_vul = 'good';
if ( strstr( $rfg, "'" ) ) {$tocheck_url_vul = 'bad';} if ( strstr( $rfg, "*" ) ) {$tocheck_url_vul = 'bad';} if ( strstr( $rfg, "=" ) ) {$tocheck_url_vul = 'bad';} if ( strstr( $rfg, "**" ) ) {$tocheck_url_vul = 'bad';} 

    if($tocheck_url_vul == "bad"){
        require_once 'lifepage/Error_419.html';
        exit();
    }else{
    $get_page_sfhjads= $connect2db_new->prepare("select * from lifepage where pageurl_new = '$rfg' ");
    $get_page_sfhjads->execute();
     if($get_page_sfhjads->rowcount() > 0){
          $get_page_sfhjadsROW=$get_page_sfhjads->fetch(PDO::FETCH_OBJ);
            $get_page_sfhjadsROWContentValuess=$get_page_sfhjadsROW->pageurl;
            $get_page_module_nameEEEE=$get_page_sfhjadsROW->module_name;
            $current_file_name = trim($get_page_sfhjadsROWContentValuess);
            $current_file_name = trim($current_file_name);  $current_file_name2 = trim($current_file_name);
         //   echo $current_file_name;
         $godderde= 'lifepage/'.$current_file_name;
            if (file_exists($godderde)) { 
                require_once 'lifepage/'.$current_file_name;
            } else {
                require_once 'lifemodules/'.$get_page_module_nameEEEE.'/content_pages/'.$current_file_name;
            }
        
        //echo  '<br>'.basename($_SERVER['PHP_SELF']).'urladdress';
        

        }else{ 
            require_once 'lifepage/Error_404.html';
        }   
    }
}catch(PDOException $e){echo'No '.$e->getMessage(); exit;}
}}catch(PDOException $e){echo'No Data Connection Available';exit;}
        
    }
 
 }else{
    echo'<script>window.location="setup.php";</script>';
  } 



?>  