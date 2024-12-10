<?php 
 

$host = "localhost";
$username =  "root";
$password = "";
$dbname = "lifetechocms";


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
 
?>