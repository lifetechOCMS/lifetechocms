    <head>
        <meta charset="utf-8" />
        <title>Tech And All.com - Flat Color CSS Drop Menu</title>
        
    
            <link href="http://localhost/com/admin/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">    
                  	
        <link rel="stylesheet" href="http://localhost/com/admin/tableconfig/bootstrap/css/bootstrap.min.css">
    
       		<link rel="stylesheet" href="http://localhost/com/admin/navbar_techandall.css"> <!-- Navigation CSS -->
        
<script src="http://localhost/com/admin/tableconfig/plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="http://localhost/com/admin/tableconfig/bootstrap/js/bootstrap.min.js"></script>
<!-- DataTables for searching and sorting -->
<script src="http://localhost/com/admin/tableconfig/plugins/datatables/jquery.dataTables.min.js"></script>
<!-- FOR nexting records -->
<script src="http://localhost/com/admin/tableconfig/plugins/datatables/dataTables.bootstrap.min.js"></script> 
<link rel="stylesheet" href="http://localhost/com/admin/tableconfig/plugins/datatables/dataTables.bootstrap.css">

 
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


<div class="container" style="width:100%; background-color:#999999">
<div>
              <label class="mobile_menu" for="mobile_menu">
              <span>Menu</span>
              </label>
              <input id="mobile_menu" type="checkbox">
              <ul class="nav" style="background-color:#CCCCCC;"  style="font-size:25px">
              <li  style="font-size:25px"><a href="../"><i class="icon-home icon-large"></i>Back to Site</a></li>
             
             
            <!----- Full Drop Down Name ----> 
              <li style=".nav >li:hover {
	background-color:#999999;
}" class="dropdown"><a href="#" style="font-size:25px">Content &nbsp;&nbsp;&nbsp;</a>     
            <!----- Full Drop Down Name Ends Here ---->     
                         
            <!----- Full Drop Down Contents  Starts Here---->     
                  
              <div class="fulldrop" >              
                <div class="column" >
                  <br>
                  <ul >
                    <li ><a href="http://localhost/com/admin/content/LifePage.php">Life Page</a></li>
                    <li><a href="http://localhost/com/admin/content/lifeContent.php">Life Content</a></li>
                    <li><a href="#">PSD</a></li>
                    <li><a href="#">Scripts</a></li>
                  </ul>
                </div>
                

                        <!----- Full Drop Down Name ----> 
               <li style=".nav >li:hover {
	background-color:#999999;
}" class="dropdown"><a href="#" style="font-size:25px">Structures &nbsp;&nbsp;</a>      
            <!----- Full Drop Down Name Ends Here ---->     
                         
            <!----- Full Drop Down Contents  Starts Here---->     
                  
              <div class="fulldrop">              
                <div class="column">
                  <br>
                  <ul>
                    <li><a href="http://localhost/com/admin/structures/menuMgt.php">Menu Management</a></li>
                    <li><a href="#">Modules</a></li>
                    <li><a href="#">PSD</a></li>
                    <li><a href="#">Scripts</a></li>
                  </ul>
                </div>
              </div>
              </li>
              <!----- Full Drop Down Contents  Ends  Here---->   
              
          <li style=".nav >li:hover {
	background-color:#999999;
}" class="dropdown"><a href="#" style="font-size:25px">Users
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>      
            <!----- Full Drop Down Name Ends Here ---->     
                         
            <!----- Full Drop Down Contents  Starts Here---->     
                  
              <div class="fulldrop">              
                <div class="column">
                  <br>
                  <ul>
                    <li><a href="http://localhost/com/admin/users/LifeUser.php">Life User</a></li>
                    <li><a href="http://localhost/com/admin/users/LifeUserRole.php">Life User Role</a></li>
                    <li><a href="http://localhost/com/admin/users/LifeUserPermis.php">Life User Permission</a></li>
                  </ul>
                </div>
              </div>
              </li>    
          
        <!----- Regular Menu Button ---->   
         
              
              <!----- Full Drop Down Appearance  Starts Here---->     
                  
               <!----- Full Drop Down Name ----> 
               <li style=".nav >li:hover {
	background-color:#999999;
}" class="dropdown"><a href="#" style="font-size:25px">Appearance &nbsp;&nbsp;</a>      
            <!----- Full Drop Down Name Ends Here ---->     
                         
            <!----- Full Drop Down Contents  Starts Here---->     
                  
              <div class="fulldrop">              
                <div class="column">
                  <br>
                  <ul>
                    <li><a href="#">Themes</a></li>
                    <li><a href="http://localhost/com/admin/appearance/block.php">Block</a></li>
                    <li><a href="http://localhost/com/admin/appearance/blkPgCont.php">Block Content</a></li>
                    <li><a href="#">Scripts</a></li>
                  </ul>
                </div>
              </div>
              </li>
        <!----- Regular Menu Button Ends---->  
           
         
          <!----- Regular Menu Button ---->  
               <li><a href="#"> LifeStyle</a></li>
          <!----- Regular Menu Button Ends---->  
          
              
          <!----- Regular Menu Button ---->  
               <li><a href="#"> About</a></li>
          <!----- Regular Menu Button Ends---->  
      
                <!----- Regular Menu Button ---->  
               <li><a href="#"> Contact</a></li>
          <!----- Regular Menu Button Ends---->  
                
           
          
           <!----- Search Form Starts---->        
              <li class="search"><form action="Search.php">
                  <i class="icon-search icon-large"></i><input type="text">
                  </form>
              </li>              
          <!----- Search Form Ends ---->    
             
                 
          </ul> 
        </div>
      </div>
      