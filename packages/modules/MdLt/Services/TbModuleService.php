<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbModule;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtService;

class TbModuleService
{
        protected $moduleModel;
        protected $request;
        protected $ltId;
        protected $userId;
        protected $ltNow;
        
        public function __construct(){
            $this->moduleModel = new TbModule();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->ltId = LtService::generateLtId();
            $this->userId = LtSession::get('lifetech_uid');
        }
        
        public function store($model){

            $moduleName = $model->moduleName;
            
            $exist = $model->select()->where('moduleName', '=', $moduleName)->get();
            
            if(count($exist) > 0){
                return  LtResponse::json('Record already exist', 101, 100); 
            }

            
            $model->ltId = $this->ltId;
            $model->updatedBy = $this->userId;
            $model->updatedAt = $this->ltNow;
            $model->createdAt = $this->ltNow;
            
            $model->insert();
        
            return $model->responseJson();
            
        }
         
} 