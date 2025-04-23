<?php  
  
  // Remote file url 
require_once("includes/connect2.php");
$rFile = 'includes/setup.php';
  // Open the file
$check = @fopen($rFile, 'r');
  // Check if the file exists
if(!$check){
   $url_access = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'],'/')) : '/';
if ($url_access == '/')  {
        //this is the index page
        session_start();
        //session_destroy();
        $current_file_name2 = 'index.php';
        $current_file_name3 = 'lifepage/index.php'; 
        require_once $current_file_name3; 
        //include('includes/controller.php'); 
}else{    
    $lifetech_connect2class=  DbConnect::dbDriver();  
    try{        
        $rfg = substr($_SERVER['PATH_INFO'],1); 
        // Check for potential URL vulnerabilities
            $tocheck_url_vul = 'good';
            $vulnerable_patterns = ["'", "*", "=", "**", "script"];
            
            foreach ($vulnerable_patterns as $pattern) {
                if (strstr($rfg, $pattern)) {
                    $tocheck_url_vul = 'bad';
                    break;
                }
            }
        // If a vulnerability is detected, show the error page
        if($tocheck_url_vul == "bad"){
            require_once 'lifepage/Error_419.html';
            exit();
        }else{
        // Prepare and execute the query to check the page URL
                $get_page_sfhjads = $lifetech_connect2class->prepare("SELECT * FROM lifepage WHERE pageurl_new = :rfg");
                $get_page_sfhjads->bindParam(':rfg', $rfg);
                $get_page_sfhjads->execute();

         if($get_page_sfhjads->rowcount() > 0){
            $get_page_sfhjadsROW=$get_page_sfhjads->fetch(PDO::FETCH_OBJ);
            $get_page_sfhjadsROWContentValuess=$get_page_sfhjadsROW->pageurl;
            $get_page_module_nameEEEE=$get_page_sfhjadsROW->module_name;
            $current_file_name = trim($get_page_sfhjadsROWContentValuess);
            $current_file_name = trim($current_file_name);  $current_file_name2 = trim($current_file_name);
            //   echo $current_file_name;
            session_start();
            include("includes/controller.php");            
          }else{ 
                require_once 'lifepage/Error_404.html';
          }   
        }
    }catch(PDOException $e){echo'No '.$e->getMessage(); exit;}
} 
}else{
    echo'<script>window.location="includes/setup.php";</script>';
} 



?>  