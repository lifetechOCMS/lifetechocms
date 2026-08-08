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
            return  LtResponse::json('Content record already exist', 3503, 100); 
        }
        
        $this->contentModel->select('MAX(sort_order) AS highestSortNo')->where('packageName', '=', $packageName)->get();
        
        $highestSortNo = $this->contentModel->responseData[0]->highestSortNo ?? null; 
    
        if (empty($highestSortNo)) {
          $highestSortNo =   8;
        }else{ 
            $highestSortNo += 8;
        }
        
        // checking if role of developer is available, then auto publish it for developer
        $roleModel = new TbRole();
        $developerRoleId = "accbc87e4b5ce2";
        $roleExist = false;
        $exist = $roleModel->select()->where('ltId', '=', $developerRoleId)->get();
        if(count($exist) > 0){
            $roleExist = true;
        }
        
        $contentId = ltId();
        $this->contentModel->contentName = $contentName;
        $this->contentModel->ltId = $contentId;
        $this->contentModel->sortOrder = $highestSortNo;
        $this->contentModel->updatedBy = $this->userId;
        $this->contentModel->updatedAt = $this->ltNow;
        $this->contentModel->createdAt = $this->ltNow;
        if($roleExist && $mvcType == 'View'){
             $this->contentModel->$developerRoleId = 1;
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
                    $template = $this->autoCreateTemplate($packageName, $contentName, $mvcType, $isService,$contentType);
                    LtFile::path($path)->writeFile($contentName, $template);
                    LtFile::path($ddmpath)->writeFile($ddmFilename, $template);
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
     
                 //creating file and ddm file for this page $contentType
                    $path="packages/".$contentType.'s/'.$packageName.'/Contents';
                    $ddmpath="packages/".$contentType.'s/'.$packageName.'/ddm';
                    $ddmContentName = pathinfo($contentName, PATHINFO_FILENAME);
                    $ddmFilename = $ddmContentName.'.Contents.lifetech';
                    LtFile::path($path)->createFile($contentName);
                    LtFile::path($ddmpath)->createFile($ddmFilename);
            }else if($contentType == 'text'){
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
                        // $result = json_encode($menuModel);
                    
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
                'contentType' => $contentType
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
            

            if(count($existing) < 0) return LtResponse::json('Unable to delete content, no record found', 3506, 100);
                $contentData = $existing[0]; 
                $contentName = $contentData->contentName;
                $contentType = $contentData->contentType;
                $packageName = $contentData->packageName;
                $mvcType = ucfirst($contentData->mvcType);
              if($mvcType == 'View'){

                // deleting from Menu service
                $menuService = new TbMenuService();
                $resultMenu = $menuService->deleteMenu(params:$contentName);
                
                //delete page service
                $pageService = new TbPageService();
                $result = $pageService->deletePages(params:$contentName);
                
              }  
              if($contentType == 'module'){
                     // deleting page content service
                $pageContentService = new TbPageContentService();
                $publishedContent = $pageContentService->deletePageContent($ltId);
                    // deleting file
                  $path="packages/".$contentType.'s/'.$packageName.'/'.$mvcType.'s/';
                  LtFile::path($path)->deleteFile($contentName);
                  // deleting ddm file 
                  $ddmpath="packages/".$contentType.'s/'.$packageName.'/ddm';
                    $ddmContentName = pathinfo($contentName, PATHINFO_FILENAME);
                    $ddmFilename = $ddmContentName.'.'.$mvcType.'.lifetech';
                    LtFile::path($ddmpath)->deleteFile($ddmFilename);
              }else{
                  
                  $path="packages/".$contentType.'s/'.$packageName.'/contents';
                  LtFile::path($path)->deleteFile($contentName);
                  
                  $ddmpath="packages/".$contentType.'s/'.$packageName.'/ddm';
                  $ddmContentName = pathinfo($contentName, PATHINFO_FILENAME);
                  $ddmFilename = $ddmContentName.'.contents.lifetech';
                  LtFile::path($ddmpath)->deleteFile($ddmFilename);
              }
                $this->contentModel->processRequest();
                $this->contentModel->delete('ltId', '=', $ltId);
                
                return $this->contentModel->responseJson();
        }
        
        
       
    }
    
    public function togglePublished($packageName= null, $isPublished){
        $publish = false;
        $this->contentModel->updatedBy = LtSession::get('ltUid');
        $this->contentModel->isPublished = $isPublished;
        if($packageName !== null){
            $this->contentModel->update('packageName', '=', $packageName);
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

        // Safe Code
        // $this->request->codeContent = $uploadFileContent;
        // $this->request->contentName = $contentName;
        // $this->request->mvcType = $content->mvcType;
        // $this->request->packageName = $content->packageName;
        // $this->request->saveContentType = $contentType;
        // $this->request->storeType = $content->storeType;
        
        // echo LtDdm::saveEditCode();
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
        
        
}