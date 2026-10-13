<?php 
 
 
    session_set_cookie_params([
    'lifetime' => 0, // session-only cookie
    'path' => '/', 
    'secure' => true,        // only send over HTTPS
    'httponly' => true,      // JavaScript can't access it
    'samesite' => 'Lax'   // optional: CSRF protection Lax or Strict
    ]);
 
    //just to autoload classes psr-4
    require_once __DIR__ . '/vendor/autoload.php';
    
     //generate Browser content script policy value for xss or csp attack
    define('CSP_NONCE', base64_encode(random_bytes(16)));
    
    //ApiTokenDetails 
    Class ApiTokenDetails
    {
        public static function apiTokenExpiredTimeOut(){
            $apiTokenExpiredTimeOut = "1800"; //30 minutes
            return $apiTokenExpiredTimeOut; 
        }
        public static function backEndBaseUrl(){ 
                $backEndBaseUrl = include("includes/endpoint.php");
            return $backEndBaseUrl;
        }
        
        //set your app id value
        public static function appId(){
            global $appIdentifier; 
            return $appIdentifier;
        }
        
         //set your token varriable name
        public static function tokenName(){
          $theName = "lwToken_".self::appId();
            return $theName;
        }
        
        //set to include webView Uaage
        public static function disableWebView(){ 
            return false;
        }
    } 
    
   
    if (!function_exists('getAppcoreVersion')) {
        function getAppcoreVersion(){
        $version = "2.7.6";
        return $version;
        }
    }

    //setting default time zone
    date_default_timezone_set('Africa/Lagos');
    
    //define log folder path 
    define('LT_LOG_PATH',  __DIR__ . '/storage/logs');

    //define production or development environment
    define('LT_ENV', 'development'); //production or development
    
    //cleaning and redirecting endpoint url
    require_once  "includes/endpoint_integration.php";
?>