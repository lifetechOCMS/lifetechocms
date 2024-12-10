<?php
//session_start();
if ($urlnames == '73d0822e61bac3e182f43682d58e7f4b'){

}else{echo 'ggg';

?>

<style type="text/css">
.a{background-color:#33CC66;}

</style>
  <div style="background-color:#999999; padding-top:5px; padding-bottom:3px"> 
    <div style="background-color:#CCCCCC;padding-top:10px; padding-bottom:10px">
        <div style="margin-left:15px; ">
        <font size="5px" color="#FF0000" style="background-color:#FFFFFF; padding-top:5px; padding-bottom:5px"><a href="index.php">&nbsp;&nbsp;Back to Sites&nbsp;&nbsp;</a></font>
		<font size="5px" color="#FF0000" style="background-color:#FFFFFF;padding-top:5px; padding-bottom:5px"><a href="../editor/index.php" >&nbsp;&nbsp;Editor	&nbsp;&nbsp;</a></font>
         <font size="5px" color="#FF0000" style="background-color:#FFFFFF;padding-top:5px; padding-bottom:5px"><a href="../content/index.php" >&nbsp;&nbsp;Content&nbsp;&nbsp;</a></font>
       <font size="5px" color="#FF0000" style="background-color:#FFFFFF;padding-top:5px; padding-bottom:5px"><a href="../structures/index.php" >&nbsp;&nbsp;Structures&nbsp;&nbsp;</a></font>
       <font size="5px" color="#FF0000" style="background-color:#FFFFFF;padding-top:5px; padding-bottom:5px"><a href="../appearance/index.php" >&nbsp;&nbsp;Apperance&nbsp;&nbsp;</a></font>
       
       
        <font size="5px" color="#FF0000" style="background-color:#FFFFFF;padding-top:5px; padding-bottom:5px"><a href="../users/index.php">&nbsp;&nbsp;Users&nbsp;&nbsp;</a></font>

<font size="5px" color="#FF0000" style="background-color:#FFFFFF; padding-top:5px; padding-bottom:5px"><a href="../lifeTech/index.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LifeTech&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></font>

<font size="5px" color="#FF0000" style="background-color:#FFFFFF;padding-top:5px; padding-bottom:5px"><a href="../../">&nbsp;&nbsp;Log Out&nbsp;&nbsp;</a></font>
        </div>
    </div>

</div>
</div>
<?php  } ?>
    <head>
    
          
       
 <link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/com/admin/tableconfig/bootstrap/css/bootstrap.min.css">
<script src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/com/admin/tableconfig/plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/com/admin/tableconfig/bootstrap/js/bootstrap.min.js"></script>
<!-- DataTables for searching and sorting -->
<script src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/com/admin/tableconfig/plugins/datatables/jquery.dataTables.min.js"></script>
<!-- FOR nexting records -->
<script src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/com/admin/tableconfig/plugins/datatables/dataTables.bootstrap.min.js"></script> 
<link rel="stylesheet" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/com/admin/tableconfig/plugins/datatables/dataTables.bootstrap.css">

 
 <script>
  $(function () {
    $("#example").DataTable();
    $('#example2').DataTable({
      "paging": true,
      "lengthChange": false,
      "searching": false,
      "ordering": true,
      "info": true,
      "autoWidth": false
    });
  });
</script>
    </head>

<body  bgcolor="#CCCCCC">

