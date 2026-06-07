<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbPackage;
use Lt\Modules\MdLt\Models\TbPageContent;
use Lt\Modules\MdLt\Models\TbContent;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use DbConnect; 

class TbPageContentService
{
        protected $pageContentModel;
        protected $request;
        protected $ltId;
        protected $userId;
        
        public function __construct(){
            $this->pageContentModel = new TbPageContent();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->userId = LtSession::get('ltUid');
        }
        
        // publishing of either page to content or content to page
        public function publishPageContent($model){
                
                if(is_object($model) && ($model->className ?? '') == "tb_page_content"){
                    $this->pageContentModel = $model;
                }elseif (is_iterable($model)) {
                    foreach ($model as $key => $value) {
                        $this->pageContentModel->$key = $value;
                    }
                   
                }else {
                    // Unsupported input
                    return  LtResponse::json("Content Value should be Array or Object", 3502, 100); 
                }
            
            
                 $pageId = $this->pageContentModel->pageId;
                 $contentId = $this->pageContentModel->contentId;
                 $publishStatus = $this->pageContentModel->publishStatus;
                 $contentType = $this->pageContentModel->contentType;
                 $packageName = $this->pageContentModel->packageName;
                 $publishSource = $this->pageContentModel->publishSource;
                 
                //  return json_encode($this->pageContentModel);

            if($publishStatus == '0'){
                    // to unpublish the content or page
                    $this->pageContentModel->remove('contentId', '=', $contentId)->andWhere('pageId', '=', $pageId)->andWhere('contentType', '=', $contentType)->delete();
                    if($this->pageContentModel->responseCategory == '200'){
                        return  LtResponse::json('Unpublish Successfully', 804, 200, ['pageId' => $pageId, 'contentId' => $contentId]);
                    }else{
                        return $this->pageContentModel->responseJson();
                    }
            }elseif($publishStatus == '1'){
                
                $this->pageContentModel->select('MAX(sort_order) AS highestSortNo')->where('pageId', '=', $pageId)->get();
            
                $highestSortNo = $this->pageContentModel->responseData[0]->highestSortNo ?? null; 
                
                if (!empty($highestSortNo)) {
                    $highestSortNo += 8;
                }else{
                    $highestSortNo = 8;
                }
            
                
                $exist = $this->pageContentModel->select()->where('contentId', '=', $contentId)->andWhere('pageId', '=', $pageId)->andWhere('contentType', '=', $contentType)->get();
                if(count($exist) > 0) return LtResponse::json('record exist', 801, 100);
                
                $this->pageContentModel->ltId = ltId();
                $this->pageContentModel->contentId = $contentId;
                $this->pageContentModel->contentType = $contentType;
                $this->pageContentModel->packageName = $packageName;
                $this->pageContentModel->publishSource = $publishSource ?: 'content';
                $this->pageContentModel->pageId = $pageId;
                $this->pageContentModel->sortOrder = $highestSortNo;
                $this->pageContentModel->updatedAt =$this->ltNow;
                $this->pageContentModel->createdAt = $this->ltNow;
                $this->pageContentModel->updatedBy = $this->userId;
                
                $this->pageContentModel->insert();
                 if($this->pageContentModel->responseCategory == '200'){
                    return  LtResponse::json('Publish Successfully', 803, 200, $this->pageContentModel->responseData);
                 }else{
                    return $this->pageContentModel->responseJson();
                 }
            }
                       
            
        }
        
        // for publishing content to page
        public function publishPage($id, $type){
            
            $this->pageContentModel->select('lt_id, page_id, content_id');
                $this->pageContentModel->where('contentId', '=', $id)->andWhere('contentType', '=', $type);

            $this->pageContentModel->get();
            return $this->pageContentModel->responseData;
        }
        
        /// for publishing page to content
        public function publishContent($id){

            $this->pageContentModel->select('lt_id, page_id, content_id, sort_order, publish_source');
                $this->pageContentModel->where('pageId', '=', $id);

            $this->pageContentModel->get();
            return $this->pageContentModel->responseData;
        }
        
        
         public function deletePageContent($ltId = null, $packageName = null){

            if($packageName !== null){
                $sqlConnect = DbConnect::dbDriver();
                
                $sqlPage = "SELECT lt_id FROM tb_page WHERE package_name =:pkgName AND page_type <> 'router'";
        
                $stmtPage = $sqlConnect->prepare($sqlPage);
                $stmtPage->execute([':pkgName' => $packageName]);
                $matchedPages = $stmtPage->fetchAll(\PDO::FETCH_COLUMN, 0);

                if (empty($matchedPages)) return 0;
                
                  // Step 3: Build dynamic placeholders and parameter bindings for deleting page content 
                $placeHolders = [];
                $parameters = [];
        
                foreach ($matchedPages as $i => $pg) {
                    $placeHolders[] = ":id$i";
                    $parameters[":id$i"] = $pg;
                }
        
                $inPageContent = implode(', ', $placeHolders);
                $parameters[':pkg'] = $packageName;
                // Step 4: Delete matching entries from tb_page_content
                $sqlDelete = "DELETE FROM tb_page_content
                              WHERE page_id IN ($inPageContent) OR package_name = :pkg";
        
                 $stmtDel = $sqlConnect->prepare($sqlDelete);
                 $stmtDel->execute($parameters);
                 return $stmtDel->rowCount();
            }else{
                $this->pageContentModel->processRequest(); //to capture ltId for response Json method
                $this->pageContentModel->remove('contentId', '=', $ltId)->orWhere('pageId', '=', $ltId)->delete();
                return $this->pageContentModel->responseJson();
            }
            
            
        }
        
        public function publishPageRegion($dataArr){
         
            if(empty($dataArr))LtResponse::json("No data", 802, 100);;
            $packageModel = new TbPackage();
            $themeRecord = $packageModel->select('package_name')->where('packageType', '=', 'theme')->andWhere('isThemeDefault', '=', 1)->get()[0];  //fetching default theme
            
            if(!$themeRecord) return LtResponse::json('No default theme', 801, 100);
            $contentModel = new TbContent();
            $contentRecord = $contentModel->select('lt_id, sort_order')->where('packageName', '=', $themeRecord->packageName)->andWhere('isThemeDefault', '=', 1)->get();
            
            if(count($contentRecord) < 0) return LtResponse::json("No content found for theme {$themeRecord->packageName}", 3410, 100);
                //   $record = []; 
                    foreach($contentRecord as $regionData){
                        $this->pageContentModel->contentId = $regionData->ltId;
                        $this->pageContentModel->sortOrder = $regionData->sortOrder;
                        $this->pageContentModel->pageId = $dataArr['pageId'];
                        $this->pageContentModel->contentType = $dataArr['contentType'];
                        $this->pageContentModel->packageName = $dataArr['packageName'] ;
                        $this->pageContentModel->createdAt = $this->ltNow;
                        $this->pageContentModel->updatedAt = $this->ltNow;
                        $this->pageContentModel->publishSource = 'region';
                        $this->pageContentModel->updatedBy = LtSession::get('ltUid');
                        $this->pageContentModel->ltId = ltId();
                        
                        // $record[] = $regionData->ltId;
                        $this->pageContentModel->insert();
                        
                    }
                    // return json_encode($record);
                     if($this->pageContentModel->responseCategory == '200'){
                        return  LtResponse::json('Publish Successfully', 3411, 200);
                     }else{
                        return $this->pageContentModel->responseJson();
                     }
        }
        
        
}
