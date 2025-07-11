<?php

class LtRoute
{
    private $method;
    private $key;
    private $value;
    private $middleware;
    private $handled = false; // Handle flag

   
   public static function __callStatic($method, $args)
    {
        $httpMethod = strtoupper($method);
        $key = $args[0] ?? null;
        $value = $args[1] ?? null;
        return new self($httpMethod, $key, $value);
    }
    // Constructor to initialize the route
    private function __construct($method, $key, $value)
    {
        $this->method = $method;
        //$this->key = '/' . ltrim($key, '/'); 
        $this->key = $key;
        $this->value = $value;
        // echo LtResponse::json($this->value);
        // Only handle once
        if (!$this->handled) {
          
            $this->handle();
            //$this->handled = true;
        }
    }
    
    //this is used to get the url path to match with the routere
    public function trimBasePath(): string {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base = ltSiteHostAddress(); 
       /* if ($base === '/') { 
            $url = '/' . ltrim($url, '/'); 
            return $url;
        }
        */
        if (str_ends_with($url, $this->key)) {   
            return $this->key;
            
        }
        
        if (str_contains($this->key, '{')) { //echo $url;
            $incomingUrl = $url;
            $routePattern = $this->key;
            // Step 1: Convert route pattern to regex without preg_quote messing up
            $regex = preg_replace('/\{(\w+)\}/', '([^/]+)', $routePattern);
            
            // Step 2: Escape slashes and add end anchor
            $regex = str_replace('/', '\/', $regex);
            $regex = '/'. $regex . '$/'; // Match only end of URL
            
            // Step 3: Try matching
            if (preg_match($regex, $incomingUrl, $matches)) {
                array_shift($matches); // Remove full match
                return $this->key;
            }  
        }
        if (str_starts_with($url, $base)) {  
            return substr($url, strlen($base)) ?: '/';
        }
        
    
        return $url;
    }
    // Middleware method for chaining middleware checks
    public function middleware($callback = null)
    {
        // Check if handle hasn't been triggered already
        if (!$this->handled) {
            //$this->handle();
            return false;
            $this->handled = true;
        }

        // Middleware logic
        if ($callback !== null) {
            if (is_callable($callback)) {
                $proceed = $callback();
                if ($proceed === false) return false;
            } elseif (is_string($callback) && strpos($callback, '@') !== false) {
                list($class, $method) = explode('@', $callback, 2);
                if (class_exists($class) && method_exists($class, $method)) {
                    $instance = new $class();
                    $proceed = call_user_func([$instance, $method]);
                    if ($proceed === false) return false;
                }
            }
        }

        // If everything passes, re-handle the route (e.g., call controller)
        //return self::invokeAction($this->value);
    }
    
     // Resolve request method (with support for method spoofing)
    private function resolveRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' && isset($_POST['_method'])) {
            $spoofed = strtoupper($_POST['_method']);
            if (in_array($spoofed, ['PUT', 'DELETE', 'PATCH'])) {
                $method = $spoofed;
            }
        }
        return $method;
    }

    // Extract request data based on HTTP method and content type
    private function getRequestData()
    {
        $method = $this->resolveRequestMethod();
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (stripos($contentType, 'application/json') !== false) {
                $raw = file_get_contents('php://input');
                return json_decode($raw, true);
            }

            if ($method === 'POST') {
                return $_POST;
            }

            parse_str(file_get_contents("php://input"), $parsed);
            return $parsed;
        }

        return $_GET;
    }
    
     // Handle matching logic for routes
    private function handle()
    {
        //$request = $this->method === 'POST' ? $_POST : $_GET;
        
        $actualMethod = $this->resolveRequestMethod();
        if ($actualMethod !== $this->method) {
            return false;
        }

        $request = $this->getRequestData();

        if (strpos($this->key, '/') !== false) {  
            $urlPath = $this->trimBasePath();  
            if ($urlPath === $this->key) { 
                return self::invokeAction($this->value);
            }
            return false;
        }

        if (strpos($this->key, '@') !== false) {
            list($param, $expected) = explode('@', $this->key, 2);
        } else {
            $urlPath = $this->trimBasePath(); 
            if ($urlPath === $this->key) {
                return self::invokeAction($this->value);
            }
        }

        if ($this->value === null) {
            if (isset($request[$param]) && $request[$param] === $expected) {
                return self::invokeAction($this->value);
            }
            return false;
        }

        if (isset($request[$param]) && $request[$param] === $expected) {
            return self::invokeAction($this->value);
        }
        return false;
    }

    // Invoke action (call controller or method)
    public static function invokeAction($value)
    {
       
        if (is_callable($value)) {
            return $value();
        }
       
                
        if (is_string($value) && substr_count($value, '@') >= 1) {
            $parts = explode('@', $value);

            if (count($parts) === 2) {
                list($controller, $method) = $parts;
                ltImport($controller);
            } elseif (count($parts) === 3) {
                list($module, $controller, $method) = $parts;
                $ct = pathinfo($controller, PATHINFO_EXTENSION) ? $controller : $controller . '.php';
                ltImport($module, $ct);
            } else {
                return false;
            }

            if (class_exists($controller) && method_exists($controller, $method)) {
                $instance = new $controller();
                ob_end_clean(); //cleare any output buffer
                    echo call_user_func([$instance, $method]);
                exit();  //exist any code aftermath
            }
        }

        return false;
    }
    
    public static function register( $moduleName = '' , $contentName='') {
    $db = DbConnect::dbDriver();  
     $code = ltImportReturn($moduleName,$contentName); 
    // 2. Match all LtRoute::method('/path', 'module@Controller@method');
    //preg_match_all('/LtRoute::(get|post|patch|put|delete)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)/i', $code, $matches, PREG_SET_ORDER);
    preg_match_all(
    '/LtRoute::(get|post|patch|put|delete)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(function\s*\([^)]*\)\s*\{.*?\}|[\'"][^\'"]+[\'"])\s*\)\s*(?:->name\(\s*[\'"]([^\'"]+)[\'"]\s*\))?/is',
    $code,    $matches,    PREG_SET_ORDER    );
    
     /* $deleteSql = "DELETE FROM lifepage WHERE module_name = :module_name AND route_file_name = :route_file_name"; 
        $stmt = $db->prepare($deleteSql);
        $stmt->execute([
            'module_name' => $moduleName,
            'route_file_name' => $contentName,
        ]);*/
        
        $updateSql =" UPDATE lifepage SET route_update_status = :route_update_status  WHERE module_name = :module_name AND route_file_name =:route_file_name";
                $stmt = $db->prepare($updateSql);
                $stmt->execute( [
                    'route_update_status' => '0',      
                    'module_name' => $moduleName,               
                    'route_file_name' => $contentName            
                ]);
            
        
    foreach ($matches as $match) {
        list(, $method, $routePath, $target, $routeName) = $match;

        // Sanitize path
        $pageurl_new = trim($routePath, '/'); 

        if (str_starts_with($target, 'function')) {
            $controller =  trim($target, '\'"');
            $page_name = $routeName ?? 'AnonymousRoute';
            $descr = 'Closure handler';
        } else { 
            $controller = trim($target, '\'"');
            $page_name = explode('@', $controller);
            $page_name = $routeName ?? end($page_name); // e.g. listStockData 
        }
    
        

        // Check if route already exists
        $stmt = $db->prepare("SELECT * FROM lifepage WHERE pageurl_new = ? and module_name=? and route_method=?");
        $stmt->execute([$pageurl_new,$moduleName,$method]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        // Set default values
        $data = [
            'page_name'     => ucfirst($page_name),
            'pageurl_new'   => $pageurl_new,
            'pagetype'      => 'Router',
            'status'        => 'Published',
            'module_name'   => $moduleName,
            'updated_by'    => 'route_sync_script',
            'route_handler'    => $controller,
            'route_method'    => $method,
            'route_file_name'    => $contentName,
            'route_update_status'    => '1', 
        ];

      
                
        if ($existing) { 
           $data['pageid'] = $existing['pageid']; 
            $updateSql = "UPDATE lifepage SET page_name = :page_name, route_method = :route_method, route_update_status = :route_update_status, route_file_name = :route_file_name, route_handler = :route_handler, pagetype = :pagetype, status = :status, module_name = :module_name, updated_by = :updated_by , pageurl_new = :pageurl_new WHERE pageid = :pageid";
            $stmt = $db->prepare($updateSql);
            $stmt->execute($data);
            //echo "<br>Updated route: $method.' '.$pageurl_new\n";
        } else {
            // Insert new
            $insertSql = "INSERT INTO lifepage (page_name, route_method, route_update_status,route_file_name, route_handler, pageurl_new, pagetype, status, module_name, updated_by) VALUES (:page_name, :route_method, :route_update_status,:route_file_name, :route_handler,  :pageurl_new, :pagetype, :status, :module_name, :updated_by)";
            $stmt = $db->prepare($insertSql);
            $stmt->execute($data);
           // echo "Inserted new route: $pageurl_new\n";
        }
       
    }
     $deleteSql = "DELETE FROM lifepage WHERE module_name = :module_name AND route_file_name = :route_file_name AND route_update_status = :route_update_status "; 
        $stmt = $db->prepare($deleteSql);
        $stmt->execute([
            'module_name' => $moduleName,
            'route_file_name' => $contentName,
            'route_update_status' => '0',
        ]);
     echo LtResponse::json("Succesful", "212","200");
   }
}

?>      