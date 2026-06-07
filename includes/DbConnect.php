<?php
use Lt\Modules\MdLt\Services\LtResponse;

class DbConnect
{
    private $dbengine   = 'mysql';
    private $dbhost;
    private $dbuser;
    private $dbpassword;
    private $dbname;
    public $dbh = null;
    public static $dbDriver_nn = null;

    public function __construct()
    {
        /*
        $this->dbhost     = getenv('LT_DB_HOST') ?: 'localhost';
        $this->dbuser     = getenv('LT_DB_USER') ?: 'root';
        $this->dbpassword = getenv('LT_DB_PASS') ?: '';
        $this->dbname     = getenv('LT_DB_NAME') ?: 'test_line';
        */

        require_once 'DatabaseConfig.php';

        $config = DatabaseConfig::get();

        $this->dbhost     = $config['host'];
        $this->dbuser     = $config['user'];
        $this->dbpassword = $config['pass'];
        $this->dbname     = $config['name'];

        try {
            $dsn = $this->dbengine . ":host={$this->dbhost};dbname={$this->dbname};charset=utf8mb4";

            $connect2db_iii = new PDO($dsn, $this->dbuser, $this->dbpassword, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $this->dbh = $connect2db_iii;
            self::$dbDriver_nn = $connect2db_iii;

        } catch (PDOException $e) {
            self::$dbDriver_nn = null;
            $errorCode = (string) $e->getCode();

            if (defined('LT_ENV') && LT_ENV === 'development') {
                if ($errorCode === '1049') {
                    die("Database not found. Please check your DB name.");
                } elseif ($errorCode === '1045') {
                    die("Invalid database username or password.");
                } elseif ($errorCode === '2002') {
                    die("Unable to connect to the database server.");
                } else {
                    die($errorCode . ' - ' . $e->getMessage());
                }
            } 
            
            //error_log('DB ERROR [' . $errorCode . ']: ' . $e->getMessage());
            $logError = 'DB ERROR [' . $errorCode . ']: ' . $e->getMessage();
            LtResponse::error($logError);
            die("Database connection error. Please try again later.");
        }
    }

    public static function dbDriver()
    { 
        if (self::$dbDriver_nn instanceof PDO) {
            return self::$dbDriver_nn;
        }

        new self();
        return self::$dbDriver_nn;   

    }
}
 
?>