<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbPage;
use Lt\Modules\MdLt\Models\TbPackage;

///// comment
// class TbPageSerice{
    //     protected $pageModel;
    //     protected $request;
    //     protected $ltId;
    //     protected $userId;
        
    //     public function __construct(){
    //         $this->pageModel = new TbPage();
    //         $this->request = new LtRequest;
    //         $this->ltNow = date('Y-m-d H:i:s');
    //         $this->userId = LtSession::get('ltUid');
    //     }
        
    //     public function store($model){
            
    //         if($model->className !== 'tb_page'){
    //             foreach ($model as $key => $newPage){
    //                 $this->pageModel->$key = $newPage;
    //             }
    //             $this->pageModel->ltId = ltId();
    //             $this->pageModel->insert();
    //             return $this->pageModel->responseJson();
    //         }

    //         $pageName = $model->pageName;
    //         $routePath = $model->routePath;
            
    //         $exist = $model->select()->where('pageName', '=', $pageName)->andWhere('routePath', '=', $routePath)->get();
            
    //         if(count($exist) > 0){
    //             return  LtResponse::json("Page [ $pageName ] already exist", 101, 100); 
    //         }

    //         $model->ltId = ltId();
    //         $model->u9atedBy = $this->userId;
    //         $model->u9atedAt = $this->ltNow;
    //         $model->createdAt = $this->ltNow;
            
    //         $model->insert();
    //         return $model->responseJson();
            
    //     }
        
    //     public function publishedContent($model){
    //         $ltId = $model->ltId;
    //         $contentArr = [];
    //         // $resultArr = [];
    //         $publishContentArr = [];
            
    //         $contentService = new TbContentService();
    //         $result = $contentService->fetchPublishContent();
               
    //         $pageContentService = new TbPageContentService();
    //         $publishedContent = $pageContentService->publishContent($ltId);
          
            
    //         foreach ($publishedContent as $item) {
    //             $publishContentArr[$item->contentId] = ['isPublished'=> 1, 'sortOrder' => $item->sortOrder, 'id' => $item->ltId, 'publishSource' => $item->publishSource];
    //         }
    //             // checking
    //         foreach ($result as $item) {
    //             $ltId = $item->ltId;
    //             $item->isPublished = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['isPublished'] : "0";
    //             $item->sortOrder = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['sortOrder'] : "0";
    //             $item->ltId = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['id'] : "0"; // page content ltId
    //             $item->publishSource = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId]['publishSource'] : ""; // page content ltId
    //             $item->contentId = $ltId; // content ltId
    //             $contentArr[] = $item;
    //         }

            
    //         usort($contentArr, function ($a, $b) {
    //             // First sort by isPublished descending
    //             $publishedCompare = $b->isPublished <=> $a->isPublished;
    //             if ($publishedCompare !== 0) {
    //                 return $publishedCompare;
    //             }
            
    //             // Then sort by sortOrder descending
    //             return $a->sortOrder <=> $b->sortOrder;
    //         });
            
    //         $contentArr = array_values(array_filter($contentArr, function($value){
    //             return $value->publishSource != 'region';
    //         }));

    //         $publishContentArr = [];
    //         $result = [];
    //         $publishedContent = [];
            
    //         if($contentArr){
    //             return LtResponse::json('Record fetch', 201, 200, $contentArr);
    //         }else{
    //             return LtResponse::json('No record found', 101, 100);
    //         }
            
    //     }
        
    //     //  to fetch default theme region for publish and upublish
    //     public function publishedRegion($model){
    //         $ltId = $model->ltId;
    //         $contentArr = [];
    //         $publishContentArr = [];
            
    //         $packageModel = new Tbpackage();
    //         $isDefaultTheme = $packageModel->select('package_name')->where('isThemeDefault', '=', 1)->get()[0];
            
    //         $contentService = new TbContent();
    //         $result = $contentService->select('lt_id, content_name, content_type, package_name, sort_order, description, is_theme_default')->where('packageName', '=', $isDefaultTheme->packageName)->orderBy('sort_order')->get();
               
    //         $pageContentService = new TbPageContentService();
    //         $publishedContent = $pageContentService->publishContent($ltId);
          
    //       foreach ($publishedContent as $item) {
    //             $publishContentArr[$item->contentId] = 1;
    //         }

    //         foreach ($result as $item) {
    //             $ltId = $item->ltId;
    //             $item->isPublished = isset($publishContentArr[$ltId]) ? $publishContentArr[$ltId] : "0";
    //             $contentArr[] = $item;
    //         }
            
    //         usort($contentArr, function ($a, $b) {
    //             return $b->isPublished <=> $a->isPublished;
    //         });
            
    //         if($contentArr){
    //             return LtResponse::json('Record fetch', 201, 200, $contentArr);
    //         }else{
    //             return LtResponse::json('No record found', 101, 100);
    //         }
            
    //         // return json_encode($publishedContent);
    //     }
        
        
        
    //     public function fetchPublishPages (){
            
    //         $publishPage = $this->pageModel->select('lt_id, page_name, package_name, description, is_published, page_type')->where('isPublished', '=', '1')->andWhere('pageType', '!=', 'router')->get();
    //         return $publishPage;
            
    //     }
        
    //     function deletePages($ltId = null, $params = null){

    //         if($params !== null){
    //             $this->pageModel->remove('packageName', '=', $params)->orWhere('pageName', '=', $params)->delete();
    //             return $this->pageModel->responseJson();
    //         }else{
    //             $this->pageModel->processRequest();
    //             $this->pageModel->delete('ltId', '=', $ltId);
    //             return $this->pageModel->responseJson();
    //         }
            
            
            
    //     }
        
        
    // }
