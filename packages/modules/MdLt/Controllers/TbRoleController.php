<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbRole;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\TbRoleService;
use Lt\Modules\MdLt\Services\LtService;
use Lt\Modules\MdLt\Services\LtSession;

/**
 *  RESPONSE CODE 3200 - 3299
 */ 


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
        $roleService = new TbRoleService();
        $result = $roleService->store();
        return $result;
       
    }
    
    
    public function getFormRoles(){
        $roleService = new TbRoleService();
        $result = $roleService->getFormRoles();
        return $result;
       
    }
    
    public function updateFormRoles(){
        $roleService = new TbRoleService();
        $result = $roleService->updateFormRoles();
        return $result;
       
    }
    
    public function update(){
        $roleService = new TbRoleService();
        $result = $roleService->update();
        return $result;
       
    }
    
    public function destroy(){
        $roleService = new TbRoleService();
        $result = $roleService->destroy();
        return $result;
       
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
