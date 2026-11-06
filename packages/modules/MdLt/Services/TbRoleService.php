<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbRole;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtService;

use Lt\Modules\MdLt\Models\TbFormRole;
use DbConnect;

/**
 *  RESPONSE CODE 3200 - 3299
 */ 


class TbRoleService
{
        protected $roleModel;
        protected $request;
        protected $ltNow;
        protected $userId;
        
        public function __construct(){
            $this->roleModel = new TbRole();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->userId = LtSession::get('ltUid');
        }
        
        
        public function store() {
            $this->roleModel->validateRequest(['roleName' => 'required|min:3']);
        
            $roleName = trim($this->request->roleName ?? '');
            $remoteId = trim(strtolower($this->request->remoteId ?? ''));
        
            // 1. Determine final ltId (remote override vs auto-generated)
            if (!empty($remoteId)) {
                if (!preg_match('/^[a-z0-9]{12}$/', $remoteId)) {
                    return LtResponse::json('Remote ID must be 12 characters and contain only lowercase letters (a-z) and numbers (0-9).', 3001, 100);
                }
        
                // Check ID uniqueness
                $roleCheck = new TbRole();
                $idExists = $roleCheck->select()->where('ltId', '=', $remoteId)->get();
                if (!empty($idExists)) {
                    return LtResponse::json('A role with this ID already exists.', 3002, 100);
                }
        
                $finalLtId = $remoteId;
            } else {
                $finalLtId = LtService::generateLtId();
            }
        
            // 2. Check roleName uniqueness using a fresh query/model instance
            $nameCheck = new TbRole();
            $nameExists = $nameCheck->select()->where('roleName', '=', $roleName)->get();
            if (!empty($nameExists)) {
                return LtResponse::json("A Role Record with this name [ $roleName ] already exists", 3202, 100);
            }
        
            // 3. Assign properties to model
            $this->roleModel->ltId        = $finalLtId;
            $this->roleModel->roleName    = $roleName;
            $this->roleModel->updatedAt    = $this->ltNow;
            $this->roleModel->createdAt    = $this->ltNow;
            $this->roleModel->isProtected = $this->request->isProtected ?? 0;
            $this->roleModel->updatedBy   = LtSession::get('ltUid');
        
            // 4. Save record
            $this->roleModel->insert();
        
            // 5. Alter table columns using the true saved ID ($finalLtId)
            if ($this->roleModel->responseCategory == '200') {
                $tables = ['tb_content', 'tb_page', 'tb_menu', 'bked_content_editor'];
                LtService::alterTableColumn($tables, $finalLtId);
            }
        
            return $this->roleModel->responseJson();
        }
        
         /**
         *   GET FORM ROLES
         */
        
        public function getFormRoles(){

            $formRoleModel = new TbFormRole();
            $query = $formRoleModel->select('lt_id, form_name, form_role')->get();
            if(empty($query)) return LtResponse::json("No Form Roles found", 3205, 100); 
            return LtResponse::json("Form Roles found", 3205, 200, $query);
        } 
         
         /**
         *   GET FORM ROLES
         */
         
         /**
         *   UPDATE FORM ROLES
         */
        
        public function updateFormRoles(){
            $newRoleId = $this->request->newRoleId ?? null;
            $formRoleltId = $this->request->formRoleltId ?? null;
            $formRoleModel = new TbFormRole();
            
            $formRoleModel->formRole = $newRoleId;
            $formRoleModel->update('ltId', '=', $formRoleltId);
            return $formRoleModel->responseJson();
        } 
         
         /**
         *   UPDATE FORM ROLES
         */
         
         /**
         *   UPDATE ROLES
         */
        
            public function update(){
                $roleModel = new TbRole(); 
                
                $roleModel->processRequest(); 
                $roleModel->validateRequest(['roleName' => 'required|min:3']);
                $roleModel->updatedBy = LtSession::get('ltUid');
                $roleModel->update('ltId', '=', $roleModel->ltId);
                return $roleModel->responseJson(); 
            } 
         
         /**
         *   UPDATE ROLES
         */
         
         /**
         *   DESTROY ROLES
         */
        
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
         
         /**
         *   DESTROY ROLES
         */

         /**
         *   ROLE PERMISSION
         */
         
         public function rolePermission(){
             $publishType = $this->request->publishType ?? null;
             $roleId = $this->request->roleId ?? null;
             $selected= $this->request->selected;

            $sqlConnect = DbConnect::dbDriver();
            
                try {
                    $sqlConnect->beginTransaction();
                
                /// UPDATE ALL CONTENT WHEN PUBLISH TYPE IS "GRANT"
                
                if($publishType == 'grant' || $publishType == 'revoke'){
                    $type = $publishType == 'grant' ? 1 : 0;
                    // update all content where content type is theme and mvc type is view
                    $sql = "UPDATE tb_content SET `$roleId` = :roleValue WHERE content_type=:contentType AND mvc_type=:mvcType";
                    $stmt = $sqlConnect->prepare($sql);
                    $stmt->execute([
                        ':roleValue' => $type,
                        ':contentType' => 'module',
                        ':mvcType' => 'view'
                        ]);
                        
                    // update all pages
                    $sql = "UPDATE tb_page SET `$roleId` = :roleValue";
                    $stmt = $sqlConnect->prepare($sql);
                    $stmt->execute([
                        ':roleValue' => $type,
                        ]);
                    
                    // update all menu
                    $sql = "UPDATE tb_menu SET `$roleId` = :roleValue";
                    $stmt = $sqlConnect->prepare($sql);
                    $stmt->execute([
                        ':roleValue' => $type,
                        ]);

                }else if($publishType == 'router'){
                    $routerId = [];
                    foreach($selected as $route){
                        $routerId[] = $route['id'];
                    }
                      // update all page router
                    $inClause = implode(',', $routerId);
                    $sql = "UPDATE tb_page SET `$roleId` = :roleValue WHERE lt_id IN ($inClause)";
                    $stmt = $sqlConnect->prepare($sql);
                    $stmt->execute([
                        ':roleValue' => 1,
                        ]);                 
                }else if($publishType == 'package'){
                    $pkgNameArr = [];
                    foreach($selected as $pkg){
                        
                        $pkgNameArr[] = "'". addslashes($pkg['packageName']) ."'";
                    }
                    
                    $inClause = implode(',', $pkgNameArr);
                    //updating page table
                    $sql = "UPDATE tb_page SET `$roleId` = :roleValue WHERE package_name IN ($inClause)";
                    $stmt = $sqlConnect->prepare($sql);
                    $stmt->execute([
                        ':roleValue' => 1,
                        ]);  
                    // // //updating menu table
                    $sql = "UPDATE tb_menu SET `$roleId` = :roleValue WHERE package_name IN ($inClause)";
                    $stmt = $sqlConnect->prepare($sql);
                    $stmt->execute([
                        ':roleValue' => 1,
                        ]); 
                    /// updating content
                    $sql = "UPDATE tb_content SET `$roleId` = :roleValue WHERE mvc_type='view' AND package_name IN ($inClause)";
                    $stmt = $sqlConnect->prepare($sql);
                    $stmt->execute([
                        ':roleValue' => 1,
                    ]);  
                    
                }else if($publishType == 'content'){
                      $contentNameArr = [];
                      $pkgNameArr = [];
                      
                      foreach($selected as $content){
                        $contentNameArr[] = $content['contentName'];
                        $pkgNameArr[] = $content['packageName'];
                      }

                     $contentPlaceholders = [];
                     $pkgPlaceholders = [];
                     $params  = [':roleValue' => 1];
                    foreach($contentNameArr as $i => $name){
                        $key = ":content_$i";
                        $contentPlaceholders[] = $key;
                        $params[$key] = $name;
                    }
                    foreach($pkgNameArr as $i => $name){
                        $key = ":pkg_$i";
                        $pkgPlaceholders[] = $key;
                        $params[$key] = $name;
                    }
                    
                    $tables = ['tb_page' => 'page_name', 'tb_menu' => 'content_name', 'tb_content'=> 'content_name'];
                    foreach($tables as $table => $column){
                        $sql = "UPDATE $table SET `$roleId` = :roleValue WHERE $column IN (". implode(',', $contentPlaceholders) .") AND package_name IN (". implode(',', $pkgPlaceholders) .")";
                        $stmt = $sqlConnect->prepare($sql);
                        $stmt->execute($params);
                    }
                    
                }
            
                $sqlConnect->commit();
                return LtResponse::json("Role successfully published for the selected", 3204, 200);
        
            } catch (PDOException $e) {
                $sqlConnect->rollBack();
                return LtResponse::error("failed: " . $e->getMessage(), 3205, 100, 'Role failed to published for the selected.');
            } 

        }
         
        /**
         *     ROLE PERMISSION
         */
         
        public function roleUserPermissionStatus($roles=[]){
            //$roles = "c81e728d9d4c2f, accbc87e4b5ce2, ri3wqzb4sc1v, 03c2q7lkutvo";
            $rolesArr = array_map('trim', explode(',', $roles));
            $newRole = [];
            $sqlConnect = DbConnect::dbDriver();
            if(empty($rolesArr)) return;
            $placeholders = implode(',', array_fill(0, count($rolesArr), '?'));
            $sql = "SELECT lt_id FROM tb_role WHERE lt_id IN ($placeholders)  AND is_enabled = 1";
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute($rolesArr);
            $result = $stmt->fetchAll(\PDO::FETCH_OBJ);
            
            foreach($result as $item){
                $newRole[] = $item->lt_id;
            }
            
            if(empty($newRole)){
                return LtResponse::json('The role has been deactivated.', 3206, 100);
            }else{
                return LtResponse::json('At least one role is enabled.', 3207, 200, $newRole);
            }
           
        }
        
        
        public function dashboardAnalysis(){
            $roles = $this->roleModel->select()->get();
            
            $roles = ['totalRoles' => count($roles)];
            return $roles;
        }
        
        /**
         *     ROLE EXIST
         */
         
         public function roleExist(String $role){
            $developerRoleId = $role;
            $roleExist = false;
            $exist = $this->roleModel->select()->where('ltId', '=', $developerRoleId)->get();
            if(count($exist) > 0){
                $roleExist = true;
            }
            
            return $roleExist;
         }
         
         
        /**
         *     ROLE EXIST
         */
         
          /**
         *     get role name by id
         */
         
        public function getRoleNameById($role){
            try {
        
                $sqlConnect = DbConnect::dbDriver();
        
                if (is_array($role)) {
                    $rolesArr = $role;
                } else {
                    $rolesArr = explode(',', $role);
                }
        
                $rolesArr = array_filter(array_map('trim', $rolesArr));
        
                if (empty($rolesArr)) {
                    return null;
                }
        
                $placeholders = implode(',', array_fill(0, count($rolesArr), '?'));
        
                $sql = "SELECT GROUP_CONCAT(role_name SEPARATOR ', ') AS role_names
                        FROM tb_role
                        WHERE lt_id IN ($placeholders)
                        AND is_enabled = 1";
        
                $stmt = $sqlConnect->prepare($sql);
                $stmt->execute($rolesArr);
        
                return $stmt->fetchColumn();
        
            } catch (\PDOException $e) {
        
                $logError = 'ROLE ERROR [' . $e->getCode() . ']: ' . $e->getMessage();
                return LtResponse::error($logError);
            }
        }
        
            
        public function fetchRole(){
            $roleModel = new TbRole();
            $result = $roleModel->select('lt_id')->where('isEnabled', 1)->orderBy('created_at')->get();
            if(empty($result)) return "Failed to fetch role";
            return $result;
        }
         

}    