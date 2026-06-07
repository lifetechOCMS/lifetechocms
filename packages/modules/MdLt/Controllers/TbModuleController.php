<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbModule;
use Lt\Modules\MdLt\Services\TbModuleService;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;


// ltImport('mdLt', 'LtService.php');
class TbModuleController
{
    
    public function index(){
        $moduleModel = new TbModule();
        $moduleModel->select()->get();
        
        return  LtResponse::json('success', 201, 200, $moduleModel->responseData); 
    }
    
        
    public function store(){
        
        $moduleModel = new TbModule();  
        $moduleModel->processRequest();
        
        $moduleModel->validateRequest([
            'moduleName' => 'required'
        ]);
  
        $dataModelService = new TbModuleService();
        $response = $dataModelService->store($moduleModel);
        
        return $response;
        
    }
    
    public function update(){
        $moduleModel = new TbModule();
        
        $moduleModel->processRequest(); 
        $moduleModel->validateRequest([
            'moduleName' => 'required'
        ]);
        $moduleModel->updatedBy = LtSession::get('lifetech_uid');
        $moduleModel->update('ltId', '=', $moduleModel->ltId);
        return $moduleModel->responseJson(); 
    }
    
    public function togglePublished(){
        $moduleModel = new TbModule();
        $moduleModel->processRequest(['isPublished', 'ltId']);  
        $moduleModel->updatedBy = LtSession::get('lifetech_uid');
        $moduleModel->update('ltId', '=', $moduleModel->ltId); 
        return $moduleModel->responseJson(); 
    }
    
    public function destroy(){
        $moduleModel = new TbModule();
        $moduleModel->processRequest(); 
    
        $ltId =  $moduleModel->ltId;

        $moduleModel->select()->where('ltId', '=', $ltId)->andWhere('isProtected', '1')->get(); 
        if($moduleModel->responseCategory === '200'){
            $moduleName = $moduleModel->responseData[0]->moduleName;
            return LtResponse::json("Module [ $moduleName ] cannot be deleted because it is Protected", "4801");
        } else {
            $moduleModel->delete('ltId', '=', $ltId);
            return $moduleModel->responseJson();
        }

    }
}