<?php 
if (!class_exists('Lifepage')) {
    class Lifepage extends LtModel { }
}

class LtNavigate {
    private $url = "";
    private $queryParams = [];

    public static function to($pageName = "", $moduleName = ""){
       // return 
       return new self($pageName, $moduleName);
    }
    
    private function __construct($pageName = "", $moduleName = ""){
         $this->toStart($pageName,$moduleName);
    }
    
    // Set the URL for navigation
    private  function toStart($pageName = "", $moduleName = "") {
        
        $lifepageModel = new Lifepage;

        if ($moduleName == "") {
            $lifepageModel->findId('page_name', $pageName);
            //$response_data = $lifepageModel->responseData->pageurlNew ?? null;
            $response_data = $lifepageModel->responseData->pageurlNew ?? null;
        } else {
            $lifepageModel->select('pageurl_new')
                ->where('page_name', $pageName)
                ->andWhere('module_name', $moduleName)
                ->get();
            $response_data = $lifepageModel->responseData[0]->pageurlNew ?? null;
        }
        //print_r($lifepageModel->responseData[0]['pageurlNew']);
       
        $this->url = ($lifepageModel->responseCategory == "200" && $response_data)
            ? lifetech_site_host_address() . '/' . $response_data
            : "PageNotFound";
            echo $this->url;
            return $this->url;
    }
    
    //public function __toString() {
    //   // return $this->url;
    //}
   
    // Store query parameters
    public function withQuery(array $queryParams) {
        $this->queryParams = array_merge($this->queryParams, $queryParams);
         $queryString = http_build_query($this->queryParams);
         $this->url = $this->url . ($queryString ? "?$queryString" : ""); 
        echo  ($queryString ? "?$queryString" : ""); 
       return $this;
    }

    // Store data in session
    public function withData($key, $value) {
        Session::set($key, $value);
        return $this;
    }

    // Perform redirection
    public function redirect() { 
        header("Location: " .  $this->url);
        exit();  
    }

    
}

// Helper functions
function ltNavigateTo($pageName = "", $moduleName = "") {
    return  LtNavigate::to($pageName, $moduleName);
}

function ltNavigateData($key = "") {
    return Session::get($key);
}

function ltNavigateBack() {
    return $_SERVER['HTTP_REFERER'] ?? '/';
}

/**
 * test this on testpage view
 * 
 */


?>  
      