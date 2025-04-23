<?php 
       

     

      class DbConnect {
          private $dbengine   = 'mysql';
          private $dbhost     = "localhost";
          private $dbuser     = "root";
          private $dbpassword = "";
          private $dbname     = "lifetechocms";
          public  $dbh = null;
          public  static $dbDriver_nn = null;

          public function __construct() {
              try {
                  // since you are extending PDO, you have to call its constructor
                  $connect2db_iii = new PDO($this->dbengine.":host=$this->dbhost;dbname=$this->dbname;  ", $this->dbuser, $this->dbpassword);
                  $connect2db_iii->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
                  $connect2db_iii->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                  $this->dbh=$connect2db_iii;
                  self::$dbDriver_nn = $connect2db_iii;
              }
              catch (PDOException $e) {
                  self::$dbDriver_nn = $e->getMessage();
                    $errorCode = $e->getCode();
                    if ($errorCode === 1049) { // Unknown database
                        echo "Database not found. Please check your DB name.";
                    } elseif ($errorCode == '1045') { // Access denied
                          echo "Invalid database username or password.";
                    } elseif ($errorCode == '2002') { // Can't connect to MySQL server
                              echo "Unable to connect to the database server.";
                    } else {                              
                          echo $errorCode.'-d '.$e->getMessage();
                    }
              }
          }
           static function dbDriver(){
            new DbConnect();
            $akdfadskjsfdk = self::$dbDriver_nn;
            return $akdfadskjsfdk;
          }
      } 
     
       
      ?>

