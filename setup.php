<?php
//include('connect2db.php');
?>
<head>
  <title>Configuration Page</title>
  <link rel="icon" type="image/x-icon" href="lifemedia/lifetech_favicon.png">
</head>
<br /><br /> 
<link href="lifeplugins/plg_bootstrapV5.0.1/bootstrap.min.css" rel="stylesheet">     
<div  style="margin-left: 100px;margin-right: 100px"> 


<div class="card p-5 m-3 shadow-lg" >
<h2><font color="#0000FF"><img width="100px"src="lifemedia/lifetech_favicon.png" /></img>Step1: PhP Configuration Setup </font></h2> 

<br />


<?php /*
$host = "localhost";
$username = "root";
$password = "";
$dbname = "lifetechocms";

include('connect2.php');
  
*/

$maxUpload      = (int)(ini_get('upload_max_filesize'));
$maxPost        = (int)(ini_get('post_max_size'));
$maxtime      = (ini_get('max_execution_time'));
//echo $maxtime  ;
 
 $a=0;$b=0;$c=0;
$togget = '';
if ($maxUpload < 40){
//echo ' <br /> <font color="#FF0000"; size="6px"> Upload Max Size</font>   <small> should not be less than 40M</small><br />';
  $Res_maxUpload =' <br /> <font color="#FF0000"; size="6px"> Upload Max Size</font>   <small> should not be less than 40M</small><br />';
 }else{$a=2;
 $Res_maxUpload = ' <h2><div style="color:#00CC00"> Upload Max Size</div></h2>';
 }

if ($maxPost < 40){
//echo ' <br /> <font color="#FF0000"; size="6px"> Post Maximum Size</font>   <small> should not be less than 40M</small><br />';
  $Res_maxPost =' <br /> <font color="#FF0000"; size="6px"> Post Maximum Size</font>   <small> should not be less than 40M</small><br />';
 }else{$b=3;
   $Res_maxPost ='<h2> <div style="color:#00CC00"> Post Maximum Size</div></h2>';
 //echo '<h2> <div style="color:#00CC00"> Post Maximum Size</div></h2>';
 }
if ($maxtime < 120){ 
  $Res_maxtime =' <br /> <font color="#FF0000"; size="6px">  Maximum Execution Time</font>   <small> should not be less than 120sec</small><br />';
 // echo ' <br /> <font color="#FF0000"; size="6px">  Maximum Execution Time</font>   <small> should not be less than 120sec</small><br />';

 }else{$c=4;
 $Res_maxtime = ' <h2><div style="color:#00CC00">Maximum Execution Time </div></h2>';
 }
  if($a==2 && $b==3 && $c==4){
    echo '<h2>Bravo!!, Configuration checked and passed</h2><br>';
      echo $Res_maxUpload;
      echo $Res_maxPost;
      echo $Res_maxtime;
      echo '<br /><br /><a type="button" class="btn btn-primary " style="width:300px" href="setup2.php">Verified!!!, Click Next </a>';
  }else{
    ?>
<div>
<a href="setup.php">Click Back to Database Integration</a>
<br /><strong>Note:</strong> In respective of PhP version you are using, You are to search for <b>PhP.ini Configuration</b> file in your server directories and adjust require paramatemer on <b>RED</b> font to meet atleast the minimum requirement on <strong>Black</strong> font infront of them..<br /> 
Once you change them <strong>restart</strong> your server then those parameters on<strong> RED</strong> font will change to <strong>GREEN</strong> font
<br /><br />Search for: <strong>upload_max_filesize, &nbsp;post_max_size, &nbsp;max_execution_time</strong> to adjust the value </div>

<div>
<br />How to Setup your PhP Configuration Guide in <strong>XAMPP</strong>  for Lifetech Usage: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;   <a href="How to change Configuration settings in PhP for lifetech usage.pdf" target="_blank">Click this</a><br />
</div>
<div><br>
Please refresh This Page after you have succesfuly change the Configuration settings and you have restarted your XAMPP Machine
</div>
<br />

<?php
      echo $Res_maxUpload;
      echo $Res_maxPost;
      echo $Res_maxtime;
  }
?>    

 
</div>
</div>






