<?php
 
include('../includes/connect2db.php');

if(isset($_POST['checkedMenu'])){
    //this function is for role, services and quicklinks
$a=$_POST['checkedMenu']; $status=$_POST['chkMenu'];

$myArray = explode(',', $a);

$record= $myArray[0];
$role= $myArray[1];
$pageurl_new= $myArray[2];
$page_name= $myArray[3];
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/

$qry1= $connect2db->prepare("update menu_mgt set `$role`='$status' where menuid='$record'");
if($qry1->execute()){   }
if ($status == '1'){


$content_name_uis= $connect2db->prepare("select * from content_page  where page_name= '$page_name'");
  $content_name_uis->execute();
if($content_name_uis->RowCount() > 0  ){
 
    while ($row_content_name_uis=$content_name_uis->fetch(PDO::FETCH_ASSOC)){
        $deb_cont_id= $row_content_name_uis['contid'];
    
     $qry1_go = $connect2db->prepare("update content_tbl set `$role`='$status' where randomno='$deb_cont_id'");
if($qry1_go->execute()){    }

    }



}


$qry1= $connect2db->prepare("update lifepage set `$role`='$status', `status`= 'Published' where pageurl_new='$pageurl_new'");
if($qry1->execute()){   }   
    
    
    
}





}

if(isset($_POST['checkedDevMgtPage'])){
    //this function is for role, services and quicklinks
$a=$_POST['checkedDevMgtPage']; $status=$_POST['chkDevMgtPage'];

$myArray = explode(',', $a);

$encryptpage= $myArray[0];
$uridd= $myArray[1]; 
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/

$qry1= $connect2db->prepare("update bked_content_editor set `$uridd`='$status' where content_name='$encryptpage'");
if($qry1->execute()){    
 

   
    
    
    
}

}


if(isset($_POST['checkedCompMgtPage'])){
    //this function is for role, services and quicklinks
$a=$_POST['checkedCompMgtPage']; $status=$_POST['chkCompMgtPage'];

$myArray = explode(',', $a);

$encryptpage= $myArray[0];
$uridd= $myArray[1]; 
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/

$qry1= $connect2db->prepare("update tb_component_mgt set `$uridd`='$status' where component_name='$encryptpage'");
if($qry1->execute()){    
     
}

}

 if(isset($_POST['subfunctionextractw'])){
 $subfunctionextract=$_POST['subfunctionextractw'];
 echo 'thanks'.$subfunctionextract;
 }

 if(isset($_POST['MenuLevel'])){
$menulevel = $_POST['MenuLevel'];
if($menulevel == 2){
  $menulevel= $connect2db->prepare("select * from menu_mgt  where level= 1");
  $menulevel->execute();
if($menulevel->RowCount() > 0  ){
 
    while ($rowlevel=$menulevel->fetch(PDO::FETCH_ASSOC)){
    $b= $rowlevel['menu_name'];     $deb= $rowlevel['randomno'];
    
    echo '<option  value="'.$deb.'">'.$b.'</option>';
    }}else{
    $b ="You Have not Registered Level 1 Menu";
    echo '<option value="'.$deb.'">'.$b.'</option>';
    }
    
    


}elseif($menulevel == 3){
 $menulevel= $connect2db->prepare("select * from menu_mgt  where level= 2");
  $menulevel->execute();
if($menulevel->RowCount() > 0  ){
 
    while ($rowlevel=$menulevel->fetch(PDO::FETCH_ASSOC)){
    $b= $rowlevel['menu_name']; $deb= $rowlevel['randomno'];
    
    echo '<option  value="'.$deb.'" >'.$b.'</option>';
    }}else{
    $b ='You Have not Registered Level 2 Menu';
    echo '<option  value="'.$deb.'" >'.$b.'</option>';
    }

}

 }
 if(isset($_POST['ThemeLevel'])){
$menulevel = $_POST['ThemeLevel'];$menuname = $_POST['ThemeName'];
if($menulevel == 2){ 
//echo '<option  value="'.$deb.'">'.$b.'</option>';
  $menulevel= $connect2db->prepare("select * from theme_tbl_region  where level= 1 and theme_name='$menuname'");
  $menulevel->execute();
if($menulevel->RowCount() > 0  ){
 echo '<option  value="'.$deb.'">'.$b.'</option>';
    while ($rowlevel=$menulevel->fetch(PDO::FETCH_ASSOC)){
    $b= $rowlevel['url_name'];  $deb= $rowlevel['url_name'];
    
    echo '<option  value="'.$deb.'">'.$b.'</option>';
    }}else{
    $b ="You Have not Registered Level 1 Menu";
    echo '<option value="'.$deb.'">'.$b.'</option>';
    }
    
    


}elseif($menulevel == 3){
 $menulevel= $connect2db->prepare("select * from theme_tbl_region where level= 2 and theme_name='$menuname'");
  $menulevel->execute();
if($menulevel->RowCount() > 0  ){
 
    while ($rowlevel=$menulevel->fetch(PDO::FETCH_ASSOC)){
    $b= $rowlevel['url_name']; $deb= $rowlevel['url_name'];
    
    echo '<option  value="'.$deb.'" >'.$b.'</option>';
    }}else{
    $b ='You Have not Registered Level 2 Menu';
    echo '<option  value="'.$deb.'" >'.$b.'</option>';
    }

}

 }
 
if(isset($_POST['checkedContent'])){
 $a=$_POST['checkedContent']; $status=$_POST['chkContent'];
//echo $a.'aaaaaaaa<br />';
$myArray = explode(',', $a);

$record= $myArray[0];
$role= $myArray[1];
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/
$qry1= $connect2db->prepare("update content_tbl set `$role`='$status' where contid='$record'");
if($qry1->execute()){
    
    }

}
 
 if(isset($_POST['checkedPagePub'])){
 $a=$_POST['checkedPagePub']; $status=$_POST['chkPagePub'];
echo '<script language="javascript">'."alert('Operation Successfully Done!!!')"."</script>";
    
//echo $a.'aaaaaaaa<br />';
$myArray = explode(',', $a);

$record= $myArray[0];
$role= $myArray[1];
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/
$qry1= $connect2db->prepare("update lifepage set `status`='$status' where pageid='$record'");
if($qry1->execute()){
    
    }

}
 
 
 
 
 if(isset($_POST['selectthemeblock'])){
 $a=$_POST['selectthemeblock'];  
//echo $a.'aaaaaaaa<br />';
$myArray = explode(',', $a);

$record= $myArray[0];
$role= $myArray[1];
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/
$qry1= $connect2db->prepare("update theme_tbl_region set `blkid`='$role' where sn='$record'");
if($qry1->execute()){
    
    }

}
 
 
 if(isset($_POST['selectmodblock'])){
 $a=$_POST['selectmodblock'];  
//echo $a.'aaaaaaaa<br />';
$myArray = explode(',', $a);
//echo 'great'.$a;
$record= $myArray[0];
$role= $myArray[1];
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/
$qry1= $connect2db->prepare("update content_tbl set `block`='$role' where contid='$record'");
if($qry1->execute()){
    
    }

}
 
 if(isset($_POST['selectmodrole'])){
 $a=$_POST['selectmodrole'];  
//echo $a.'aaaaaaaa<br />';
$myArray = explode(',', $a);
//echo 'great'.$a;
$record= $myArray[0];
$role= $myArray[1];
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/
echo $role.' '.$record;
$qry1= $connect2db->prepare("update category set `categoryrole`='$role' where sn='$record'");
if($qry1->execute()){
    
    }

}
 
  if(isset($_POST['selectmodblocktheme'])){
 $a=$_POST['selectmodblocktheme'];  
//echo $a.'aaaaaaaa<br />';
$myArray = explode(',', $a);
//echo 'great'.$a;
$record= $myArray[0];
$role= $myArray[1];
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/
$qry1= $connect2db->prepare("update theme_tbl_region set `blkid`='$role' where sn='$record'");
if($qry1->execute()){
    
    }

}
 
 
 
 
if(isset($_POST['checkedPage'])){
 $a=$_POST['checkedPage']; $status=$_POST['chkPage'];

$myArray = explode(',', $a);

$record= $myArray[0];
$role= $myArray[1];
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/
$qry1= $connect2db->prepare("update lifepage set `$role`='$status' where pageid='$record'");
if($qry1->execute()){
    
    }

}



if(isset($_POST['checkedPagePubContent'])){
 $a=$_POST['checkedPagePubContent']; $status=$_POST['chkPagePubContent'];

$myArray = explode(',', $a);

$record= $myArray[0];
$role= $myArray[1]; 
     
if($status == '1'){
$qryy= $connect2db->prepare("select * from content_page where page_name='$role' and contid='$record'");

        $qryy->execute();
$rowss = $qryy->fetch(PDO::FETCH_ASSOC);
    if($rowss){
    
     echo '<script language="javascript">'."alert('Sorry,  It has been Published before!!!')"."</script>";
    
 

}else{
//echo 'gppd';

  $query= $connect2db->prepare("insert into content_page (page_name,contid) values (?,?)");
    $query->execute([$role,$record]);
    $query=null;
      echo '<script language="javascript">'."alert('Content Successfully Published for Current Page!!!')"."</script>";
    
   }


}else{


    
$qryy= $connect2db->prepare("Delete  from content_page where page_name='$role' and contid='$record'");
    if($qryy->execute()){
         echo '<script language="javascript">'."alert('Content Successful Unpublished for this Page!!!')"."</script>";
     
}
}
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/


}




if(isset($_POST['checkedPageSlideContent'])){
 $a=$_POST['checkedPageSlideContent']; $status=$_POST['chkPageSlideContent'];

$myArray = explode(',', $a);

$url= $myArray[0];
$page= $myArray[1]; 
$priority= $myArray[2]; 
     
if($status == '1'){
$qryy= $connect2db->prepare("select * from slide_priority where page_name='$page' and url_name='$url'");

        $qryy->execute();
$rowss = $qryy->fetch(PDO::FETCH_ASSOC);
    if($rowss){
    
     echo '<script language="javascript">'."alert('Sorry,  It has been Published before!!!')"."</script>";
    
 

}else{
//echo 'gppd';

  $query= $connect2db->prepare("insert into slide_priority (page_name,url_name, priority) values (?,?,?)");
    $query->execute([$page,$url,$priority]);
    $query=null;
         echo '<h1> <strong> Region Successful Published for this Page!!!</strong></h1>';
   }


}else{


    
$qryy= $connect2db->prepare("Delete  from slide_priority where page_name='$page' and url_name='$url'");
    if($qryy->execute()){
         echo '<h1> <strong> Region Successful Unpublished for this Page!!!</strong></h1>';
     
}
}
/*
echo $record.' recored<br />';
echo $role.' role<br />';
echo $status.' status<br />';
*/


}


if(isset($_POST['checkedmdstatus'])){
 $a=$_POST['checkedmdstatus']; $status=$_POST['chkmdstatus'];
 if ($status == 1){
 $qry1= $connect2db->prepare("update module_tbl set status='Enabled' where modid='$a'");
if($qry1->execute()){
    
    }
    
    
 } else{

 $qry1= $connect2db->prepare("select * from module_tbl  where modid='$a'");
if($qry1->execute()){
$row=$qry1->fetch(PDO::FETCH_ASSOC);
$name=$row['module_name'];

$qry12= $connect2db->prepare("select * from content_tbl  where page_name='$name'");
 $qry12->execute();
while ($row12=$qry12->fetch(PDO::FETCH_ASSOC)){
    $contdel=  $row12['contid'];
    
    $qrydel=$connect2db->prepare("delete from content_page where contid='$contdel'");
$qrydel->execute();
    

    
    }
 
 }
  $qry1= $connect2db->prepare("update module_tbl set status='Disabled' where modid='$a'");
if($qry1->execute()){
    echo '<script language="javascript">'."alert('Operation Successfully Done!!!')"."</script>";
    
    }

}}
 
?>