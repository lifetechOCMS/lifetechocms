<?php 
include('../../includes/connect2db.php');

 $snn=$_GET['sn'];
  $qry= $connect2db->prepare("select * from theme_tbl where sn = '$snn' ");
$qry->execute();
$row=$qry->fetch(PDO::FETCH_ASSOC);

?>

<a href="themeCont.php">&nbsp<< Click here to go back</a>

<form action="" post="method">
	<table border="1" width="600px">
        <tr>
        	<td width="600px" height="500px"><img  width="1200px" height="700px" src="../../lifepage/themes/lifeblock/<?php echo $row['lifelayout'] ?>"/></td>        
        </tr>
        <tr>
        	
            <td width="" height="px">Theme Name: <b><i style="color:#FF0000"><?php echo $row['theme_name'] ?></i></b></td>       
        </tr>
        <tr>
            <td width="" height="px">Theme Description: <b><i style="color:#FF0000"><?php echo $row['themedesc'] ?></i></b></td>       
        </tr>
        
	</table>

</form>
