<?php
namespace Lt\Modules\MdLt\Services;

use Lt\Modules\MdLt\Models\TbContent;
use Lt\Modules\MdLt\Models\TbRole;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\TbMenuService;
use Lt\Modules\MdLt\Services\TbPageService;
use Lt\Modules\MdLt\Services\LtFile;
use Lt\Modules\MdLt\Services\CaseConverter; 
use Lt\Modules\MdLt\Services\TbPageContentService; 

use Lt\Modules\MdLt\Models\TbPage;
use Lt\Modules\MdLt\Services\TbRoleService;

use DbConnect;
use LtDdm;



class TbContentService
{
    protected $contentModel;
    protected $request;
    protected $userId;
    protected $ltNow;
    
    public function __construct(){
        $this->contentModel = new TbContent();
        $this->ltNow = date('Y-m-d H:i:s');
        $this->userId = LtSession::get('ltUid');
        $this->request = new LtRequest;
    }
    
    public function store($model, $isService){
        $menuService = new TbMenuService();
        $pageService = new TbPageService();
        //check whether the parameter is object or an array
        if (is_object($model) && ($model->className ?? '') === 'tb_content') {
        $this->contentModel = $model;
        }
        // If it's an object or array (not tb_content), copy its values
        elseif (is_iterable($model)) {
            foreach ($model as $key => $value) {
                $this->contentModel->$key = $value;
            }
           
        }
        else {
            // Unsupported input
            return  LtResponse::json("Content Value should be Array or Object", 3502, 100); 
        }
        

      
        $contentName = $this->contentModel->contentName;
        $packageName = $this->contentModel->packageName;
        $mvcType = ucfirst($this->contentModel->mvcType);
        $contentType = $this->contentModel->contentType;
        $storeType = $this->contentModel->storeType;
        $pageType = $this->contentModel->pageType;
        $isPage = $this->contentModel->isPage;
        $isMenu = $this->contentModel->isMenu;
        $themeResourceType = $this->contentModel->themeResourceType;
        $additionalPath = $this->contentModel->additionalPath;
        
         //check for extension
            $extension = pathinfo($contentName, PATHINFO_EXTENSION);
            if($contentType == 'module' || $contentType == 'plugin'){
                if ($extension === '') {
                    if($mvcType == 'View'){
                        $contentName = $contentName.".html";
                    }else{
                        $contentName = $contentName.".php";
                    }
                    
                } 
            }else if ($contentType == 'theme'){
                if ($extension === '') {
                    $contentName = $contentName.".html";
                } 
            }
            
            
        $this->contentModel->select();
        if($packageName){
            $this->contentModel->where('contentName', '=', $contentName)->andWhere('packageName', '=', $packageName);
        }else{
            $this->contentModel->where('contentName', '=', $contentName);
        }
        
      $exist = $this->contentModel->get();
        
        if(count($exist) > 0){
            return  LtResponse::json("Content {$contentName} record already exist", 3503, 100); 
        }
        
        $this->contentModel->select('MAX(sort_order) AS highestSortNo')->where('packageName', '=', $packageName)->get();
        
        $highestSortNo = $this->contentModel->responseData[0]->highestSortNo ?? null; 
    
        if (empty($highestSortNo)) {
          $highestSortNo =   8;
        }else{ 
            $highestSortNo += 8;
        }
        
        // checking if role of developer is available, then auto publish it for developer
        $roleModel = new TbRoleService();
        $developerRoleId = "accbc87e4b5ce2";
        $roleExist = $roleModel->roleExist($developerRoleId);
        
        $contentId = ltId();
        $this->contentModel->contentName = $contentName;
        $this->contentModel->ltId = $contentId;
        $this->contentModel->sortOrder = $highestSortNo;
        $this->contentModel->updatedBy = $this->userId;
        $this->contentModel->updatedAt = $this->ltNow;
        $this->contentModel->createdAt = $this->ltNow;
        if($roleExist){
            if($mvcType == 'View' || $themeResourceType == 'View'){
                $this->contentModel->$developerRoleId = 1;
            }
        }

        // return $template;
        $this->contentModel->insert();
        
        if($this->contentModel->responseCategory == 200){
            
            if($contentType == 'module' || $contentType == 'plugin'){

                if($storeType === 'folder'){
                    //creating file and ddm file for this page $contentType
                    $path="packages/".$contentType.'s/'.$packageName.'/'.$mvcType.'s/';
                    $ddmpath="packages/".$contentType.'s/'.$packageName.'/ddm';
                    $ddmContentName = pathinfo($contentName, PATHINFO_FILENAME);
                    $ddmFilename = $ddmContentName.'.'.$mvcType.'.lifetech';
                    LtFile::path($path)->createFile($contentName);
                    LtFile::path($ddmpath)->createFile($ddmFilename);
                    $template = $this->autoCreateTemplate($packageName, $contentName, $mvcType, $isService, $contentType);
                    if($template){
                        LtFile::path($path)->writeFile($contentName, $template);
                        LtFile::path($ddmpath)->writeFile($ddmFilename, $template);
                    }
                    
                }

        
                if($mvcType == 'View'){
                    
                    $contentRoutePath = pathinfo($contentName, PATHINFO_FILENAME);
                    $path = '/'.$packageName.'/'.$contentRoutePath;
                    $pageId = ltId();
                    $pageModel = [
                        'ltId' => $pageId,
                        'pageName'=> $contentName, 
                        'packageName' => $packageName, 
                        'routePath' => $path,
                        'pageType' => $pageType,
                        'updatedAt' => $this->ltNow,
                        'createdAt' => $this->ltNow,
                    ];
                    if($roleExist){
                        $pageModel[$developerRoleId] = 1;
                    }
                    // creating page for the package content
                    
                    $result = $pageService->store($pageModel);
                    
                    if($pageType !== 'api'){
                                 // Create menu for the package
                         
                        $parentId = $menuService->getMenuId($packageName);
                        
                        $menuModel = [
                            'menuName'=> $contentName, 
                            'contentName'=> $contentName, 
                            'packageName' => $packageName, 
                            'routePath' => $path,
                            'parentMenuId' => $parentId->ltId,
                            'target' => '_blank',
                            'menuLevel' => '2',
                            'isPublished' => '1',
                            'publishedAt' => $this->ltNow,
                            'updatedAt' => $this->ltNow,
                            'createdAt' => $this->ltNow,
                        ];
                         if($roleExist){
                            $menuModel[$developerRoleId] = 1;
                        }
                        
                        $result = $menuService->store($menuModel);
                        
                    }
                    
                    // publish to page content
                    $pageContent = [
                        "pageId" => $pageId,
                        "contentId" => $contentId,
                        "publishStatus" => 1,
                        "contentType" => $contentType,
                        "packageName" => $packageName,
                        "publishSource" => "content"
                    ];
                    // 
                     $pageContentService = new TbPageContentService();
                     $publishedContent = $pageContentService->publishPageContent($pageContent);
                     
                    
                    if($pageType == 'theme'){
                        
                        $pageContent = [
                            "pageId" => $pageId,
                            "contentType" => 'theme',
                            "packageName" => $packageName,
                        ];
                      $pageContentService->publishPageRegion($pageContent);
                    }
                    
                    
                    $this->contentModel->responseData->routePath = $path;

                }
            }
            else if($contentType == 'theme'){
                    
                    if($storeType === 'folder'){
                        //creating file and ddm file for this page $contentType
                        if(!empty($additionalPath)){
                            $additionalPath = ltrim($additionalPath, '/');
                        }
                        
                        $path="packages/".$contentType.'s/'.$packageName.'/'.$themeResourceType.'s/'.$additionalPath;
                        
                        $ddmpath="packages/".$contentType.'s/'.$packageName.'/ddm';
                        $ddmContentName = pathinfo($contentName, PATHINFO_FILENAME);
                        $ddmFilename = $ddmContentName.'.'.$themeResourceType.'.lifetech';
                        LtFile::path($path)->createFile($contentName);
                        LtFile::path($ddmpath)->createFile($ddmFilename);
                    
                    }     
                    
                    if($themeResourceType == 'View'){
                        
                        $contentRoutePath = pathinfo($contentName, PATHINFO_FILENAME);
                        $path = '/'.$packageName.'/'.$contentRoutePath;
                        $pageId = ltId();
                        $pageModel = [
                            'ltId' => $pageId,
                            'pageName'=> $contentName, 
                            'packageName' => $packageName, 
                            'routePath' => $path,
                            'pageType' => "theme",
                            'updatedAt' => $this->ltNow,
                            'createdAt' => $this->ltNow,
                        ];
                        if($roleExist){
                            $pageModel[$developerRoleId] = 1;
                        }
                        
                        // // creating page for the package content
                        $pageService->store($pageModel);
                        
                        //// Menu Start
                        $parentId = $menuService->getMenuId($packageName);
                        
                        $menuModel = [
                            'menuName'=> $contentName, 
                            'contentName'=> $contentName, 
                            'packageName' => $packageName, 
                            'routePath' => $path,
                            'parentMenuId' => $parentId->ltId,
                            'target' => '_blank',
                            'menuLevel' => '2',
                            'isPublished' => '1',
                            'publishedAt' => $this->ltNow,
                            'updatedAt' => $this->ltNow,
                            'createdAt' => $this->ltNow,
                        ];
                        if($roleExist){
                            $menuModel[$developerRoleId] = 1;
                        }
                        // creating menu for the package content
                        $menuService->store($menuModel);
                        
                        // Page Content
                         // publish to page content
                        $pageContent = [
                            "pageId" => $pageId,
                            "contentId" => $contentId,
                            "publishStatus" => 1,
                            "contentType" => $contentType,
                            "packageName" => $packageName,
                            "publishSource" => "content"
                        ];
                        // 
                         $pageContentService = new TbPageContentService();
                         $pageContentService->publishPageContent($pageContent);
                         
                         //checking if Default layouts exist
                        $defaultPath ="packages/".$contentType.'s/'.$packageName.'/Layouts/Default.html';
                        if(file_exists($defaultPath)){
                            $layoutData = [
                                'layoutName' => "Default.html",
                                'contentId' =>  $contentId,
                                'layoutPackageName' => $packageName
                            ];
                            // assign Default layout to the content
                            $result = $this->assignLayouts($layoutData);
                            $decodeResult = json_decode($result);

                            if($decodeResult->responseCategory == 200){
                                $this->contentModel->responseData->themeLayoutPackage = $packageName;
                                $this->contentModel->responseData->themeLayout = "Default.html";
                            }
                        }else{
                        // Publishing for Region 
                            $contentModel = new TbContent(); // re-instantiating the model
                            $contentRecord = $contentModel->select('lt_id, sort_order')->where('packageName', '=', $packageName)->andWhere('themeResourceType','=', 'Component')->andWhere('isThemeDefault', '=', 1)->get();
                                
                                $pageContent = [
                                    "pageId" => $pageId,
                                    "contentType" => 'theme',
                                    "packageName" => $packageName,
                                ];
                                    
                            $pageContentService->publishPageRegion($pageContent, $contentRecord);

                        }
                        
                        $this->contentModel->responseData->routePath = $path;
                        $this->contentModel->responseData->isPublished = 1;
                        
                    }
                    
            }else if($contentType == 'text' || $contentType == 'html'){
                $path = '/'.$contentName;
                if($isMenu){
                    
                    $menuModel = [
                            'menuName'=> $contentName, 
                            'contentName'=> $contentName, 
                            'routePath' => $path,
                            'target' => '_blank',
                            'menuLevel' => '1',
                            'isPublished' => '1',
                            'publishedAt' => $this->ltNow
                        ];
                         if($roleExist){
                            $menuModel[$developerRoleId] = 1;
                        }
                        
                        $result = $menuService->store($menuModel);
                    
                }
                if($isPage){
                    $pageId = ltId();
                    $pageModel = [
                        'ltId' => $pageId,
                        'pageName'=> $contentName,
                        'routePath' => $path,
                        'pageType' => $pageType,
                        'updatedAt' => $this->ltNow,
                        'createdAt' => $this->ltNow,
                    ];
                    if($roleExist){
                        $pageModel[$developerRoleId] = 1;
                    }
                    // creating page for the package content
                    $pageService = new TbPageService();
                    $result = $pageService->store($pageModel);
                    
                    
                    // publish to page content
                    $pageContent = [
                        "pageId" => $pageId,
                        "contentId" => $contentId,
                        "publishStatus" => 1,
                        "contentType" => $contentType,
                        "publishSource" => "content"
                    ];
                    // 
                     $pageContentService = new TbPageContentService();
                     $publishedContent = $pageContentService->publishPageContent($pageContent);
                     
                    
                    if($pageType == 'theme'){
                        
                        $pageContent = [
                            "pageId" => $pageId,
                            "contentType" => 'theme',
                        ];
                        $pageContentService->publishPageRegion($pageContent);
                    }
                    
                    
                    $this->contentModel->responseData->routePath = $path;
                }
            }
            
        }
        // return $template;
        return $this->contentModel->responseJson();
        
    }

    public function autoCreate(){
        $sqlConnect = DbConnect::dbDriver();
    
        $tableName   = $this->request->selectedTable ?? null;
        $packageName = $this->request->packageName ?? null;
        $contentType = $this->request->contentType ?? null;
    
        $createModel      = (bool) ($this->request->model ?? false);
        $createController = (bool) ($this->request->controller ?? false);
        $createService    = (bool) ($this->request->service ?? false);
    
        if (empty($tableName) || empty($packageName)) {
            return LtResponse::json('Invalid request parameters.', 400, 100);
        }
    
        // Escape wildcard characters for LIKE
        $safeTableName = str_replace(['%', '_'], ['\%', '\_'], $tableName);
    
        // Check if table exists
        $stmt = $sqlConnect->prepare("SHOW TABLES LIKE :tableName");
        $stmt->execute([':tableName' => $safeTableName]);
        if (!$stmt->fetch(\PDO::FETCH_NUM)) {
            return LtResponse::json('Table does not exist.', 3507, 100);
        }
    
        // Build content base name from table name
        $parts = explode('_', $tableName);
        $baseName = '';
        foreach ($parts as $part) {
            $baseName .= ucfirst($part);
        }
    
        $contentMap = [];
    
        if ($createModel) {
            $contentMap['Model'] = $baseName;
        }
        if ($createController) {
            $contentMap['Controller'] = $baseName . 'Controller';
        }
        if ($createService) {
            $contentMap['Service'] = $baseName . 'Service';
        }
    
        if (empty($contentMap)) {
            return LtResponse::json('Nothing to create.', 204, 100);
        }
    
        $responses = [];

        foreach ($contentMap as $mvcType => $contentName) {
            $storeResponse = $this->store([
                'contentName' => $contentName,
                'storeType'   => 'folder',
                'mvcType'     => $mvcType,
                'packageName' => $packageName,
                'contentType' => $contentType,
                'isPublished' => 1
            ], $createService);
        
            // If store() returns JSON, decode it
            if (is_string($storeResponse)) {
                $storeResponse = json_decode($storeResponse, true);
            }
        
            $responses[] = $storeResponse;
        }
        
        return LtResponse::json('success', 200, 200, $responses);

    }
    
    public function autoCreateTemplate(string $packageName, string $contentName, string $type, bool $isService = false, string $contentType):string {

    $caseConverter = new CaseConverter();

    $extension = pathinfo($contentName, PATHINFO_EXTENSION);
    $fileNameWithNoExt = pathinfo($contentName, PATHINFO_FILENAME);
    $contentType = ucwords($contentType).'s';

    // ltImport('mdLt','TbContent.php');
    if($extension !== 'php') return "";
    if ($type === 'Model') {
        return "<?php
namespace Lt\\$contentType\\$packageName\\Models;

class {$fileNameWithNoExt} extends \\LtModel
{

}


";
                }
    // Controller or Service template
if (in_array($type, ['Controller', 'Service'], true)) {
    $result = implode('_', array_slice(explode('_', $caseConverter->pascalToSnake($fileNameWithNoExt)), 0, -1));
    $modelName = $caseConverter->snakeToPascal($result);
    $importContent = "";

    if ($type === "Controller") {
        if($isService){
        $importContent = "namespace Lt\\{$contentType}\\{$packageName}\\{$type}s;

use Lt\\{$contentType}\\{$packageName}\\Services\\{$modelName}Service;
        ";
        }else{
           $importContent = "namespace Lt\\{$contentType}\\{$packageName}\\{$type}s;
           
           "; 
        }
    } else {
        $derivedModelName = preg_replace("/Service$/", "", $fileNameWithNoExt);
        $importContent = "namespace Lt\\{$contentType}\\{$packageName}\\{$type}s;

use Lt\\{$contentType}\\{$packageName}\\Models\\{$derivedModelName};
        ";
    }

    return "<?php
{$importContent}
class {$fileNameWithNoExt}
{

}

";
}


            
            
    // return the template
    }
    
    // fetching publishing page to for content
    public function publishedPage($model){
        $ltId = $model->ltId;
        $contentType= $model->contentType;
        $pageArr = [];
        $publishPageArr = [];
        
          // to fetch all the publish pages  
        $pageService = new TbPageService();
        $result = $pageService->fetchPublishPages();
        
           
        // to fetch publish page from page content for the mergin
        $pageContentService = new TbPageContentService();
        $publishedContent = $pageContentService->publishPage($ltId, $contentType);
      
        foreach ($publishedContent as $item) {
            $publishPageArr[$item->pageId] = 1;
        }

        foreach ($result as $item) {
            $ltId = $item->ltId;
            $item->isPublished = isset($publishPageArr[$ltId]) ? $publishPageArr[$ltId] : "0";
            $pageArr[] = $item;
        }
        
        usort($pageArr, function ($a, $b) {
            return $b->isPublished <=> $a->isPublished;
        });
        
        $publishPageArr = [];
        $result = [];
        $publishedContent = [];
        
        if($pageArr){
            return LtResponse::json('Page record fetch successfully for publishing', 3504, 200, $pageArr);
        }else{
            return LtResponse::json('No page record found for publishing', 3505, 100);
        }
        
        
        
    }
    
    public function fetchPublishContent($ltId = null){
        
        $this->contentModel->select('lt_id, content_name, package_name, description, is_published, content_type');
        if($ltId !== null){
         $publishContent = $this->contentModel->where('ltId', '=', $ltId)->get();
        }else{
          $publishContent = $this->contentModel->get();
        }

        return $publishContent;
        
    }
    
    
    public function deleteContent($ltId = null, $packageName = null){
        
        if($packageName !== null){
            $this->contentModel->delete('packageName', '=', $packageName);
            return $this->contentModel->responseJson();
        }else{
            
            $existing = $this->contentModel->select()->where('ltId', '=', $ltId)->get();
            $id = $ltId;

            if(count($existing) < 0) return LtResponse::json('Unable to delete content, no record found', 3506, 100);
                $pageContentService = new TbPageContentService();
                $contentData = $existing[0]; 
                $contentName = $contentData->contentName;
                $contentType = $contentData->contentType;
                $packageName = $contentData->packageName;
                $mvcType = ucfirst($contentData->mvcType);
                $themeResourceType = ucfirst($contentData->themeResourceType);	
              if($mvcType == 'View' || $themeResourceType == 'View'){
                    $pageModel = new TbPage();
                    $pageData = $pageModel->select('lt_id')->where('pageName', '=', $contentName)->get()[0];
                    $id = $pageData->ltId;
                 
                // deleting from Menu service
                $menuService = new TbMenuService();
                $paramsData = [
                    'packageName' => $packageName,
                    'contentName' => $contentName
                    ];
                $resultMenu = $menuService->deleteMenu(params:$paramsData);
                
                //delete page service
                $pageService = new TbPageService();
                $result = $pageService->deletePages(params:$paramsData);
                
              }  
              if($contentType == 'module'){
                     // deleting page content service
                $publishedContent = $pageContentService->deletePageContent($id);
                    // deleting file
                  $path="packages/".$contentType.'s/'.$packageName.'/'.$mvcType.'s/';
                  LtFile::path($path)->deleteFile($contentName);
                  // deleting ddm file 
                  $ddmpath="packages/".$contentType.'s/'.$packageName.'/ddm';
                    $ddmContentName = pathinfo($contentName, PATHINFO_FILENAME);
                    $ddmFilename = $ddmContentName.'.'.$mvcType.'.lifetech';
                    LtFile::path($ddmpath)->deleteFile($ddmFilename);
              }elseif($contentType == 'theme'){

                $publishedContent = $pageContentService->deletePageContent($id);
                  $additionalPath = $contentData->additionalPath;
                  if(!empty($additionalPath)){
                        $additionalPath = ltrim($additionalPath, '/');
                    }
                     
                  $path = "packages/".$contentType.'s/'.$packageName.'/'.$themeResourceType.'s/'.$additionalPath;
                //   LtFile::path($path)->deleteFile($contentName);
                  
                  $ddmpath="packages/".$contentType.'s/'.$packageName.'/ddm';
                  $ddmContentName = pathinfo($contentName, PATHINFO_FILENAME);
                  $ddmFilename = $ddmContentName.'.'.$themeResourceType.'.lifetech';
                  LtFile::path($ddmpath)->deleteFile($ddmFilename);
              }
                $this->contentModel->processRequest();
                $this->contentModel->delete('ltId', '=', $ltId);
                
                return $this->contentModel->responseJson();
        }
        
        
       
    }
    
    
    public function togglePublishedContent($packageName= null, $isPublished){
        $publish = false;
        $this->contentModel->updatedBy = LtSession::get('ltUid');
            $this->contentModel->isPublished = $isPublished;
        if($packageName !== null){
            $this->contentModel->update('packageName', '=', $packageName);
            $publish =true;
        }else{
            $ltId = $this->request->ltId; 
            $this->contentModel->update('ltId', '=', $ltId); 
            $publish =true;
        }
        if($publish){
            return $this->contentModel->responseJson(); 
        }
    }
  
    public function download($model){
        $ltId = $model->ltId;
        if(empty($ltId)) return;
        
        $content = $model->select('content_name, package_name, content_type, mvc_type')->where('ltId', '=', $ltId)->get()[0];
        if(empty($content)) return;
        $contentType = $content->contentType;
        $contentName = $content->contentName;
        
        if($contentType === 'module'){
            $path="packages/".$contentType.'s/'.$content->packageName.'/'.$content->mvcType.'s/';
        }elseif($contentType === 'theme'){
            $path="packages/".$contentType.'s/'.$content->packageName.'/contents';
        }
        
        $result = LtFile::path($path)->download($contentName);
        return LtResponse::json('File downloaded successfully.', 3504, 200);
    }
    
    public function upload($model){
        $ltId = $model->ltId;
        if(empty($ltId)) return;
        
        $content = $model->select('content_name, package_name, content_type, mvc_type, store_type')->where('ltId', '=', $ltId)->get()[0];
        if(empty($content)) return;
        $contentType = $content->contentType;
        $contentName = $content->contentName;
        
        if($contentType === 'module'){
            $path="packages/".$contentType.'s/'.$content->packageName.'/'.$content->mvcType.'s/';
        }elseif($contentType === 'theme'){
            $path="packages/".$contentType.'s/'.$content->packageName.'/contents';
        }
        $uploadFileContentPath = $file = $_FILES['file']['tmp_name'];
        $uploadFileContent = file_get_contents($uploadFileContentPath);
        $result = LtFile::path($path)->writeFile($contentName, $uploadFileContent);
        
        return $result;

    }
  
    public function dashboardAnalysis(){
        $contents = $this->contentModel->select()->get();
        
        $theme = $module = $text = [];
        foreach($contents as $content){
            if($content->contentType === 'theme'){
                $theme[] = $content;
            }
            if($content->contentType === 'module'){
                $module[] = $content;
            }
            if($content->contentType === 'text'){
                $text[] = $content;
            }
        }
        $result = ['Theme Content' => count($theme), 'Module Content' => count($module), 'User Content' => count($text), 'totalContents' => count($contents)];
        return $result;
    }
    

    private function parsePathDetails(string $path){
        // 1. Normalize directory separators
        $normalizedPath = trim(str_replace('\\', '/', $path), '/');
        
      
        
        $segments = array_values(array_filter(explode('/', trim($normalizedPath, '/'))));
        
        // 3. Extract the first folder after package name (e.g. "Views", "components", "services")
        $firstFolder = array_shift($segments) ?? '';
          
        // Strip trailing 's' or 'S' (Views -> View, components -> component, services -> service)
        $type = preg_replace('/s$/i', '', $firstFolder);
    
        // 4. Pop the filename at the end if present
        if (!empty($segments)) {
            array_pop($segments);
        }
    
        // 5. Remaining segments make up the additional path
        $additionalPath = !empty($segments) ? '/' . implode('/', $segments) : '/';
    
        return [
            'type'           => $type,
            'additionalPath' => $additionalPath,
        ];
    }
    
    public function synchronizeContent(){
        $packageName = $this->request->packageName;
        $packageType = strtolower((string)$this->request->packageType);
        $items       = $this->request->items;
    
        if (empty($packageName) || empty($packageType) || empty($items) || !is_array($items)) {
            return LtResponse::json('Invalid synchronization payload', 3504, 100);
        }
    
        $responses = [];
    
        // Determine key dynamically: 'themeResourceType' vs 'mvcType'
        $typeKey = ($packageType === 'theme') ? 'themeResourceType' : 'mvcType';
    
        foreach ($items as $item) {
            
            $relativePath = $item['relativePath'] ?? '';
            
            // Extract type and subfolder path
            $pathDetails = $this->parsePathDetails($relativePath);
            $isServices = $pathDetails['type'] == 'Service' ? true : false;
            $isThemeDefault = $pathDetails['type'] == 'Component' ? 1 : 0;
            $payload = [
                'contentName'    => $item['contentName'],
                'storeType'      => 'folder',
                'description'    => "Synchronized from file manager",
                'packageName'    => $packageName,
                'isThemeDefault' => $isThemeDefault,
                 $typeKey         => $pathDetails['type'],           
                'additionalPath' => $pathDetails['additionalPath'], 
                'contentType'    => $packageType,
                'isPublished'   => 1
            ];
            

            $storeResponse = $this->store($payload, false);
    
            if (is_string($storeResponse)) {
                $storeResponse = json_decode($storeResponse, true);
            }
    
            $responses[] = $storeResponse;
        }
    
        if (empty($responses)) {
            return LtResponse::json('Failed to synchronize', 3504, 100);
        }
        /*
         * Process pending layouts.
         */
        $layoutResult = $this->processPendingLayouts($packageName,$packageType);
        
        // return LtResponse::json('Synchronization completed', 200, 200, ['contents' => $responses,'layouts' => $layoutResult]);
        
        return LtResponse::json('Synchronized successfully', 200, 200, $responses);
    }
    

    /**
     * Read pending layouts, assign them, and update JSON files.
     *
     * Successful items are appended to AppliedLayouts.json.
     * Failed items remain in PendingLayouts.json.
     */
    private function processPendingLayouts(string $packageName,string $packageType): array {
    
        $result = ['total' => 0,'successful' => 0,'failed' => 0];
    
        $layoutsDir ="packages/".$packageType.'s/'.$packageName.'/Storage/Layouts';
    
        $pendingFile = $layoutsDir . '/PendingLayouts.json';
        $appliedFile = $layoutsDir . '/AppliedLayouts.json';
    
        if (!is_file($pendingFile)) {
            return $result;
        }
    
/*
         * STEP 1: Read PendingLayouts.json.
         */
        $pendingContent = file_get_contents($pendingFile);

        if ($pendingContent === false) {
            throw new \RuntimeException(
                'Unable to read PendingLayouts.json'
            );
        }

        // Remove UTF-8 BOM if present and trim whitespace
        $pendingContent = trim($pendingContent, "\xEF\xBB\xBF \t\n\r\0\x0B");

        if ($pendingContent === '') {
            return $result;
        }

        try {
            $pendingLayouts = json_decode($pendingContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \RuntimeException(
                'Invalid JSON in PendingLayouts.json: ' . $e->getMessage()
            );
        }
    
        $result['total'] = count($pendingLayouts);
    
        if (empty($pendingLayouts)) {
            return $result;
        }
        
       
/*
         * STEP 2: Load existing applied layouts.
         */
        $appliedLayouts = [];

        if (is_file($appliedFile)) {

            $appliedContent = file_get_contents($appliedFile);

            if ($appliedContent === false) {
                throw new \RuntimeException('Unable to read AppliedLayouts.json');
            }

            $appliedContent = trim($appliedContent, "\xEF\xBB\xBF \t\n\r\0\x0B");

            if ($appliedContent !== '') {
                $appliedLayouts = json_decode($appliedContent, true, 512, JSON_THROW_ON_ERROR);

                if (!is_array($appliedLayouts)) {
                    throw new \RuntimeException('AppliedLayouts.json must contain an array');
                }
            }
        }
        
        
    
        /*
         * STEP 3: Attempt each pending layout assignment.
         */
        $remainingLayouts = [];
    
        foreach ($pendingLayouts as $layoutItem) {
    
            if (!is_array($layoutItem)) {
                $remainingLayouts[] = $layoutItem;
                $result['failed']++;
                continue;
            }
            
            try {
    
                // Calling assignLayouts() method.
                $assignRes = $this->assignLayouts($layoutItem);
                
                // $remainingLayouts[] = $assignRes;
                if (is_string($assignRes)) {
                    $assignRes = json_decode($assignRes,true);
                }
    
                $isSuccess = $assignRes['responseCategory'] == '200';
    
                if ($isSuccess) {
    
                    // Avoid appending an identical record twice.
                    $alreadyApplied = false;
    
                    foreach ($appliedLayouts as $existing) {
                        if ($existing === $layoutItem) {
                            $alreadyApplied = true;
                            break;
                        }
                    }
    
                    if (!$alreadyApplied) {
                        $appliedLayouts[] = $layoutItem;
                    }
    
                    $result['successful']++;
    
                } else {
    
                    $remainingLayouts[] = $layoutItem;
                    $result['failed']++;
                }
    
            } catch (\Throwable $e) {
    
                $remainingLayouts[] = $layoutItem;
                $result['failed']++;
    
                LtResponse::json('Layout assignment failed: '. $e->getMessage());
            }
        }
        
        /*
         * STEP 4: Persist successful assignments.
         *
         * Write AppliedLayouts.json before removing entries from
         * PendingLayouts.json to reduce loss after partial failures.
         */
        if ($result['successful'] > 0) {
    
            $appliedJson = json_encode(array_values($appliedLayouts), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    
            if (file_put_contents($appliedFile, $appliedJson, LOCK_EX) === false) {
                throw new \RuntimeException(
                    'Failed to update AppliedLayouts.json'
                );
            }
    
            $pendingJson = json_encode(array_values($remainingLayouts), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    
            if (file_put_contents($pendingFile, $pendingJson, LOCK_EX) === false) {
                throw new \RuntimeException(
                    'Failed to update PendingLayouts.json'
                );
            }
        }
    
        return $result;
    }

    
    public function scanContent(){
         
        $packageName = $this->request->packageName;
        $packageType = strtolower((string)$this->request->packageType);
    
        if (empty($packageName) || empty($packageType)) {
            return LtResponse::json('Package Name and Package Type cannot be empty', 3504, 100);
        }
    
        $folderMap = [
            'theme'  => ['Components', 'Layouts', 'Views', 'Assets'],
            'module' => ['Model', 'Controller', 'Services', 'Views'],
            'plugin' => ['Model', 'Controller', 'Services', 'Views'],
        ];
    
        if (!isset($folderMap[$packageType])) {
            return LtResponse::json('Unsupported package type', 3504, 100);
        }
    
        $baseFolder = ($packageType === 'theme') ? 'themes' : $packageType . 's';
        $packageBasePath = "packages/{$baseFolder}/{$packageName}";
    
        if (!is_dir($packageBasePath)) {
            return LtResponse::json("Directory not found: {$packageBasePath}", 404, 100);
        }
    
        // 1. Fetch existing content records for this package
        
        $registered = $this->contentModel->select('content_name')->where('packageName', '=', $packageName)->andWhere('contentType', '=', $packageType)->get();
    
        $flipped = array_column($registered, 'contentName');
        $registeredLookup = array_flip($flipped);
        $discovered = [];
        
        // 2. Scan folders recursively
        foreach ($folderMap[$packageType] as $folder) {
            $folderPath = "{$packageBasePath}/{$folder}";
            $discovered[$folder] = [];
            // $discovered[$folder] = $folderPath;
    
            if (!is_dir($folderPath)) {
                continue;
            }
            
            
            
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($folderPath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
    
            foreach ($iterator as $file) {
                if ($file->isDir()) continue;
    
                $fileName = $file->getFilename();
    
                // Ignore auto-generated index.php & dotfiles
                if (strtolower($fileName) === 'index.php' || str_starts_with($fileName, '.')) {
                    continue;
                }
    
                $normalizedFilePath = str_replace('\\', '/', $file->getPathname()); // e.g., "packages/themes/ThemeBrainDump/Views/UserProfile.html"
                
                if (str_contains($normalizedFilePath, $packageName)) {
                    // 1. Find exactly where 'ThemeBrainDump' starts
                    $packagePos = strpos($normalizedFilePath, $packageName);
                    
                    // 2. Extract everything after 'ThemeBrainDump'
                    $subPath = substr($normalizedFilePath, $packagePos + strlen($packageName));
                    
                    // 3. Strip any leading forward slashes to get a clean relative path
                    $subPath = ltrim($subPath, '/');
                } else {
                    $subPath = $normalizedFilePath;
                }
                
                // Exclude already registered files
                if (isset($registeredLookup[$fileName])) {
                    continue;
                }
    
                $discovered[$folder][] = [
                    'contentName'  => $fileName,
                    'relativePath' => $subPath,
                    'category'     => $folder
                ];
            }
        }
    
        return LtResponse::json('Scan successful', 200, 200, $discovered);
    }
    
    public function getFolders(){
        $resoucesType = $this->request->type; // e.g., 'View', 'Component'
        $additionalPath = $this->request->path; // e.g., '/', '/auth'
        $packageName = $this->request->packageName;
       
        if(empty($resoucesType)){
            return LtResponse::json('Resources Type can\'t be empty', 3504, 100);
        }

        // 1. Properly format the additional path with a leading slash if it's not empty
        $pathSuffix = '';
        if(!empty($additionalPath) && $additionalPath !== '/'){
            $pathSuffix = '/' . trim($additionalPath, '/');
        }

        // 2. Format the resource type
        $resoucesType = $resoucesType . 's'; 

        // 3. Safely build the full path
        $folderPath = rtrim("packages/themes/" . $packageName . "/" . $resoucesType . $pathSuffix, '/');
       
        $responses = [];

        // 4. Verify directory exists before attempting to scan it
        if(is_dir($folderPath)){
            // array_diff removes '.' and '..' cleanly without relying on array_slice
            $files = array_diff(scandir($folderPath), ['.', '..']);
            
            foreach ($files as $file){
                $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;
                
                // 5. Only push to response if it is ACTUALLY a directory
                if(is_dir($filePath)){
                    // We only need to return the folder name to match the frontend dropdown logic
                    $responses[] = $file; 
                }
            }
        }
        
        if(empty($responses)) return LtResponse::json('No subdirectory found', 101, 100);
        
        return LtResponse::json('validate successfully', 200, 200, $responses);
    }
    
    public function fetchLayouts(){
        $packageName = $this->request->packageName;
    
        if (empty($packageName)) {
            return LtResponse::json('Package Name cannot be empty', 3504, 100);
        }
    
        $layouts = $this->contentModel
            ->select('content_name, description, package_name')
            ->where('themeResourceType', '=', 'Layout')
            ->get();
    
        if (empty($layouts)) {
            return LtResponse::json('No Layouts found', 101, 100);
        }
    
        // Group layouts by package_name
        $grouped = [];
        foreach ($layouts as $layout) {
            $pkg = $layout->packageName ?? 'Other';
            $grouped[$pkg][] = [
                'contentName' => $layout->contentName,
                'description' => $layout->description ?? '',
                'packageName' => $pkg
            ];
        }
    
        // Sort to ensure the requested package appears at the top
        uksort($grouped, function($a, $b) use ($packageName) {
            if ($a === $packageName) return -1;
            if ($b === $packageName) return 1;
            return strcasecmp($a, $b);
        });
    
        return LtResponse::json('Layouts fetched successfully', 200, 200, [
            'currentPackage' => $packageName,
            'groupedLayouts' => $grouped
        ]);
    }
    
    public function assignLayouts($data){
        
        if (!is_array($data)) {
            return LtResponse::error('Invalid layout assignment data', 3504, 100);
        }
        $sqlConnect = DbConnect::dbDriver();
        
        $layoutName = $data['layoutName'] ?? null;
        $contentDetails = $data['contentName'] ?? $data['contentId'];
        $layoutPackageName = $data['layoutPackageName'] ?? null;
    
        if (empty($layoutName) ||empty($contentDetails) || empty($layoutPackageName)) {
            return LtResponse::error('Please provide layout, content and package name', 3504, 100);
        }
    
        /*
         * STEP 1: Find the content by ID or content name.
         */
        $searchById = ctype_digit((string)$contentDetails);
        
        $sql = "
            SELECT
                content_name AS contentName,
                lt_id AS ltId,
                package_name AS packageName
            FROM tb_content
            WHERE package_name = :packageName
              AND " . ($searchById
                  ? "lt_id = :contentDetails"
                  : "content_name = :contentDetails") . "
            LIMIT 1
        ";
        
        $stmt = $sqlConnect->prepare($sql);
        
        $stmt->execute([
            ':packageName' => $layoutPackageName,
            ':contentDetails' => $contentDetails
        ]);
        
        $content = $stmt->fetch(\PDO::FETCH_OBJ);
        
        if(empty($content)) return LtResponse::json('No Contents found', 101, 100);
  
        $pageModel = new TbPage();
        $pageRecords = $pageModel->select('lt_id')->where('pageName', '=', $content->contentName)->andWhere('packageName', '=', $content->packageName)->get();
        $pageData = $pageRecords[0] ?? null;
        if(empty($pageData)) return LtResponse::json('No Page found', 101, 100);
        
        /*
         * STEP 3: Assign the layout to the page.
         */
        $pageModel->themeLayout = $layoutName;
        $pageModel->themeLayoutPackage = $layoutPackageName;
        $pageModel->updatedBy = $this->userId;
        $pageModel->updatedAt = $this->ltNow;
        $pageModel->update('ltId', '=', $pageData->ltId);
        
        return $pageModel->responseJson(); 

    }
    
    
    public function contentRegions(){
        $contentName = $this->request->contentName;
        $contentId = $this->request->contentId;
        $packageName = $this->request->packageName;
        $pageModel = new TbPage();
        $sqlConnect = DbConnect::dbDriver();
       
        if (empty($contentName) || empty($packageName)) {
            return LtResponse::json('please provide content name and package name', 3504, 100);
        }
        
        $pageData = $pageModel->select('lt_id, page_name')->where('pageName', '=', $contentName)->andWhere('packageName', '=', $packageName)->get();
        
        if(empty($pageData)) return LtResponse::json('No Page found for content $contentName', 101, 100);
        
        $pageContentService = new TbPageContentService();
        $publishedContents = $pageContentService->fetchPublished($pageData[0]->ltId);
        
        if(empty($publishedContents)) return LtResponse::json('No Publish Content Found', 101, 100);
        
        $contentIds = array_column($publishedContents, 'contentId');
        
        $placeholders = implode(', ', array_fill(0, count($contentIds), '?'));
        
        $stmt = $sqlConnect->prepare("SELECT lt_id, content_name, package_name FROM tb_content WHERE lt_id IN ($placeholders)");

        // 3. Execute and pass the raw array directly
        $stmt->execute($contentIds);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        //map the result ltId to contentName
        $nameMap = [];
        $pkgMap = [];
        if(!empty($results)){
            foreach($results as $row){
                $nameMap[$row['lt_id']] = $row['content_name'];
                $pkgMap[$row['lt_id']] = $row['package_name'];
            }
        }
        
        //update the original record with name
        foreach($publishedContents as &$content){
            $name = $nameMap[$content->contentId] ?? '';
            $pkg = $pkgMap[$content->contentId] ?? '';
            $content->contentName = $name;
            $content->packageName = $pkg;
        }
        
        unset($content);

        if(empty($publishedContents)) return LtResponse::json('No Region found', 101, 100);
        
        return LtResponse::json('Regions Found', 200, 200, $publishedContents);
    }
        
        
}