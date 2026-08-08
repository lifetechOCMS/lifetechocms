<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbMenu;
use Lt\Modules\MdLt\Models\TbPage;
use Lt\Modules\MdLt\Models\TbContent;
use Lt\Modules\MdLt\Models\TbPageContent;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtService;
use Lt\Modules\MdLt\Services\TbMenuService;
use Lt\Modules\MdLt\Services\LtSession;
use DbConnect;


// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
class TbMenuController
{

    public function index(){
        $menuModel = new TbMenu();
        $menuModel->processRequest();
        $newFormat = $menuModel->menu;
        $menuModel->select()->get();
        if($newFormat){
            $data = $menuModel->responseData;
            $menus = $this->buildTree($data);
            return LtResponse::json('Data Fetched', 2009, 200, $menus);
        }else{
            return $menuModel->responseJson();
        }
 
        

    }
    
    public function buildTree(array $elements) {
            $branch = [];
            $indexed = [];
            
            // 1. Indexing
            foreach ($elements as $element) {
                $element->children = []; 
                $indexed[$element->ltId] = $element;
            }
            
            // 2. Linking (Note the & reference)
            foreach ($indexed as $id => &$node) {
                $pId = (int) $node->parentMenuId;
                
                if ($pId != 0 && isset($indexed[$pId])) {
                    // Add this node to its parent's children array
                    $indexed[$pId]->children[] = $node;
                } else {
                    // This is a top-level node
                    $branch[] = $node;
                }
            }
    

            return $branch;
    }

    public function isNavigation(){
        $menuModel = new TbMenu();
        $request = new LtRequest();
        $menuModel->select('lt_id, menu_name, slug, route_path, target, is_external, parent_menu_id, menu_level, icon');
        $roles = [];
            $userRole = LtSession::get('ltUrid');
            if(empty($userRole)){
               $userRole =  "c81e728d9d4c2f"; // anonymous role
            };
            $roles = array_map('trim', explode(',', $userRole));


            $first = true;
            foreach ($roles as $role) {
                    if ($first) {
                        $menuModel->where($role, '=', 1);
                        $first = false;
                    } else {
                        $menuModel->orWhere($role, '=', 1);
                    }
            }

        $data = $menuModel->get();

        // 2. Build the Tree
        $result = $this->buildHierarchyMenu($data, "");

        if(!$result) return LtResponse::json('No Record   Found', 2012, 100);
        return LtResponse::json('Data Fetched', 2009, 200, $result);
    }
    public function isQuicklink(){
        $menuModel = new TbMenu();
        $request = new LtRequest();
        $sqlConnect = DbConnect::dbDriver();
        $roles = [];
            $userRole = LtSession::get('ltUrid');
             if(empty($userRole)){
               $userRole =  "c81e728d9d4c2f";
            };
            $roles = array_map('trim', explode(',', $userRole));

            $sql = "SELECT menu_name, target, route_path FROM tb_menu WHERE is_quicklink = 1";
            if (!empty($roles)) {
                $conditions = array_map(function($role) {
                    return "$role = 1";
                }, $roles);

                $sql .= " AND (" . implode(" OR ", $conditions) . ")";
            }
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_OBJ);
              if(!$data) return LtResponse::json('No Record Found', 2012, 100);
            return LtResponse::json('Data Fetched', 2009, 200, $data);
    }
    public function isService(){
        $menuModel = new TbMenu();
        $request = new LtRequest();
        $sqlConnect = DbConnect::dbDriver();
        $roles = [];
            $userRole = LtSession::get('ltUrid');
            if(empty($userRole)){
               $userRole =  "c81e728d9d4c2f";
            };
            $roles = array_map('trim', explode(',', $userRole));

            $sql = "SELECT menu_name, target, route_path FROM tb_menu WHERE is_service = 1";

            if (!empty($roles)) {
                $conditions = array_map(function($role) {
                    return "$role = 1";
                }, $roles);

                $sql .= " AND (" . implode(" OR ", $conditions) . ")";
            }
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_OBJ);

            if(!$data) return LtResponse::json('No Record Found', 2012, 100);
            return LtResponse::json('Data Fetched', 2009, 200, $data);
    }

    public function buildHierarchyMenu(array $items, $parentId = "") {
        $branch = [];

        foreach ($items as $item) {
            $itemParentId = $item->parentMenuId ?? "";

            if ($itemParentId == $parentId) {

                $children = $this->buildHierarchyMenu($items, $item->ltId);

                if (!empty($children)) {
                    $item->children = $children;
                }

                $branch[] = $item;
            }

        }

        usort($branch, function ($a, $b) {
            return $a->menuLevel <=> $b->menuLevel;
        });

        return $branch;
    }

    public function store(){

        $menuModel = new TbMenu();
        $menuModel->processRequest();

        $menuModel->validateRequest([
            'menuName' => 'required',
            'menuLevel' => 'required',
        ]);

        $dataModelService = new TbMenuService();
        $response = $dataModelService->store($menuModel);

        return $response;


    }

    public function update(){
        $menuModel = new TbMenu();

        $menuModel->processRequest();
        $menuModel->updatedBy = LtSession::get('ltUid');
        $menuModel->update('ltId', '=', $menuModel->ltId);
        return $menuModel->responseJson();
    }




    public function toggleQuicklink(){
        $menuModel = new TbMenu();
        $menuModel->processRequest(['isQuicklink', 'ltId']);

        $service = LtService::getIsValue($menuModel->isQuicklink);

        $menuModel->updatedBy = LtSession::get('ltUid');
        $menuModel->isQuicklink = $service;

        $menuModel->update('ltId', '=', $menuModel->ltId);
        return $menuModel->responseJson();
    }

    public function toggleService(){
        $menuModel = new TbMenu();
        $menuModel->processRequest(['isService', 'ltId']);

        $service = LtService::getIsValue($menuModel->isService);

        $menuModel->updatedBy = LtSession::get('ltUid');
        $menuModel->isService = $service;

        $menuModel->update('ltId', '=', $menuModel->ltId);
        return $menuModel->responseJson();
    }

    public function destroy(){
        $menuModel = new TbMenu();
        $menuModel->processRequest();
        $ltId =  $menuModel->ltId;

        $dataModelService = new TbMenuService();
        $response = $dataModelService->deleteMenu($ltId);

        return $response;

    }

    public function parents(){
        $menuModel = new TbMenu();
        $menuModel->processRequest();

        $level = $menuModel->level;
        $parentMenuLevel = ($level > 1) ? ($level - 1) : false;

        if($parentMenuLevel){

             $menuModel->select()->where('menuLevel', '=', $parentMenuLevel)->get();
             return  LtResponse::json('success', 1501, 200, $menuModel->responseData);
        }else{

            return  LtResponse::json('failed', 1502, 100);
        }

    }

    public function toggleRole(){
        $menuModel = new TbMenu();
        $pageModel = new TbPage();
        $contentModel = new TbContent();
        $pageContentModel = new TbPageContent();
        $sqlConnect = DbConnect::dbDriver();
        $menuModel->processRequest();
        $ltId = $menuModel->ltId;
        $roleId = $menuModel->roleId;
        $routePath = $menuModel->routePath;

        $publishValue = LtService::getIsValue($menuModel->publishValue);

        $menuModel->updatedBy = LtSession::get('ltUid');
        $menuModel->$roleId = $publishValue;

        $menuModel->update('ltId', '=', $ltId);

        if($routePath !== '#'){

            $page = $pageModel->select('lt_id, page_name, package_name')->where('routePath', '=', $routePath)->get()[0];

            if(empty($page)) return;

            // toggle role publishing for content
               $sql = "UPDATE tb_content SET `$roleId` = :roleValue WHERE package_name = :pkgName AND content_name = :contentName";
               $stmt = $sqlConnect->prepare($sql);
               $stmt->execute([
                   ":roleValue" => $publishValue,
                   ":pkgName"     => $page->packageName,
                   ":contentName"     => $page->pageName
               ]);

             // toggle role publishing for page
                $sql = "UPDATE tb_page SET `$roleId` = :roleValue WHERE route_path = :routePath";
                $stmt = $sqlConnect->prepare($sql);
                $stmt->execute([
                    ":roleValue" => $publishValue,
                    ":routePath"     => $routePath,
                ]);
        }
        if($publishValue == 1 && $routePath !== '#' ){
            $contents = $contentModel->select('lt_id, content_type')->where('packageName','=', $page->packageName)->andWhere('contentName', '=', $page->pageName)->get()[0];
            if(empty($contents)) return;

            $exist = $pageContentModel->select()->where('contentId', '=', $contents->ltId)->andWhere('pageId', '=', $page->ltId)->get();
                if(empty($exist)){

                    $pageContentModel->ltId = ltId();
                    $pageContentModel->contentId = $contents->ltId;
                    $pageContentModel->contentType = $contents->contentType;
                    $pageContentModel->packageName = $page->packageName;
                    $pageContentModel->publishSource = 'content';
                    $pageContentModel->pageId = $page->ltId;
                    $pageContentModel->sortOrder = 8;
                    $pageContentModel->updatedAt =$this->ltNow;
                    $pageContentModel->createdAt = $this->ltNow;

                    $pageContentModel->insert();

                }

        }


        return $menuModel->responseJson();

    }


}
