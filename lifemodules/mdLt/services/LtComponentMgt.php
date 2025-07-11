<?php  

if(!class_exists('tb_component_mgt')){
    class  tb_component_mgt extends LtModel{ }
}

class LtComponentMgt{
    
    static function publish($names=""){
        global  $lifetech_ses_role_mvc176; 
        $newconnect = DbConnect::dbDriver(); 
        $qrypageuu= $newconnect->prepare("select * from tb_component_mgt  WHERE component_name='$names'");
        $qrypageuu->execute();
            if ($qrypageuu-> Rowcount() >0 ){ 
                $qrypageuu= $newconnect->prepare("select * from tb_component_mgt  WHERE component_name='$names' AND $lifetech_ses_role_mvc176");
                $qrypageuu->execute();
                    if ($qrypageuu-> Rowcount() >0 ){ 
                        $AccehhhssDenied='';
                        return true;
                    }else{
                        return false;
                    }
            }else{
                $lifetech_general_id= lifetech_general_id();
                $qrypageuu= $newconnect->prepare("insert into tb_component_mgt (lifetech_general_id, lifetech_table_status,component_name) VALUES ('$lifetech_general_id','1','$names') ");
                $qrypageuu->execute();
                return false;
            }
      
    }
    
    
}

/*
*** GO to view page and test me

*/
?>

      
      
      
      