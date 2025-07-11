<?php


class LtRequestPlaceholder
{
     private static $data = [];

    public static function set(string $key, mixed $value): void
    {
        self::$data[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$data[$key] ?? $default;
    }

    public static function all(): array
    {
        return self::$data;
    }
    
}


function toGetRequestMethod(){
    $toGetRequestMethod = $_SERVER['REQUEST_METHOD'];

    if ($toGetRequestMethod === 'POST') {
        // Check for method override
        if (isset($_POST['_method'])) {
        $toGetRequestMethod = strtoupper($_POST['_method']); // e.g. PUT, DELETE
        }
    }  
    return $toGetRequestMethod;
}

function toCheckRoute($route, $isIndexPage = "") {
    $route_method = toGetRequestMethod();

    $db = DbConnect::dbDriver();
    if ($isIndexPage === "Yes") {
        $stmt = $db->prepare("SELECT * FROM lifepage WHERE page_name = :route LIMIT 1");
    } else {
      $stmt = $db->prepare(" SELECT * FROM lifepage   WHERE pageurl_new = :route 
      AND (route_method IS NULL OR route_method = '' OR route_method = :route_method) LIMIT 1");      
    $stmt->bindParam(':route_method', $route_method);
    }
    $stmt->bindParam(':route', $route);
    $stmt->execute();
    //return $stmt->rowCount() > 0 ? $stmt->fetch(PDO::FETCH_OBJ) : false;
    return $stmt->rowCount() > 0 
    ? (object)['urlRoute' => $stmt->fetch(PDO::FETCH_OBJ), 'urlRouteInApi' => null] 
    : false;
}

function toCheckApiRoute($incomingPath){
     //check whether it is API route
    $route_method = toGetRequestMethod();

    $db = DbConnect::dbDriver(); 
        $stmt = $db->prepare("SELECT *, LENGTH(pageurl_new) - LENGTH(REPLACE (pageurl_new, '/', '')) AS slash_count FROM lifepage WHERE pagetype = 'Api' AND (route_method IS NULL OR route_method = '' OR route_method = :route_method) ORDER BY slash_count DESC");

        $stmt->bindParam(':route_method', $route_method);
        $stmt->execute();

        $apiRoutes = $stmt->fetchAll();
            $segments = explode('/', $incomingPath);  
                foreach ($apiRoutes as $pageRouting) { 
                    $apiPath = trim($pageRouting->pageurl_new, '/');
                    if (str_starts_with( $incomingPath, $apiPath) && str_starts_with($incomingPath, $apiPath.'/')) { 
                            $remainingPath = trim(substr($incomingPath, strlen($apiPath)), '/');
                            return (object)['urlRoute' => $pageRouting, 'urlRouteInApi' => $remainingPath]; }
                }
                return false; 
}

function toCheckDynamicRoute($incomingUrl) {
    $route_method = toGetRequestMethod();
  
    $db = DbConnect::dbDriver();
    $incomingUrl = trim($incomingUrl, '/'); // Normalize
    $incomingSegments = explode('/', $incomingUrl);
    $incomingCount = count($incomingSegments);

    // Fetch dynamic routes with {$...} placeholders
    $stmt = $db->prepare("SELECT * FROM lifepage WHERE pagetype = 'Router' AND pageurl_new LIKE '%{%'  AND (route_method IS NULL OR route_method = '' OR route_method = :route_method)");
    $stmt->bindParam(':route_method', $route_method);
    $stmt->execute();
    $routes = $stmt->fetchAll(PDO::FETCH_OBJ);

    foreach ($routes as $route) { 
        $pattern = trim($route->pageurl_new, '/');
        $patternSegments = explode('/', $pattern);

        // Step 1: Check segment count match
        if (count($patternSegments) !== $incomingCount) {
            continue; // Not same structure
        }        
     
        // Step 2: Extract parameter names
        preg_match_all('/\{(\w+)\}/', $pattern, $paramNames);
        $paramNames = $paramNames[1];

        // Step 3: Replace {$param} with regex capture groups
        $regexPattern = preg_replace_callback('/\{\w+\}/', function ($match) {
            return '([^/]+)';
        }, $pattern);

        // Step 4: Escape other parts (carefully)
        $regexPattern = str_replace('/', '\/', $regexPattern);

        // Finalize regex
        $regexPattern = '/^' . $regexPattern . '$/'; 

        // Step 5: Match the URL
        if (preg_match($regexPattern, $incomingUrl, $matches)) {
            array_shift($matches); // Remove the full match

            $params = [];
            foreach ($paramNames as $i => $paramName) {
                $params[$paramName] = $matches[$i] ?? null;
                $route->$paramName = $matches[$i] ?? null; 
                $toDetermineRequest =  toGetRequestMethod();
                if($toDetermineRequest === "GET"){
                    $_GET[$paramName] = $matches[$i] ?? null;
                }else if($toDetermineRequest === "POST"){
                    $_POST[$paramName] = $matches[$i] ?? null;
                }else{  
                    $paramData = $matches[$i] ?? null;
                    LtRequestPlaceholder::set($paramName, $paramData); 
                }
                
            } 
            return (object)['urlRoute' => $route, 'urlRouteInApi' => null];  
        }
   
}
 // No match
    return false;
}

function urlDispatcher($incomingPath) {

    $db = DbConnect::dbDriver();
    $incomingPath = trim($incomingPath, '/');
    $segments = explode('/', $incomingPath);     

    $verifyToCheckWebRoute = toCheckRoute($incomingPath); // Exact match
        if($verifyToCheckWebRoute){
            return $verifyToCheckWebRoute;
        }

        //check whether it is route
        $verifyToCheckApiRoute =toCheckApiRoute($incomingPath);
        if($verifyToCheckApiRoute){

            $apiRouteValue= $verifyToCheckApiRoute->urlRoute;
            $apiRouteUrlRemaining= $verifyToCheckApiRoute->urlRouteInApi;
            //echo 'checking'. $apiRouteUrlRemaining;
            $verifyapiRouteValueRemaining = toCheckRoute($apiRouteUrlRemaining); // Exact match
            if($verifyapiRouteValueRemaining){
                return (object)['urlRoute' => $apiRouteValue, 'urlRouteInApi' => $verifyapiRouteValueRemaining->urlRoute]; 
            }

            $verifyapiRouteValueRemaining = toCheckDynamicRoute($apiRouteUrlRemaining);
            if($verifyapiRouteValueRemaining){
                return (object)['urlRoute' => $apiRouteValue, 'urlRouteInApi' => $verifyapiRouteValueRemaining->urlRoute]; 
            }

            return false;

        }else{
            
            //check whether webrout is having dynamic value
            $verifyapiRouteValueRemaining = toCheckDynamicRoute($incomingPath);
            if($verifyapiRouteValueRemaining){
                return $verifyapiRouteValueRemaining;
            }
            return false; 
        } 
    return false; // Not found
}

?>