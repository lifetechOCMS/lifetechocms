<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtResponse;
use DbConnect;  
 

// error_reporting(E_ALL); 
// ini_set('display_errors', 1);


class TbResponseService
{
    protected $responseModel;
    protected $request;
    protected $userId;
    protected $ltNow;
    public $sqlConnect;
    
    public function __construct(){
        $this->responseModel = new TbResponse();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->userId = LtSession::get('ltUid');
        $this->sqlConnect = DbConnect::dbDriver();
    }
    
    public function storeResponse($model){

        $responseResult = $model->responseResult;
        $responseCode = $model->responseCode;
        
        $existing =  $this->responseModel->select()->where('responseCode', '=', $responseCode)->get();
        
        if(count($existing) > 0){
            return LtResponse::json('The response code already exists. Please enter a different code.', '3101', '100');
        }

        $model->ltId = ltId();
        $model->updatedBy = $this->userId;
        $model->updatedAt = $this->ltNow;
        $model->createdAt = $this->ltNow;

        $model->insert();
        
        return $model->responseJson();
        
    }
    
    
    
    
//         public function storeResponse($model){
        
//             // $sql = "SHOW TABLE LIKE 'tb_response'";
//             // $sql = "ALTER TABLE tb_response ADD COLUMN package_name VARCHAR(100), ADD COLUMN content_name VARCHAR(100), ADD COLUMN description TEXT";
// $stmt = $this->sqlConnect->prepare("SHOW COLUMNS FROM tb_response");
// $stmt->execute();

// $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

// return json_encode($columns);

//             // $responseResult = $model->responseResult;
//             // $responseCode = $model->responseCode;

//             // $model->ltId = ltId();
//             // $model->updatedBy = $this->userId;
//             // $model->updatedAt = $this->ltNow;
//             // $model->createdAt = $this->ltNow;

//             // $model->insert();
        
//             // return $model->responseJson();
        
//         }
 
    
}