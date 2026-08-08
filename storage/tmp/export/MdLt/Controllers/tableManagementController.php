<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Services\tableManagementService;

class tableManagementController
{
    // public $sqlConnect = DbConnect::dbDriver();

    public function index(){
        
        $dataService = new tableManagementService();
        $response = $dataService->indexTable();
        
        return "$response";

    }
    
    
    public function browse(){
        
        $dataService = new tableManagementService();
        $response = $dataService->browseTable();
        return $response;
    }
    
    public function structure(){

        $dataService = new tableManagementService();
        $response = $dataService->tableStructure();
        return $response;
    }
    public function truncate(){

        $dataService = new tableManagementService();
        $response = $dataService->truncateTable();
        return $response;
    }
    
    public function deleteRecord(){
        
        $dataService = new tableManagementService();
        $response = $dataService->deleteTableRecord();
        return $response;
    }
    public function drop(){
        
        $dataService = new tableManagementService();
        $response = $dataService->dropTable();
        return $response;
    }
    
    public function store(){
        
        $dataService = new tableManagementService();
        $response = $dataService->createTable();
        return $response;
    }
    
    public function sqlExecute(){
        $dataService = new tableManagementService();
        $response = $dataService->sqlExecute();
        return $response;
    }
    
    
}
