<?php 
 
    session_set_cookie_params([
    'lifetime' => 0, // session-only cookie
    'path' => '/',
    'domain' => 'localhost', // or your custom domain in production
    'secure' => true,        // only send over HTTPS
    'httponly' => true,      // JavaScript can't access it
    'samesite' => 'Strict'   // optional: CSRF protection
    ]);

?>