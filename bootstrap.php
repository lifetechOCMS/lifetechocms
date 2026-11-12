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
    
    //setting default time zone
    date_default_timezone_set('Africa/Lagos');
    
    //define log folder path 
    define('LT_LOG_PATH',  __DIR__ . '/storage/logs');

    //define production or development environment
    define('LT_ENV', 'development'); //production or development

    //ApiTokenDetails 
    Class ApiTokenDetails
    {
        public static function apiTokenExpiredTimeOut(){
           return LT_ENV === 'development'
                ? 7200   // Development: 2 hours
                : 1800;   // Production: 30 minutes
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
    
    //preparing the current version of the framework
    $GLOBALS['appcoreVersion'] = '2.8.1';
    if (!function_exists('getAppcoreVersion')) {
        function getAppcoreVersion(): string
        {
            $version = $GLOBALS['appcoreVersion'] ?? '2.8.0';
            return $version;
        }
    }

    
    //cleaning and redirecting endpoint url
    require_once  "includes/endpoint_integration.php";
?>