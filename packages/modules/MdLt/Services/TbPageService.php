<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbPage;
use Lt\Modules\MdLt\Models\TbPackage;
use Lt\Modules\MdLt\Models\TbContent;
use Lt\Modules\MdLt\Models\TbApiRoute;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\TbContentService;
use Lt\Modules\MdLt\Services\TbPageContentService;
use DbConnect;  


class TbPageService
{
        protected $pageModel;
        protected $request;
        protected $ltId;
        protected $userId;
        
        public function __construct(){
            $this->pageModel = new TbPage();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->userId = LtSession::get('ltUid');
        }
        
        public function store($model){
            
            if($model->className !== 'tb_page'){
                foreach ($model as $key => $newPage){
                    $this->pageModel->$key = $newPage;
                }
                if($this->pageModel->ltId == ''){
                    
                    $this->pageModel->ltId = ltId();
                }
                $this->pageModel->insert();
                return $this->pageModel->responseJson();
            }

            $pageName = $model->pageName;
            $routePath = $model->routePath;
            
            $exist = $model->select()->where('pageName', '=', $pageName)->andWhere('routePath', '=', $routePath)->get();
            
            if(count($exist) > 0){
                return  LtResponse::json("Page [ $pageName ] already exist", 3403, 100); 
            }

            $model->ltId = ltId();
            $model->u9atedBy = $this->userId;
            $model->u9atedAt = $this->ltNow;
            $model->createdAt = $this->ltNow;
            
            $model->insert();
            return $model->responseJson();
            
        }
        
        public function publishedContent($model){
            $ltId = $model->ltId;
            $contentArr = [];
            // $resultArr = [];
            $publishContentArr = [];
            
            $contentService = new TbContentService();
            $result = $contentService->fetchPublishContent();
               
            $pageContentService = new TbPageContentService();
            $publishedContent = $pageContentService->publishContent($ltId);


            foreach ($publishedContent as $item) {
                $publishContentArr[$item->contentId] = ['isPublished'=> 1, 'sortOrder' => $item->sortOrder, 'id' => $item->ltId, 'publishSource' => $item->publishSource];
            }
                // checking
            foreach ($result as $item) {
                $ltId = $item->ltId;
                $item->isPublished = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['isPublished'] : "0";
                $item->sortOrder = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['sortOrder'] : "0";
                $item->ltId = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['id'] : "0"; // page content ltId
                $item->publishSource = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['publishSource'] : ""; // page content ltId
                $item->contentId = $ltId; // content ltId
                $contentArr[] = $item;
            }

            
            usort($contentArr, function ($a, $b) {
                // First sort by isPublished descending
                $publishedCompare = $b->isPublished <=> $a->isPublished;
                if ($publishedCompare !== 0) {
                    return $publishedCompare;
                }
            
                // Then sort by sortOrder descending
                return $a->sortOrder <=> $b->sortOrder;
            });
            
            $contentArr = array_values(array_filter($contentArr, function($value){
                return $value->publishSource != 'region';
            }));

            $publishContentArr = [];
            $result = [];
            $publishedContent = [];
            
            if($contentArr){
                return LtResponse::json('Content records retrieved successfully for page publishing.', 3404, 200, $contentArr);
            }else{
                return LtResponse::json('No records retrieved for page publishing.', 3405, 100);
            }
            
        }
        
        //  to fetch default theme region for publish and upublish
        public function publishedRegion($model){
            $ltId = $model->ltId;
            $contentArr = [];
            $publishContentArr = [];
            
            $packageModel = new Tbpackage();
            $isDefaultTheme = $packageModel->select('package_name')->where('isThemeDefault', '=', 1)->get()[0];
            
            $contentService = new TbContent();
            $result = $contentService->select('lt_id, content_name, content_type, package_name, sort_order, description, is_theme_default')->where('packageName', '=', $isDefaultTheme->packageName)->orderBy('sort_order')->get();

            $pageContentService = new TbPageContentService();
            $publishedContent = $pageContentService->publishContent($ltId);

            
             foreach ($publishedContent as $item) {
                $publishContentArr[$item->contentId] = ['isPublished'=> 1, 'sortOrder' => $item->sortOrder, 'id' => $item->ltId, 'publishSource' => $item->publishSource];
            }
                // checking
            foreach ($result as $item) {
                $ltId = $item->ltId;
                $item->isPublished = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['isPublished'] : "0";
                $item->sortOrder = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['sortOrder'] : "0";
                $item->ltId = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['id'] : "0"; // page content ltId
                $item->publishSource = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['publishSource'] : ""; // page content ltId
                $item->contentId = $ltId; // content ltId
                $contentArr[] = $item;
            }

             usort($contentArr, function ($a, $b) {
                // First sort by isPublished descending
                $publishedCompare = $b->isPublished <=> $a->isPublished;
                if ($publishedCompare !== 0) {
                    return $publishedCompare;
                }
            
                // Then sort by sortOrder descending
                return $a->sortOrder <=> $b->sortOrder;
            });
            
            
            if($contentArr){
                return LtResponse::json('Region records retrieved successfully for page publishing.', 3406, 200, $contentArr);
            }else{
                return LtResponse::json('No records retrieved for page publishing to region', 3407, 100);
            }
            
            // return json_encode($publishedContent);
        }
        
        /**
         *      FETCHING ROUTE PUBLISHED PAGE
         */
        public function publishedRoute($model){
            $ltId = $model->ltId;
            $contentArr = [];
            $publishRouteArr = [];

            /**
             * Select page with page type router
             */
            
            $pageRouteContent = $this->pageModel->select('lt_id, page_name, route_path, route_method')->where('pageType', '=', 'router')->andWhere('isPUblished', '=', 1)->get(); 

            /**
             *  FETCHING PUBLISHED RECORD FROM tb_api_route
             */
            
            $apiRouteModel = new TbApiRoute();
            $publishedRoute = $apiRouteModel->select('lt_id, api_id, route_id')->where('apiId', '=', $ltId)->get();

            
             foreach ($publishedRoute as $item) {
                $publishRouteArr[$item->routeId] = ['isPublished'=> 1];
            }
            //     // checking
            foreach ($pageRouteContent as $item) {
                $ltId = $item->ltId;
                $item->isPublished = isset($publishRouteArr[$ltId]) ? $publishRouteArr[$ltId]['isPublished'] : "0";
                $item->routeId = $ltId; // route ltId
                $contentArr[] = $item;
            }

             usort($contentArr, function ($a, $b) {
                // First sort by isPublished descending
                $publishedCompare = $b->isPublished <=> $a->isPublished;
                if ($publishedCompare !== 0) {
                    return $publishedCompare;
                }
            
                // Then sort by sortOrder descending
                return $a->sortOrder <=> $b->sortOrder;
            });
            
            
            if($contentArr){
                return LtResponse::json('route records retrieved successfully for api publishing.', 3406, 200, $contentArr);
            }else{
                return LtResponse::json('No records retrieved for route publishing to api', 3407, 100);
            }
            
            // return json_encode($publishRouteArr);
        }
        
        public function toggleRoute($model){
            $pageId = $model->pageId;
            $routeId = $model->routeId;
            $publishStatus = $model->publishStatus;
            $apiRouteModel = new TbApiRoute();
            
            if($publishStatus == '0'){
                    // to unpublish the content or page
                    $apiRouteModel->remove('routeId', '=', $routeId)->andWhere('apiId', '=', $pageId)->delete();
                    if($apiRouteModel->responseCategory == '200'){
                        return  LtResponse::json('Unpublish Successfully', 804, 200, ['pageId' => $pageId, 'routeId' => $routeId]);
                    }else{
                        return $this->pageContentModel->responseJson();
                    }
            }else{
                
                $exist = $apiRouteModel->select()->where('routeId', '=', $routeId)->andWhere('apiId', '=', $pageId)->get();
                if(count($exist) > 0) return LtResponse::json('record exist', 101, 100);
                $apiRouteModel->ltId = ltId();
                $apiRouteModel->routeId = $routeId;
                $apiRouteModel->apiId = $pageId;
                $apiRouteModel->updatedAt =$this->ltNow;
                $apiRouteModel->createdAt = $this->ltNow;
                $apiRouteModel->updatedBy = $this->userId;
                
                $apiRouteModel->insert();
                 if($apiRouteModel->responseCategory == '200'){
                    return  LtResponse::json('Publish Successfully', 803, 200, ['pageId' => $pageId, 'routeId' => $routeId]);
                 }else{
                    return $apiRouteModel->responseJson();
                 }
                
            }
            
        }
        
        
        public function fetchPublishPages (){
            
            $publishPage = $this->pageModel->select('lt_id, page_name, package_name, description, is_published, page_type')->where('isPublished', '=', '1')->andWhere('pageType', '!=', 'router')->get();
            return $publishPage;
            
        }
        
        public function deletePages($ltId = null, $params = null){

            if($params !== null){
                $this->pageModel->remove('packageName', '=', $params)->orWhere('pageName', '=', $params)->delete();
                return $this->pageModel->responseJson();
            }else{
                $this->pageModel->processRequest();
                $this->pageModel->delete('ltId', '=', $ltId);
                return $this->pageModel->responseJson();
            }
 
        }
        
        public function publishedToRegion(){
             
            $packageModel = new TbPackage();
            $contentModel = new TbContent();
            $sqlConnect = DbConnect::dbDriver();
        
            $publishSource = $this->request->publishSource;
            $publishType   = $this->request->publishType;
            $selected      = $this->request->selected;
            $themeId       = $this->request->themeId;
        
            if (empty($selected)) {
                return LtResponse::json('No page has been selected for publishing to the specified region.', 3408, 100);
            }
        
            // Fetch package name
            $packageRecord = $packageModel->select('package_name')
                                          ->where('ltId', '=', $themeId)
                                          ->get();
        
            if (empty($packageRecord)) {
                return LtResponse::json('Invalid package theme ID', 3409, 100);
            }
        
            $packageName = $packageRecord[0]->packageName;
        
            try {
                $sqlConnect->beginTransaction();
        
                // Fetch theme default contents
                $sql1 = "SELECT lt_id, sort_order 
                         FROM tb_content 
                         WHERE package_name = :pkg AND is_theme_default=:themeDefault";
        
                $stmt1 = $sqlConnect->prepare($sql1);
                $stmt1->execute([
                    ':pkg'            => $packageName,
                    ':themeDefault' => '1'
                ]);
        
                $packageContent = $stmt1->fetchAll(\PDO::FETCH_ASSOC);
        
                if (empty($packageContent)) {
                    $sqlConnect->rollBack();
                    return LtResponse::json('Theme content not found for publishing', 3410, 100);
                }
        
                /**
                 * Build page list
                 */
                $pageList = [];
        
                if ($publishType === 'package') {
                    $placeholders = [];
                    $params = [];
        
                    foreach ($selected as $i => $pkg) {
                        $placeholders[] = ":pkg$i";
                        $params[":pkg$i"] = $pkg['packageName'];
                    }
        
                    $inPkg = implode(', ', $placeholders);
        
                    $sqlPage = "SELECT lt_id AS page_id, page_type, package_name
                                FROM tb_page
                                WHERE page_type = 'theme'
                                AND package_name IN ($inPkg)";
        
                    $stmtPage = $sqlConnect->prepare($sqlPage);
                    $stmtPage->execute($params);
                    $pageList = $stmtPage->fetchAll(\PDO::FETCH_ASSOC);
                } else {
                    foreach ($selected as $pg) {
                        $pageList[] = [
                            'page_id'      => $pg['id'],
                            'package_name' => $pg['packageName'],
                            'page_type'    => $pg['pageType']
                        ];
                    }
                }
        
                if (empty($pageList)) {
                    $sqlConnect->rollBack();
                   return LtResponse::json('No page has been selected for publishing to the specified region.', 3408, 100);
                }
        
                /**
                 * Delete old region content
                 */
                $pl = [];
                $params = [];
        
                foreach ($pageList as $i => $pg) {
                    $pl[] = ":p$i";
                    $params[":p$i"] = $pg['page_id'];
                }
        
                $inPages = implode(', ', $pl);
        
                $sqlDelete = "DELETE FROM tb_page_content
                              WHERE publish_source = 'region'
                              AND page_id IN ($inPages)";
        
                $stmtDel = $sqlConnect->prepare($sqlDelete);
                $stmtDel->execute($params);
        
                /**
                 * Insert new page content
                 */
                $now   = $this->ltNow;
                $ltUid = LtSession::get('ltUid');
        
                $insertData = [];
        
                foreach ($pageList as $pg) {
                    foreach ($packageContent as $cnt) {
                        $insertData[] = [
                            $pg['page_id'],            // page_id
                            $cnt['lt_id'],             // content_id
                            $pg['page_type'],          // content_type
                            $pg['package_name'],       // package_name
                            ltId(),                    // lt_id
                            $now,                      // created_at
                            $now,                      // updated_at
                            $ltUid,                    // updated_by
                            $publishSource,            // publish_source
                            $cnt['sort_order']         // sort_order
                        ];
                    }
                }
        
                $batchSize = 100;
                $cols = "(page_id, content_id, content_type, package_name, lt_id, created_at, updated_at, updated_by, publish_source, sort_order)";
        
                for ($i = 0; $i < count($insertData); $i += $batchSize) {
                    $batch = array_slice($insertData, $i, $batchSize);
        
                    $placeholders = implode(", ", array_fill(0, count($batch), "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"));
        
                    $sqlInsert = "INSERT INTO tb_page_content $cols VALUES $placeholders
                                  ON DUPLICATE KEY UPDATE
                                    updated_at = VALUES(updated_at),
                                    updated_by = VALUES(updated_by),
                                    content_type = VALUES(content_type),
                                    publish_source = VALUES(publish_source),
                                    sort_order = VALUES(sort_order)";
        
                    $stmtIns = $sqlConnect->prepare($sqlInsert);
        
                    $flatValues = [];
                    foreach ($batch as $row) {
                        foreach ($row as $val) {
                            $flatValues[] = $val;
                        }
                    }
        
                    $stmtIns->execute($flatValues);
                }
                
                // return json_encode([$sqlInsert, $flatValues]);
        
                $sqlConnect->commit();
                return LtResponse::json("Page(s) successfully published for the selected theme.", 3411, 200);
        
            } catch (PDOException $e) {
                $sqlConnect->rollBack();
                return LtResponse::error("failed: " . $e->getMessage(), 3412, 100, 'Page(s) failed to published for the selected theme.');
            }
        }

    
        public function dashboardAnalysis(){
            $pages = $this->pageModel->select()->get();
            
            $theme = $router = $report = $api = [];
            foreach($pages as $page){
                if($page->pageType === 'theme'){
                    $theme[] = $page;
                }
                if($page->pageType === 'router'){
                    $router[] = $page;
                }
                if($page->pageType === 'report'){
                    $report[] = $page;
                }
                if($page->pageType === 'api'){
                    $api[] = $page;
                }
            }
            $pages = ['Theme' => count($theme), 'Routes' => count($router), 'Report' => count($report), 'Api' => count($api), 'totalPages' => count($pages)];
            return $pages;
        }



}
    