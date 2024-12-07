 <?php  eval("?> $mmd <?php"); ?>
 
 <?php  
		 include('connect2db.php');
        
        // Append the requested resource location to the URL   
        $m= $_SERVER['REQUEST_URI']; 
          
        
		$kp=explode("/",$m );
		//echo count($kp); 
		 $ckp=count($kp);
		$ckm=0;
		$ckp-=1;
		$urlname='';
		while ($ckm < 2 ){
		 $urlpos=$kp[$ckp];
		if(strpos($urlpos,'?')){
		
		$strpos= strpos($urlpos,'?');    $urlsubstr =substr($urlpos,0,$strpos);
		$urlname.=$urlsubstr;
		}else {
		$urlname.=$kp[$ckp];}
		//echo '<br>'.$urlname.'<br>';
		$ckp-=1;
		$ckm+=1;
		}
		echo '<br>'.$urlname;    
		$urls=sha1($urlname); echo md5($urls);
		
		
		$urlnames=md5(sha1($urlname)); 
		 
$qry= $connect2db->prepare("select * from content_editor where content_name=? ");

$qry->execute([$urlnames]);

$row=$qry->fetch(PDO::FETCH_ASSOC);
  
if($row){
echo '';
$mmd=$row['content'];

$qryhd= $connect2db->prepare("select * from lifetechfunction where uid=?  and ufield=?");
$qryhd->execute(['LoadPage2','hd']);
$rowhd=$qryhd->fetch(PDO::FETCH_ASSOC);
 $mmdhd=$rowhd['uvalue'];
 eval("?> $mmdhd <?php");
 
 $qrybd= $connect2db->prepare("select * from lifetechfunction where uid=?  and ufield=?");
$qrybd->execute(['LoadPage2','bd']);
$rowbd=$qrybd->fetch(PDO::FETCH_ASSOC);
 $mmdbd=$rowbd['uvalue'];
 eval("?> $mmdbd <?php");
  $qryfd= $connect2db->prepare("select * from lifetechfunction where uid=?  and ufield=?");
$qryfd->execute(['LoadPage2','fd']);
$rowfd=$qryfd->fetch(PDO::FETCH_ASSOC);
 $mmdfd=$rowfd['uvalue'];
 eval("?> $mmdfd <?php");

//echo '<br />echos'.$mmd;

 
echo $urlnames;
	//	exit();
 //eval($mmd);
}
		
		
		
      ?>   