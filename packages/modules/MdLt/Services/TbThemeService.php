<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbTheme;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtService;

class TbThemeService{
        protected $themeModel;
        protected $request;
        protected $ltId;
        protected $userId;
        protected $ltNow;
        
        public function __construct(){
            $this->themeModel = new TbTheme();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->ltId = LtService::generateLtId();
            $this->userId = LtSession::get('lifetech_uid');
        }
        
        public function store($model){

            $themeName = $model->themeName;
            $version = $model->version;
            
            $exist = $model->select()->where('themeName', '=', $themeName)->andWhere('version', '=', $version)->get();
            
            if(count($exist) > 0){
                return  LtResponse::json("Theme [ $themeName ] with version [ $version ] already exist", 101, 100); 
            }

            $model->ltId = $this->ltId;
            $model->updatedBy = $this->userId;
            $model->updatedAt = $this->ltNow;
            $model->createdAt = $this->ltNow;
            
            $model->insert();
        
            return $model->responseJson();
            
        }
        
        
        
        
        
    }
    
