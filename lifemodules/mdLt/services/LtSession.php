<?php 
 
class LtSession
    {
       /**
         * Starts new or resumes existing session
         * 
         * @access  public
         * @return  bool
         */

        public function start()
        {
            if(session_start()) {
                return true;
            }
            return false;
        }

        /**
         * End existing session, destroy, unset and delete session cookie
         * 
         * @access  public
         * @return  void
         */

        public function end()
        {
            if($this->status != true) {
                $this->start();
            }
            session_destroy();
            session_unset();
            setcookie(session_name(), null, 0, "/");
        }

        /**
         * Set new session item
         * 
         * @access  public
         * @param   mixed
         * @param   mixed
         * @return  mixed
         */

        public static function set($key, $value)
        {           
            return $_SESSION[$key] = $value;
        }

        /**
         * Checks if session key is already set
         * 
         * @access  public
         * @param   mixed  - session key
         * @return  bool 
         */

        public static function has($key)
        {
            if(isset($_SESSION[$key])) {
                return true;
            }
            return false;
        }   

        /**
         * Get session item 
         * @access  public 
         */

        public static function get($key)
        {
            if(!isset($_SESSION[$key])) {
                return false;
            }
            return $_SESSION[$key];         
        }
        //unset some session
         public static function forget($key)
        {
            if(!isset($_SESSION[$key])) {
                return false;
            }
             unset($_SESSION[$key]);         
        }
        //unset some session
         public static function destroy()
        {
            session_destroy();         
        }
        //get all session
         public static function all()
        {
            return $_SESSION;         
        }

    }
/*
Session::set($key, $value);
Session::get($key);
Session::all();
Session::forget($key);
Session::destroy();
Session::has($key);
*/
?>    
      