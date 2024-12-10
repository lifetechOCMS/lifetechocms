
<?php /*
$host = "localhost";
$username = "root";
$password = "";
$dbname = "lifetechocms";

include('connect2.php');
  
*/




 //$pageurl =lifetechrandom_string(150);
  
  
      
//rename ($pageurl, "$pageurl");




if(isset($_POST['dbinfo'])){
//echo 'welcome';
$host = $_POST['hostname'];
$username =  $_POST['username'];
$password = $_POST['password'];
$dbname = $_POST['database'];
$errror ="";
echo '<br /><br /><br /><br />';
if( empty($host) || empty($username)      || empty($dbname) ){
echo '<div  style="margin-left:100px"><font   color="#FF0000"><h2> Your elements must not be empty</h2></font></div>';
}else{

try{$connect2db = new PDO("mysql:dbname=$dbname; host=$host", $username, $password);
$connect2db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);$connect2db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if($connect2db){//global $connect2db;

      

}

}catch(PDOException $e){
    try {
        // Connect to MySQL as a root/admin user
        $pdo = new PDO('mysql:host=localhost', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Define the database, new user, and credentials
      

        $database =$dbname;
        $newUser = $username;
        $newHost = $host; // or '%' for any host
        $newPassword = $password;

        // SQL to create the database
        $createDatabaseSQL = "CREATE DATABASE IF NOT EXISTS $database";

        // SQL to create the user
        $createUserSQL = "CREATE USER :username@:host IDENTIFIED BY :password";

        // SQL to grant all privileges on the new database to the new user
        $grantPrivilegesSQL = "GRANT ALL PRIVILEGES ON $database.* TO :username@:host";
        //$grantPrivilegesSQL = "GRANT ALL PRIVILEGES ON *.* TO 'lifetech_ocms_todeleteu'@'localhost'";
        //$grantPrivilegesSQL = "GRANT ALL PRIVILEGES ON *.* TO :username@:host ";
        
        // Execute the CREATE DATABASE statement
        $pdo->exec($createDatabaseSQL);
       // echo "Database `$database` created successfully.\n";

        // Prepare and execute the CREATE USER statement
        $stmt = $pdo->prepare($createUserSQL);
        $stmt->execute([
            ':username' => $newUser,
            ':host' => $newHost,
            ':password' => $newPassword,
        ]);
        //echo "User `$newUser` created successfully.\n";

        // Prepare and execute the GRANT PRIVILEGES statement
        $stmt = $pdo->prepare($grantPrivilegesSQL);
        $stmt->execute([
            ':username' => $newUser,
            ':host' => $newHost,
        ]);
        //echo "Privileges granted to `$newUser` on `$database`.\n";

        // Flush privileges to apply changes
        $pdo->exec('FLUSH PRIVILEGES');
     // echo "Privileges flushed successfully.\n";
    } catch (PDOException $e) {
       $errror = "Error: " . $e->getMessage();
    }

    if ($errror != ""){
    echo'<div  style="margin-left:100px"><font   color="#FF0000"><h3>'.$errror.'</h3</font><br> <h2><font   color="blue">I will advise you create the Database and assign the user Privilege directly on your DBMS before you continue!!</font></h2></div>';
    goto secontinu; 
    }else{
      
    } 
}
// to write the connection files

       $pageurl= 'connect2db_setup.php'; 
        
        $fh = fopen($pageurl,"w");
          
          $content='<?php     

      $host = "'.$host.'";
      $username =  "'.$username.'";
      $password = "'.$password.'";
      $dbname = "'.$dbname.'";



      try{$connect2db = new PDO("mysql:dbname=$dbname; host=$host", $username, $password);
      $connect2db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);$connect2db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      if($connect2db){

      }}catch(PDOException $e){echo "The Database Information Supplied is not Correct, Reverify!!!";}        
      ?>';
      fwrite($fh,$content);
            fclose($fh);
            
      $pageurldelete= 'connect2dbdelete.php'; 
        
        $fhdelete = fopen($pageurldelete,"w");
          
          $contentdelete='<?php 
       

      $host = "'.$host.'";
      $username =  "'.$username.'";
      $password = "'.$password.'";
      $dbname = "'.$dbname.'";


      class DbConnect {
          private $dbengine   = \'mysql\';
          private $dbhost     = "'.$host.'";
          private $dbuser     = "'.$username.'";
          private $dbpassword = "'.$password.'";
          private $dbname     = "'.$dbname.'";
          public  $dbh = null;
          public  static $dbDriver_nn = null;

          public function __construct() {
              try {
                  // since you are extending PDO, you have to call its constructor
                  $connect2db_iii = new PDO($this->dbengine.":host=$this->dbhost;dbname=$this->dbname; charset=latin1; ", $this->dbuser, $this->dbpassword);
                  $connect2db_iii->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                  $connect2db_iii->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                  $this->dbh=$connect2db_iii;
                  self::$dbDriver_nn = $connect2db_iii;
                  //return $connect2db_iii;
                  //$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              }
              catch (PDOException $e) {
                  //echo $e->getMessage();
                  self::$dbDriver_nn = $e->getMessage();
              }
          }
           static function dbDriver(){
            new DbConnect();
            $akdfadskjsfdk = self::$dbDriver_nn;
            return $akdfadskjsfdk;
          }
      } 
      $kk=new DbConnect;

      include("connect2.php");
       
      ?>';


        fwrite($fhdelete,$contentdelete);
            fclose($fhdelete);
            rename ($pageurldelete, "connect2db.php");
            

        $pageurldelete= 'connect2dbdelete.php'; 
        $fhdelete = fopen($pageurldelete,"w");
          
          $contentdelete='<?php 
       

      $host = "'.$host.'";
      $username =  "'.$username.'";
      $password = "'.$password.'";
      $dbname = "'.$dbname.'";
       
       
      ?>';


        fwrite($fhdelete,$contentdelete);
            fclose($fhdelete);
            rename ($pageurldelete, "connect_config.php");
      echo '<div  style="margin-left:100px"><font color="#00CC33"><h2>Connection successful... 
      <br>Database is Loading..</h2> </font></div>';
      echo'<script>window.location= "setup3.php";</script>';  
     
}

      
secontinu:      


}


?> 
<head>
  <title>Database Page</title>
  <link rel="icon" type="image/x-icon" href="../lifemedia/lifetech_favicon.png">
</head>
<br /><br /> 
<link href="../lifeplugins/plg_bootstrapV5.0.1/bootstrap.min.css" rel="stylesheet">     
<div  style="margin-left: 100px;margin-right: 100px">
<script type="text/javascript" src="../jquery-3.5.1.min.js" type="text">    
 </script>
<script type="text/javascript" src="../lifeplugins/plg_bootstrapV5.0.1/bootstrap.bundle.min.js">    
 </script>

<div class="card p-5 m-3 shadow-lg" >
<form action="setup2.php" method="post">
   
<script type="text/javascript"> 
function sh(){
   $(".wgood").show();
}
</script>
  
<a href="setup.php"> <-Step1 </a>
<h2><font color="#0000FF"><img width="100px"src="../lifemedia/lifetech_favicon.png" /></img>Step2: Database Configuration </font></h2>Provide your database information<br /><br />
 
<br /><strong>Note:</strong> If you are creating a student account, then use this compulsory details so that you can be getting the general <strong><button class="btn btn-primary" type="button" onclick="sh();">software key :: click
  </button> </strong><br />
<div class="collapse wgood" id=""> 
Database Name: <strong>lifetechocms</strong><br />
Package Name: <strong>lifetech_ocms</strong><br />
Host Name:<strong>localhost</strong><br />
Username:<strong>root</strong><br />
Password:<strong>Nill</strong><br /> 
</div>
<br /> 
<br />
<div class="row">
  <div class="col-12 col-md-1"><label class="">Username:</label></div>  
  <div class="col-12 col-md-5">
    <input type="text" class="form-control " width="60px" name="username"/>   <br>
  </div>
</div> 
<div class="row">
  <div class="col-12 col-md-1"><label class="">Host Name:</label></div>  
  <div class="col-12 col-md-5">
    <input type="text" name="hostname"  value="localhost" class="form-control"/> <br>
  </div>
</div> 
<div class="row">
  <div class="col-12 col-md-1"><label class="">Password:</label></div>  
  <div class="col-12 col-md-5">
    <input type="password" name="password"  class="form-control"/> <br>
  </div>
</div>  
<div class="row">
  <div class="col-12 col-md-1"><label class="">Database Name : </label></div>  
  <div class="col-12 col-md-5">
    <input type="text" name="database" class="form-control"/>  <br>
  </div>
</div>
<div class="row">
  <div class="col-12 col-md-1"><label class=""> </label></div>  
  <div class="col-12 col-md-5">
    <input type="submit" name="dbinfo" class="form-control bg-primary text-white" value="Creat/Verify Database"/>
  </div>
</div>

</form>

</div>
</div> 