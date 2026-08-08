<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbContent;
use Lt\Modules\MdLt\Models\TbPage;
use Lt\Modules\MdLt\Models\TbMenu;
use Lt\Modules\MdLt\Models\TbPageContent;
use Lt\Modules\MdLt\Services\LtService;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\TbContentService;
use DbConnect;
//  to be leave


class TbContentController
{
    
    public function show(){
        $contentModel = new TbContent();
        $pageModel = new TbPage();
        $contentModel->processRequest();
        $contentType = $contentModel->contentType;
        $packageName = $contentModel->packageName;
        $contentArr = [];
        
        // return $moduleName;
        $contentModel->select();
        
        if (!empty($contentType)) {
            if($contentType == 'text'){
                $contentModel->where('contentType', '=', 'text')->orWhere('contentType', '=', 'html');
            }else{
                $contentModel->where('contentType', '=', $contentType);
            }
        }
        
        if (!empty($packageName)) {
            if (!empty($contentType)) {
                $contentModel->andWhere('packageName', '=', $packageName);
            } else {
                $contentModel->where('packageName', '=', $packageName);
            }
        }
        
        
        
        $contentModel->orderBy('sort_order')->get();
        
            
        foreach($contentModel->responseData as $data){
            if($data->mvcType == 'View'){
                if($data->packageName !== null){
                    $pageContent = $pageModel->select('route_path')->where('packageName','=', $data->packageName)->andWhere('pageName', '=', $data->contentName)->get()[0];
                }else{
                    $pageContent = $pageModel->select('route_path')->where('pageName', '=', $data->contentName)->get()[0];
                }
                $contentArr[] = array_merge((array)$data, ['routePath' => $pageContent->routePath]);
            }else{
                $contentArr[] = (array)$data;
            }
        }
        $contentModel->responseData = $contentArr;
        // return json_encode($contentArr);
        return $contentModel->responseJson();

    }
    
    public function store(){
        
        $contentModel = new TbContent();  
        $contentModel->processRequest();
        
        $contentModel->validateRequest([
            'contentName' => 'required',
            'description' => 'required',
        ]);
  
        $dataModelService = new TbContentService();
        $response = $dataModelService->store($contentModel, false);
        
        return $response;
        
    }

    public function autoCreate(){
        $dataModelService = new TbContentService();
        $response = $dataModelService->autoCreate();
        
        return $response;
    }
    
    public function update(){
        $contentModel = new TbContent();
        
        $contentModel->processRequest(); 
        $contentModel->updatedBy = LtSession::get('ltUid');
        // $contentModel->sort_order = 32;
        $contentModel->update('ltId', '=', $contentModel->ltId);
        return $contentModel->responseJson(); 
    }
    
    public function destroy(){
        
        $contentModel = new TbContent();
        $contentModel->processRequest(); 
        $ltId =  $contentModel->ltId;
              
        $dataModelService = new TbContentService();
        $response = $dataModelService->deleteContent($ltId);
        
        return $response;

    }
    
    public function publishedPages(){
        $contentModel = new TbContent();
        $contentModel->processRequest(); 
    
        $dataModelService = new TbContentService();
        $response = $dataModelService->publishedPage($contentModel);
        
        return $response;
        
    }
    
    public function published(){
        $contentModel = new TbContent();
        $contentModel->processRequest(); 
    
        $dataModelService = new TbContentService();
        $response = $dataModelService->publish($contentModel);
        
        return $response;
    }
    
    public function synchronizeContent(){
        $contentModel = new TbContent();
        
        $dataModelService = new TbContentService();
        $response = $dataModelService->synchronizeContent();
        
        return $response;
    }

    public function toggleRole(){
        $sqlConnect = DbConnect::dbDriver();
        $contentModel = new TbContent();
        $menuModel = new TbMenu();
        $pageModel = new TbPage();
        $pageContentModel = new TbPageContent();
        $contentModel->processRequest(); 
        $ltId = $contentModel->ltId;
        $roleId = $contentModel->roleId;
        
        $contents = $contentModel->select('package_name, content_name, content_type, mvc_type')->where('ltId','=', $ltId)->get()[0];
        $publishValue = LtService::getIsValue($contentModel->publishValue);
        $contentType = $contents->contentType;
        $packageName = $contents->packageName;

       if($contents->mvcType !== 'View' && $contentType !== 'theme'){
            return LtResponse::json('Role can only be publish for type View','3512', '100');
        };
        
        // toggle role publishing for content
        $contentModel->updatedBy = LtSession::get('ltUid');
        $contentModel->$roleId = $publishValue;
        $contentModel->update('ltId', '=', $ltId);
        
        // early return if contentType is theme
        if($contentType == 'theme' || $contentType == 'text' || $contentType == 'html'){
             return $contentModel->responseJson();
        }
        
         // toggle role publishing for menu
             if ($packageName !== null) {
                // package_name is not null or empty
                
                $sql = "UPDATE tb_menu 
                        SET $roleId = :roleValue 
                        WHERE package_name = :pkgName AND content_name = :contentName";
                $stmt = $sqlConnect->prepare($sql);
                $stmt->execute([
                    ":roleValue"   => $publishValue,
                    ":pkgName"     => $packageName,
                    ":contentName" => $contents->contentName,
                ]);
                
                 // toggle role publishing for page
                $sql = "UPDATE tb_page SET `$roleId` = :roleValue WHERE package_name = :pkgName AND page_name = :contentName";
                $stmt = $sqlConnect->prepare($sql);
                $stmt->execute([
                    ":roleValue" => $publishValue,
                    ":pkgName"     => $packageName,
                    ":contentName"     => $contents->contentName,
                ]);
                
                $pageContent = $pageModel->select('lt_id')->where('packageName','=', $packageName)->andWhere('pageName', '=', $contents->contentName)->get()[0];
                if(!$pageContent) return;
                
            } else {
                
                // package_name is null or empty, only check content_name
                $sql = "UPDATE tb_menu 
                        SET $roleId = :roleValue 
                        WHERE content_name = :contentName AND package_name IS NULL";
                $stmt = $sqlConnect->prepare($sql);
                $stmt->execute([
                    ":roleValue"   => $publishValue,
                    ":contentName" => $contents->contentName,
                ]);
                
                 // toggle role publishing for page
                $sql = "UPDATE tb_page SET `$roleId` = :roleValue WHERE page_name = :contentName";
                $stmt = $sqlConnect->prepare($sql);
                $stmt->execute([
                    ":roleValue" => $publishValue,
                    ":contentName"     => $contents->contentName,
                ]);
                
                $pageContent = $pageModel->select('lt_id')->where('pageName', '=', $contents->contentName)->get()[0];
                if(!$pageContent) return;
            }
                
            
            if($publishValue == 1){
                
                $exist = $pageContentModel->select()->where('contentId', '=', $ltId)->andWhere('pageId', '=', $pageContent->ltId)->get();
                    if(empty($exist)){
                         
                        $pageContentModel->ltId = ltId();
                        $pageContentModel->contentId = $ltId;
                        $pageContentModel->contentType = $contentType;
                        $pageContentModel->packageName = $packageName;
                        $pageContentModel->publishSource = 'content';
                        $pageContentModel->pageId = $pageContent->ltId;
                        $pageContentModel->sortOrder = 8;
                        $pageContentModel->updatedAt =$this->ltNow;
                        $pageContentModel->createdAt = $this->ltNow;
                        
                        $pageContentModel->insert();
                         
                    } 
         
            }
            
            return $contentModel->responseJson();  

        
    }

    public function moveContents(){
        $contentModel = new TbContent();
        $request = new LtRequest;
        $currentId = $request->currentId;
        $currentSortOrderNo = $request->currentSortOrderNo;
        $packageName = $request->packageName;
        $newSortOrderNo = $request->newSortOrderNo;
        $step =  8;
        $curr_pos = $currentSortOrderNo / $step; 
        $new_pos = $newSortOrderNo / $step;
        $sqlConnect = DbConnect::dbDriver();
        
        
        if ($new_pos < $curr_pos) {  // Moving UP

            $sql = "UPDATE tb_content
                    SET sort_order = sort_order + :step
                    WHERE sort_order >= :new_order
                      AND sort_order < :current_order
                      AND package_name = :packageName";
        
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute([
                ":new_order"     => $newSortOrderNo,
                ":current_order" => $currentSortOrderNo,
                ":step"          => $step,
                ":packageName"   => $packageName
            ]);

            $sql2 = "UPDATE tb_content SET sort_order = :newOrder WHERE lt_id = :ltId";
            $stmt2 = $sqlConnect->prepare($sql2);
            $stmt2->execute([
                ":newOrder" => $newSortOrderNo,
                ":ltId"     => $currentId
            ]);
        }
        
        
        if ($new_pos > $curr_pos) { // Moving DOWN
        
            $sql = "UPDATE tb_content
                    SET sort_order = sort_order - :step
                    WHERE sort_order <= :new_order
                      AND sort_order > :current_order
                      AND package_name = :packageName";
        
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute([
                ":new_order"     => $newSortOrderNo,
                ":current_order" => $currentSortOrderNo,
                ":step"          => $step,
                ":packageName"   => $packageName
            ]);
        
            $sql2 = "UPDATE tb_content SET sort_order = :newOrder WHERE lt_id = :ltId";
            $stmt2 = $sqlConnect->prepare($sql2);
            $stmt2->execute([
                ":newOrder" => $newSortOrderNo,
                ":ltId"     => $currentId
            ]);
        }

        
        return LtResponse::json('content moved successfully','3501', '200');
        
        
    }
    
    
    public function downloadContent(){
        
        $contentModel = new TbContent();
        $contentModel->processRequest(); 
    
        $dataModelService = new TbContentService();
        $response = $dataModelService->download($contentModel);

        return $response;
        
    }
    public function uploadContent(){
        
        $contentModel = new TbContent();
        $contentModel->processRequest(); 
    
        $dataModelService = new TbContentService();
        $response = $dataModelService->upload($contentModel);
        
        return $response;
        
    }
}