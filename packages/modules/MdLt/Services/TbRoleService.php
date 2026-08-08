<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbRole;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use DbConnect;


// ltImport('mdLt', 'TbRole.php');
// ltImport('mdLt', 'LtService.php');

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
         
        public function getRoleNameById($role)
        {
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
         

}    