<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbRole;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\TbRoleService;
use Lt\Modules\MdLt\Services\LtService;
use Lt\Modules\MdLt\Services\LtSession;;

class TbRoleController
{
    public function index(){
        $roleModel = new TbRole();
        $roleModel->select();
    
        $request = new LtRequest();
        $isEnabled = LtService::getIsValue ($request->is_enabled);
        $isProtected = LtService::getIsValue ($request->is_protected);
       
        if ($isEnabled !== null && $isProtected !== null) {
            $roleModel->where('isEnabled', $isEnabled)
                      ->andWhere('isProtected', $isProtected);
        } elseif ($isEnabled !== null) {
            $roleModel->where('isEnabled', $isEnabled);
        } elseif ($isProtected !== null) {
            $roleModel->where('isProtected', $isProtected);
        }
        
        $roleModel->orderBy('created_at')->get();
        return  LtResponse::json('Role Record Fetched Successfully', 3201, 200, $roleModel->responseData); 
    }
    
    public function select(){
        $roleModel = new TbRole();
        $roleModel->select('lt_id, role_name')->where('isEnabled', 1)->orderBy('created_at')->get();
        return  LtResponse::json('Role Record Fetched Successfully', 3201, 200, $roleModel->responseData); 
    }

    public function store(){
        $roleModel = new TbRole(); 
        $ltId = LtService::generateLtId();
        $roleModel->processRequest(); 
        $roleModel->validateRequest(['roleName' => 'required|min:3']);
        $roleModel->ltId = $ltId;
        $roleName = $roleModel->roleName;
        
        $exist = $roleModel->select()->where('roleName', '=', $roleName)->get();
        if(count($exist) > 0) return  LtResponse::json('Role Record Exist', 3202, 100);
        
        $roleModel->updatedBy = LtSession::get('ltUid');
        
        $roleModel->insert();
        $tables = ['tb_content', 'tb_page', 'tb_menu', 'bked_content_editor'];
        if($roleModel->responseCategory == '200'){
           $response =  LtService::alterTableColumn($tables, $ltId);
        }
        
        return $roleModel->responseJson();
    }
    
    public function update(){
        $roleModel = new TbRole(); 
        
        $roleModel->processRequest(); 
        $roleModel->validateRequest(['roleName' => 'required|min:3']);
        $roleModel->updatedBy = LtSession::get('ltUid');
        $roleModel->update('ltId', '=', $roleModel->ltId);
        return $roleModel->responseJson(); 
    }
    
    public function destroy(){
        $roleModel = new TbRole(); 
        
        $roleModel->processRequest(); 
        $ltId = $roleModel->ltId; 
        
        $roleModel->select()->where('ltId', $ltId)->andWhere('isProtected', '1')->get(); 
        if($roleModel->responseCategory === '200'){
            return LtResponse::json("This Role cannot be deleted because it is Protected", "3203");
        }else{
         
        $roleModel->delete('ltId', '=', $ltId);
            
         $tables = ['tb_content', 'tb_page', 'tb_menu', 'bked_content_editor'];
         $response =  LtService::dropTableColumn($tables, $ltId);
            
              
          return $roleModel->responseJson();
        } 
    }
    
    public function toggleEnabled(){
        $roleModel = new TbRole(); 
        $roleModel->processRequest(['isEnabled', 'ltId']);  
        $roleModel->updatedBy = LtSession::get('ltUid');
        $roleModel->update('ltId', '=', $roleModel->ltId); 
        return $roleModel->responseJson(); 
    }
    
    public function rolePermissions(){
        $roleModel = new TbRole(); 
  
        $dataModelService = new TbRoleService();
        $response = $dataModelService->rolePermission();
   
        return $response;
        
    }
    

    
}
