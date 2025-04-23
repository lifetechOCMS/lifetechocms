
<head>
  <title>Final Page</title>
  <link rel="icon" type="image/x-icon" href="../lifemedia/lifetech_favicon.png">
</head>
<br /><br /> 
<link href="../lifeplugins/plg_bootstrapV5.0.1/bootstrap.min.css" rel="stylesheet">     
  
<div  style="margin-left: 100px;margin-right: 100px"> 

<div class="card p-5 m-3 shadow-lg" >
<h2><font color="#0000FF"><img width="100px"src="../lifemedia/lifetech_favicon.png" /></img>Step3: Final Page</font></h2> 

<br />   
<?php
error_reporting(1);  
include('connect2db_setup.php');
  

 $table_default = array( 'block','category','content_editor','content_page','content_tbl','lifepage','menu_mgt','lifetechfunction','module_form','module_tbl','site_tb','slide_priority','theme_form','theme_tbl_region','user_data','user_permission','user_role'); 

    $tableList = array();
    // $result = $connect2db->query("SHOW TABLES");
    $result = $connect2db->prepare("SHOW TABLES");  
    $result ->execute();
    $table_fields = $result->fetchAll(PDO::FETCH_NUM);
  
    $kingaaeusdf ="Unseen";
    
    foreach( $table_default as $value ) {
              //  echo "Value is $value <br />";
            foreach($table_fields as $column){
                    $kjsrjwfk2i_djse = $column[0] ;
                    if ($kjsrjwfk2i_djse == $value ){
                        $kingaaeusdf ="Seen"; 
                    }

                 }
            if($kingaaeusdf != "Seen"){
              $rFileLoadnn = 'lifetech_installation_db.sql';
                echo '<h3>Installing Database tables...</h3>';         
                 $query = ''; $mnt4= '';
                          $sqlScript = file($rFileLoadnn); 
                          foreach ($sqlScript as $line) {
                          $mnt4= $mnt4 . $line;
                            $startWith = substr(trim($line), 0 ,2);
                            $endWith = substr(trim($line), -1 ,1);
                            $endWithL = substr(trim($line), -22 ,22); 
                              
                            $query = $query . $line;
                            if ($endWith == ';') {
                                if(strpos($query, 'AUTOCOMMIT') !== false){}else{ 
                                $query = substr($query,0,-1); $query.=';';
                                $qry= $connect2db->prepare($query);
                                $qry->execute();
                                // echo 'almost on it'.$query;
                                   }                              
                              $query= '';   
                            }
                          
                          }                         

            $reqUrl = $_SERVER['REQUEST_URI']; $djkasdgs = substr($reqUrl,0,-19); 
            $qry1= $connect2db->prepare("update site_tb set site_host_address='$djkasdgs'  where sn='1'");
              if($qry1->execute()){
                 
                unlink("setup.php");
                unlink("setup2.php");
                unlink("setup3.php"); 
                unlink("connect2db_setup.php"); 
                 
                 echo '<h1> Congratulations Lifetechocms Software Almost Installed ...!!!<h1>';
                 echo '<h1> You will be redirected to the Main-Page now ...!!!<h1>';
                  echo '<script>
                        setTimeout(function() {
                            window.location.href = "../admin/activator/index.php?final_activate_page=activated";
                        }, 2000); // 2000 milliseconds = 5 seconds
                    </script>'; 
                  // 
                 /*echo '<h1> You can now proceed to Secure your software with UNIQUE KEY before usage  <a href="../admin/activator/index.php?final_activate_page=activated"> Go there</a>!!!<h1>';
                 */
                 exit();
                }else{
                echo '<h1> .....Sorry Your Software Database was unable to Finalize...<br />!
                    If the problem persist, please import the database file directly into your database before retrying<br/><h1>';
                 echo '<h1>Retry!!!! <a href="setup3.php"> Again</a>!!!<h1>';
                  
                 exit();

                } 
           
            }
            $kingaaeusdf ="Unseen"; 
}

/*
 echo '<h1> Congratulations Lifetech Software Successfully Installed!!!<h1>';
   echo '<h1> You can now proceed to Secure your software with UNIQUE KEY before usage  <a href="../admin/activator/index.php?final_activate_page=activated"> Go there</a>!!!<h1>';
   
    
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ) ? "https://" : "http://";
 $djkasdgs = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
 $djkasdgs=substr($djkasdgs,0,-19);

  $qry1= $connect2db->prepare("update site_tb set site_host_address='$djkasdgs'  where sn='1'");
if($qry1->execute()){
   
unlink("setup.php");
unlink("setup2.php");
unlink("setup3.php");   
unlink("connect2db_setup.php");   
  }


  */

 



?>  

 </div>
</div>