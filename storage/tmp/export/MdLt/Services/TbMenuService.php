<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbMenu;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;

class TbMenuService
{
        protected $menuModel;
        protected $request;
        protected $userId;
        protected $ltNow;
        
        public function __construct(){
            $this->menuModel = new TbMenu();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->userId = LtSession::get('ltUid');

        }
        
        public function store($model){

            if (is_object($model) && ($model->className ?? '') === 'tb_menu') {
            $this->menuModel = $model;
            }
            // If it's an object or array (not tb_menu), copy its values
            elseif (is_iterable($model)) {
                foreach ($model as $key => $value) {
                    $this->menuModel->$key = $value;
                }
               
            }
            else {
                // Unsupported input
                return  LtResponse::json("Menu data is not supported", 1503, 100); 
            }
            
            $menuName = $this->menuModel->menuName ?: "nill";
            // $moduleName = $model->moduleName;
            
            $exist = $this->menuModel->select()->where('menuName', '=', $menuName)->get();
            
            if(count($exist) > 0){
                return  LtResponse::json("Menu [ $menuName ] already exist", 1504, 100); 
            }

            $this->menuModel->ltId = ltId();
            $this->menuModel->updatedBy = $this->userId;
            $this->menuModel->updatedAt = $this->ltNow;
            $this->menuModel->createdAt = $this->ltNow;
            
            $this->menuModel->insert();
            return $this->menuModel->responseJson();
            
            
        }
        
        public function deleteMenu($ltId = null, $params = null){
            if($params !== null){
                $this->menuModel->remove('packageName', '=', $params)->orWhere('contentName', '=', $params)->delete();
                // $this->menuModel->remove('packageName', '=', $params);
                return $this->menuModel->responseJson();
            }else{
                $this->menuModel->processRequest();
                $this->menuModel->delete('ltId', '=', $ltId);
                return $this->menuModel->responseJson();
            }
            
            
        }
        
        public function getMenuId($packageName){
            
          $record = $this->menuModel->select('lt_Id')->where('packageName', '=', $packageName)->andWhere('menuLevel', '=', '1')->get()[0];

          return $record;
           
        }
        
         public function dashboardAnalysis(){
            $menu = $this->menuModel->select()->get();
            
            $result = ['totalMenu' => count($menu)];
            return $result;
        }
}
