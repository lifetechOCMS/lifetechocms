<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbTheme;
use Lt\Modules\MdLt\Services\TbThemeService;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use LtDdm;

// ltImport('mdLt', 'LtService.php');
class TbThemeController {
    
    public function index(){
        $themeModel = new TbTheme();
        $themeModel->processRequest();
        $themeModel->select()->get();
        
        return  LtResponse::json('success', 201, 200, $themeModel->responseData); 
    }
    
        
    public function store(){
        
        $themeModel = new TbTheme();  
        $themeModel->processRequest();
        
        $themeModel->validateRequest([
            'themeName' => 'required',
            'version' => 'required',
            'description' => 'required',
        ]);
  
        $dataModelService = new TbThemeService();
        $response = $dataModelService->store($themeModel);
        
        return $response;
        
    }
    
    public function update(){
        $themeModel = new TbTheme();
        
        $themeModel->processRequest(); 
        $themeModel->updatedBy = LtSession::get('lifetech_uid');
        $themeModel->update('ltId', '=', $themeModel->ltId);
        return $themeModel->responseJson(); 
    }
    
       public function togglePublished(){
        $themeModel = new TbTheme();
        // $themeModel->processRequest(['isPublished', 'ltId']);  
        $request = new LtRequest();

        $publishStatus = $request->isPublished;

        $themeModel->isPublished = '0'; 
        
        $themeModel->edit()->set()->update();
        
        $themeModel->updatedBy = LtSession::get('lifetech_uid');
        $themeModel->isPublished = $publishStatus;
        
        $themeModel->update('ltId', '=', $request->ltId); 
        return $themeModel->responseJson(); 
    }

    public function destroy(){
        $themeModel = new TbTheme();
        $themeModel->processRequest(); 
    
        $ltId =  $themeModel->ltId;
        
        $themeModel->delete('ltId', '=', $ltId);
        return $themeModel->responseJson();

        // $themeModel->select()->where('ltId', '=', $ltId)->andWhere('isProtected', '1')->get(); 
        // if($themeModel->responseCategory === '200'){
        //     $moduleName = $themeModel->responseData[0]->moduleName;
        //     return LtResponse::json("Module [ $moduleName ] cannot be deleted because it is Protected", "4801");
        // } else {
            
        // }

    }
    
}
