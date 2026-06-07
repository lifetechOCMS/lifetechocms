<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbPage;
use Lt\Modules\MdLt\Services\LtService;
use Lt\Modules\MdLt\Services\TbPageService;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Models\TbContent;
use DbConnect;


class TbPageController
{

    
    public function index(){
        $pageModel = new TbPage();
        $pageModel->select();
        // $pageModel->processRequest();
        $request = new LtRequest();
        $pageType = $request->page_type ?? null;
        
        $isPublished = LtService::getIsValue($request->is_published ?? null);
        
        // Build conditions dynamically
        $hasCondition = false;
        
        if($isPublished !== null){
            $pageModel->where('isPublished', '=', $isPublished)->andWhere('pageType','<>','router');
            $hasCondition = true;
        }
        if($pageType !== null){
            if($hasCondition){
                $pageModel->andWhere('pageType', '=', $pageType);
            }else{
                $pageModel->where('pageType', '=', $pageType);
                $hasCondition = true;
            }
        }
        
        $pageModel->get();
        return $pageModel->responseJson();
       
    }
    
        
    public function store(){
        
        $pageModel = new TbPage();  
        $pageModel->processRequest();
        
        $pageModel->validateRequest([
            'pageName' => 'required',
            'pageType' => 'required',
        ]);
  
        $dataModelService = new TbPageService();
        $response = $dataModelService->store($pageModel);
        
        return $response;

        
    }
    
    public function update(){
        $pageModel = new TbPage();
        
        $pageModel->processRequest(); 
        $pageModel->updatedBy = LtSession::get('ltUid');
        $pageModel->update('ltId', '=', $pageModel->ltId);
        return $pageModel->responseJson(); 
    }
    
    public function toggleQuicklink(){
        $pageModel = new TbPage();
        $pageModel->processRequest(['isQuicklink', 'ltId']); 
        
        $service = LtService::getIsValue($pageModel->isQuicklink);

        $pageModel->updatedBy = LtSession::get('ltUid');
        $pageModel->isQuicklink = $service;
        
        $pageModel->update('ltId', '=', $pageModel->ltId); 
        return $pageModel->responseJson(); 
    }
    
    public function toggleService(){
        $pageModel = new TbPage();
        $pageModel->processRequest(['isService', 'ltId']); 
        
        $service = LtService::getIsValue($pageModel->isService);

        $pageModel->updatedBy = LtSession::get('ltUid');
        $pageModel->isService = $service;
        
        $pageModel->update('ltId', '=', $pageModel->ltId); 
        return $pageModel->responseJson(); 
    }
    public function togglePublished(){
        $pageModel = new TbPage();
        $pageModel->processRequest(['isPublished', 'ltId']); 
        
        $published = LtService::getIsValue($pageModel->isPublished);

        $pageModel->updatedBy = LtSession::get('ltUid');
        $pageModel->isPublished = $published;
        
        $pageModel->update('ltId', '=', $pageModel->ltId); 
        return $pageModel->responseJson(); 
    }
    
    public function destroy(){
        $pageModel = new TbPage();
        $pageModel->processRequest(); 
    
        $ltId =  $pageModel->ltId;
        
        $dataModelService = new TbPageService();
        $response = $dataModelService->deletePages($ltId);
        
        return $response;

    }
    
    public function publishedContents(){
        $pageModel = new TbPage();
        $pageModel->processRequest(); 
    
        $dataModelService = new TbPageService();
        $response = $dataModelService->publishedContent($pageModel);
        
        return $response;
        
    }
    public function publishedRegion(){
        $pageModel = new TbPage();
        $pageModel->processRequest(); 
    
        $dataModelService = new TbPageService();
        $response = $dataModelService->publishedRegion($pageModel);
        
        return $response;
        
    }
    public function publishedRoute(){
        $pageModel = new TbPage();
        $pageModel->processRequest(); 
    
        $dataModelService = new TbPageService();
        $response = $dataModelService->publishedRoute($pageModel);
        
        return $response;
        
    }
    public function toggleRoute(){
        $pageModel = new TbPage();
        $pageModel->processRequest(); 
    
        $dataModelService = new TbPageService();
        $response = $dataModelService->toggleRoute($pageModel);
        
        return $response;
        
    }
    
    public function toggleRole(){
        $pageModel = new TbPage();
        $pageModel->processRequest(); 
        $ltId = $pageModel->ltId;
        $roleId = $pageModel->roleId;
        $packageName = $pageModel->packageName;
        $pageName = $pageModel->pageName;
        $pageType = $pageModel->pageType;

        $publishValue = LtService::getIsValue($pageModel->publishValue);

        $pageModel->updatedBy = LtSession::get('ltUid');
        $pageModel->$roleId = $publishValue;
        
        $pageModel->update('ltId', '=', $ltId); 
        if($pageType !== 'router'){
            $sqlConnect = DbConnect::dbDriver();
            // $contentModel->$roleId =  $publishValue;
            // $result = $contentModel->edit($roleId, '=', $publishValue)->where('packageName', '=', $packageName)->andWhere('contentName', '=', $pageName)->toSql();
           // toggle role publishing for menu
            $sql = "UPDATE tb_content SET `$roleId` = :roleValue WHERE package_name = :pkgName AND content_name = :contentName";
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute([
                ":roleValue" => $publishValue,
                ":pkgName"     => $packageName,
                ":contentName"     => $pageName
            ]);
            
        }
        
        return $pageModel->responseJson();  
        
    }
    
    
    public function moveContents(){
        $request = new LtRequest;
        $currentId = $request->currentId;
        $currentSortOrderNo = $request->currentSortOrderNo;
        $pageId = $request->pageId;
        $newSortOrderNo = $request->newSortOrderNo;
        $publishSource = $request->publishSource;
        $step =  8;
        $curr_pos = $currentSortOrderNo / $step; 
        $new_pos = $newSortOrderNo / $step;
        $sqlConnect = DbConnect::dbDriver();
        
        
        if ($new_pos < $curr_pos) {  // Moving UP

            $sql = "UPDATE tb_page_content
                    SET sort_order = sort_order + :step
                    WHERE sort_order >= :new_order
                      AND sort_order < :current_order
                      AND page_id = :pageId
                      AND publish_source = :publishSource";
        
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute([
                ":new_order"     => $newSortOrderNo,
                ":current_order" => $currentSortOrderNo,
                ":step"          => $step,
                ":publishSource" => $publishSource,
                ":pageId"   => $pageId
            ]);
        
            $sql2 = "UPDATE tb_page_content SET sort_order = :newOrder WHERE lt_id = :ltId AND page_id = :pageId";
            $stmt2 = $sqlConnect->prepare($sql2);
            $stmt2->execute([
                ":newOrder" => $newSortOrderNo,
                ":ltId"     => $currentId,
                ":pageId"     => $pageId,
            ]);
            
        }
        
        
        if ($new_pos > $curr_pos) { // Moving DOWN
        
            $sql = "UPDATE tb_page_content
                    SET sort_order = sort_order - :step
                    WHERE sort_order <= :new_order
                      AND sort_order > :current_order
                      AND page_id = :pageId
                      AND publish_source = :publishSource";
        
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute([
                ":new_order"     => $newSortOrderNo,
                ":current_order" => $currentSortOrderNo,
                ":step"          => $step,
                ":publishSource" => $publishSource,
                ":pageId"        => $pageId
            ]);
        
            $sql2 = "UPDATE tb_page_content SET sort_order = :newOrder WHERE lt_id = :ltId AND page_id = :pageId";
            $stmt2 = $sqlConnect->prepare($sql2);
            $stmt2->execute([
                ":newOrder" => $newSortOrderNo,
                ":ltId"     => $currentId,
                ":pageId"     => $pageId,
            ]);
        }

        return LtResponse::json('Page content moved successfully','3401', '200');
        
        
    }
    
    public function routePath(){
        $pageModel = new TbPage();
        $sqlConnect = DbConnect::dbDriver();
        $pageModel->processRequest(); 
        $ltId = $pageModel->ltId;
        $routePath = $pageModel->routePath;
        $oldRoutePath = $pageModel->oldRoutePath;
        
        $existing = $pageModel->select()->where('routePath', '=', $routePath)->get();
        
        if(count($existing)) return LtResponse::json("failed: route path [ $routePath ] exist already",'3402', '200');
        // updating page route path
        $pageModel->update('ltId', '=', $ltId);
        
        // updating menu route for all existence
        $sql = "UPDATE tb_menu SET route_path = :routePath WHERE route_path = :oldPath";
        $stmt = $sqlConnect->prepare($sql);
        $stmt->execute([
            ':routePath' => $routePath,
            ':oldPath' => $oldRoutePath,
            ]);
        
        return $pageModel->responseJson();
    }
    
    public function publishToRegion(){

        $dataModelService = new TbPageService();
        $response = $dataModelService->publishedToRegion();
        
        return $response;

    }
    
}