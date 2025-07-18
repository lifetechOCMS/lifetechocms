<?php 
 
    session_set_cookie_params([
    'lifetime' => 0, // session-only cookie
    'path' => '/',
    'domain' => 'localhost', // or your custom domain in production
    'secure' => true,        // only send over HTTPS
    'httponly' => true,      // JavaScript can't access it
    'samesite' => 'Strict'   // optional: CSRF protection
    ]);

    //ApiTokenDetails 
    Class ApiTokenDetails
    {
        public static function apiTokenExpiredTimeOut(){
            $apiTokenExpiredTimeOut = "1800"; //30 minutes
            return $apiTokenExpiredTimeOut;
        }
    } 

    //setting default time zone
    date_default_timezone_set('Africa/Lagos');
?>