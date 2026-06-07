<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbPage;
use Lt\Modules\MdLt\Services\LtSession;

class LtNavigate 
{
    private $url = "";
    private $queryParams = [];

    public static function to($pageName = "", $moduleName = ""){
       // return 
       return new self($pageName, $moduleName);
    }
    
    public function __construct($pageName = "", $moduleName = ""){
         $this->toStart($pageName,$moduleName);
    }
    
    // Set the URL for navigation
    private  function toStart($pageName = "", $moduleName = "") {
        
        $lifepageModel = new TbPage;

        if ($moduleName == "") {
            $lifepageModel->findId('page_name', $pageName);
            //$response_data = $lifepageModel->responseData->pageurlNew ?? null;
            $response_data = $lifepageModel->responseData->routePath ?? null;
        } else {
            $lifepageModel->select('route_path')
                ->where('page_name', $pageName)
                ->andWhere('package_name', $moduleName)
                ->get();
            $response_data = $lifepageModel->responseData[0]->routePath ?? null;
        }
        //print_r($lifepageModel->responseData[0]['pageurlNew']);
       
        $this->url = ($lifepageModel->responseCategory == "200" && $response_data)
            ? ltSiteHostAddress() . $response_data
            : "PageNotFound";
           // echo $this->url;
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
        LtSession::set($key, $value);
        return $this;
    }
    
    public function getUrl()
    {
        return $this->url;
    }

    // Perform redirection
    public function redirect() { 
        header("Location: " .  $this->url);
        exit();  
    }

    
}

// Helper functions
function ltNavigateTo($pageName = "", $moduleName = "") {
     $geturl =  LtNavigate::to($pageName, $moduleName);
    return $geturl->getUrl();
}

function ltNavigateData($key = "") {
    $sessionData = LtSession::get($key);
    LtSession::forget($key);
    return $sessionData;
}

function ltNavigateBack() {
    return $_SERVER['HTTP_REFERER'] ?? '/';
}

/**
 * test this on testpage view
 * 
 */
