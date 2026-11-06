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


/**
 *  RESPONSE CODE 3400 - 3499
 */ 



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
        
        public function store($model) {
            // 1. Handle array or non-tb_page object inputs (e.g. from TbContentService, or from other services)
            if (!is_object($model) || ($model->className ?? '') !== 'tb_page') {
                if (!is_iterable($model)) {
                    return LtResponse::json("Page value must be an Array or Object.", 3401, 100);
                }
        
                foreach ($model as $key => $newPage) {
                    $this->pageModel->$key = $newPage;
                }
        
                if (empty($this->pageModel->ltId)) {
                    $this->pageModel->ltId = ltId();
                }
        
                // Auto-assign active default theme layout if pageType is 'theme' and not already set
                if (($this->pageModel->pageType ?? '') === 'theme') {
                    if (empty($this->pageModel->themeLayout) || empty($this->pageModel->themeLayoutPackage)) {
                        $activeDefault = $this->getActiveDefaultThemeLayout();
                        $this->pageModel->themeLayout        = $activeDefault['layoutName'] ?? null;
                        $this->pageModel->themeLayoutPackage = $activeDefault['packageName'] ?? null;
                    }
                } else {
                    $this->pageModel->themeLayout        = null;
                    $this->pageModel->themeLayoutPackage = null;
                }
        
                $this->pageModel->updatedBy = $this->pageModel->updatedBy ?? $this->userId;
                $this->pageModel->updatedAt = $this->pageModel->updatedAt ?? $this->ltNow;
                $this->pageModel->createdAt = $this->pageModel->createdAt ?? $this->ltNow;
        
                $this->pageModel->insert();
                return $this->pageModel->responseJson();
            }
        
            // 2. Direct TbPage model creation (from Page UI)
            $pageName  = trim($model->pageName ?? '');
            $routePath = trim($model->routePath ?? '');
            $pageType  = trim($model->pageType ?? '');
        
            $checkPage = new TbPage();
            $existing  = $checkPage->select()
                                  ->where('pageName', '=', $pageName)
                                  ->andWhere('routePath', '=', $routePath)
                                  ->get();
        
            if (!empty($existing)) {
                return LtResponse::json("Page '{$pageName}' already exists.", 3403, 100);
            }
        
            // 3. Assign Default.html layout ONLY for 'theme' pages (never 'api', 'router', 'report')
            if ($pageType === 'theme') {
                $activeDefault             = $this->getActiveDefaultThemeLayout();
                $model->themeLayout        = $activeDefault['layoutName'] ?? null;
                $model->themeLayoutPackage = $activeDefault['packageName'] ?? null;
            } else {
                $model->themeLayout        = null;
                $model->themeLayoutPackage = null;
            }
            
            
        
            $model->ltId      = ltId();
            $model->updatedBy = $this->userId;
            $model->updatedAt = $this->ltNow;
            $model->createdAt = $this->ltNow;
        
            $model->insert();
            return $model->responseJson();
        }
        
        private function getActiveDefaultThemeLayout(): ?array {
            $sqlConnect = DbConnect::dbDriver();
            $sql = "
                SELECT 
                    p.package_name,
                    c.content_name
                FROM tb_package p
                INNER JOIN tb_content c 
                    ON c.package_name = p.package_name
                  AND c.content_name = 'Default.html'
                  AND c.theme_resource_type = 'Layout'
                WHERE p.package_type = 'theme'
                  AND p.is_theme_default = 1
                LIMIT 1
            ";
        
            $stmt = $sqlConnect->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
            return [
                'packageName' => $row['package_name'],
                'layoutName'  => $row['content_name'],
            ];
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
                if(is_string($params)){
                    $this->pageModel->remove('packageName', '=', $params)->delete();
                }elseif(is_array($params)){
                    $this->pageModel->remove('packageName', '=', $params['packageName'])->andWhere('pageName', '=', $params['contentName'])->delete();
                }
                return $this->pageModel->responseJson();
            }else{
                $this->pageModel->processRequest();
                $pageCheck = new TbPage();
                $pageExist = $pageCheck->select()->where('ltId', '=', $ltId)->get();
        
                if(empty($pageExist)) return LtResponse()::json("Invalid Page Id", "3302");
            
                $isProtected = $pageExist[0]->isProtected;
                
                if($isProtected == '1'){
                     return LtResponse()::json("Page cannot be deleted because it is Protected", "3302");
                }
                
                $this->pageModel->delete('ltId', '=', $ltId);
                return $this->pageModel->responseJson();
            }
 
        }
        
        public function publishedToDefault() {
            $packageModel = new TbPackage();
            $contentModel = new TbContent();
        
            $publishType = trim($this->request->publishType ?? '');
            $selected    = $this->request->selected ?? [];
            $themeId     = trim($this->request->themeId ?? '');
            $updatedBy   = LtSession::get('ltUid');
            $sqlConnect = DbConnect::dbDriver();
        
            // 1. Validate inputs
            if (empty($selected) || !is_array($selected)) {
                return LtResponse::json('No page has been selected for publishing to the specified theme.', 3408, 100);
            }
        
            if (empty($themeId)) {
                return LtResponse::json('No target theme ID was provided.', 3409, 100);
            }
        
            // 2. Fetch and validate theme package record
            $packageRecord = $packageModel->select('lt_id, package_name, package_type')
                                          ->where('ltId', '=', $themeId)
                                          ->get();
        
            if (empty($packageRecord)) {
                return LtResponse::json('The specified theme package was not found.', 3410, 100);
            }
        
            $packageName = $packageRecord[0]->packageName;
            $packageType = $packageRecord[0]->packageType;
        
            if ($packageType !== 'theme') {
                return LtResponse::json("Selected package is not a valid theme.", 3410, 100);
            }
        
            // 3. Verify Default.html exists in both tb_content and on disk
            $defaultLayout = $contentModel->select('lt_id, content_name')
                                          ->where('packageName', '=', $packageName)
                                          ->andWhere('contentName', '=', 'Default.html')
                                          ->andWhere('themeResourceType', '=', 'Layout')
                                          ->get();
        
            if (empty($defaultLayout)) {
                return LtResponse::json("Default.html content record not found. Please create one.", 3315, 100);
            }
        
            $layoutName = $defaultLayout[0]->contentName ?? 'Default.html';
        
            $path = "packages/{$packageType}s/{$packageName}/Layouts/{$layoutName}";
            if (!file_exists($path)) {
                return LtResponse::json("Default.html file not found on disk. Please create one.", 3316, 100);
            }
        
            // 4. Extract target filter values (supports both array and object items in $selected)
            $placeholders = [];
            $inParams     = [];
        
            foreach (array_values($selected) as $i => $item) {
                $itemArr = (array) $item;
                $val     = ($publishType === 'package')
                    ? trim($itemArr['packageName'] ?? '')
                    : trim($itemArr['id']);
        
                if ($val !== '') {
                    $key            = ":item_{$i}";
                    $placeholders[] = $key;
                    $inParams[$key] = $val;
                }
            }
        
            if (empty($placeholders)) {
                return LtResponse::json('No valid page or package identifiers found in selection.', 3408, 100);
            }
        
            $inClause     = implode(', ', $placeholders);
            $targetColumn = ($publishType === 'package') ? 'package_name' : 'lt_id';
        
            // 5. Execute single atomic batch UPDATE on the same $this->sqlConnect instance
            try {
                $sqlConnect->beginTransaction();
        
                $sql = "
                    UPDATE tb_page 
                    SET theme_layout         = :themeLayout,
                        theme_layout_package = :themeLayoutPackage,
                        updated_by           = :updatedBy,
                        updated_at           = :updatedAt
                    WHERE page_type = :pageType
                      AND {$targetColumn} IN ({$inClause})
                ";
        
                $baseParams = [
                    ':themeLayout'        => $layoutName,
                    ':themeLayoutPackage' => $packageName,
                    ':updatedBy'          => $updatedBy,
                    ':updatedAt'          => $this->ltNow,
                    ':pageType'           => 'theme',
                ];
        
                $stmtUpdatePages = $sqlConnect->prepare($sql);
                $stmtUpdatePages->execute(array_merge($baseParams, $inParams));
        
                $sqlConnect->commit();
        
                return LtResponse::json("Page(s) successfully published for the selected theme.", 3411, 200);
        
            } catch (\Throwable $e) {
                if ($sqlConnect->inTransaction()) {
                    $sqlConnect->rollBack();
                }
                return LtResponse::error("Failed: " . $e->getMessage(), 3412, 100, 'Page(s) failed to publish for the selected theme.');
            }
        }
        
        public function togglePublished($packageName= null, $isPublished){
            $publish = false;
            $this->pageModel->updatedBy = LtSession::get('ltUid');
                $this->pageModel->isPublished = $isPublished;
            if($packageName !== null){
                $this->pageModel->update('packageName', '=', $packageName);
                $publish =true;
            }else{
                $modelRequest = $this->pageModel->processRequest(); 
                $ltId = $modelRequest->ltId;
                return $ltId;
                $this->pageModel->update('ltId', '=', $ltId); 
                $publish =true;
            }
            if($publish){
                return $this->pageModel->responseJson(); 
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
    