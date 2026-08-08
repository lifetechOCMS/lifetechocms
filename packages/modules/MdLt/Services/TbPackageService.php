<?php
namespace Lt\Modules\MdLt\Services; 

use Lt\Modules\MdLt\Models\TbPage;
use Lt\Modules\MdLt\Models\TbContent;
use Lt\Modules\MdLt\Models\TbMenu;
use Lt\Modules\MdLt\Models\TbPageContent;
use Lt\Modules\MdLt\Models\TbPackage;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\TbMenuService;
use Lt\Modules\MdLt\Services\TbPageService;
use Lt\Modules\MdLt\Services\LtFile;
use Lt\Modules\MdLt\Services\TbContentService;

use Lt\Modules\MdLt\Services\TbRoleService;

use DbConnect; 
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class TbPackageService
{
        protected $packageModel;
        protected $request;
        protected $userId;
        protected $ltNow;
        public $sqlConnect;
        
        public function __construct(){
            $this->packageModel = new TbPackage();
            $this->request = new LtRequest;
            $this->ltNow = date('Y-m-d H:i:s');
            $this->userId = LtSession::get('ltUid');
            $this->sqlConnect = DbConnect::dbDriver();
        }
        
        public function store($model){

            $packageName = $model->packageName;
            $packageType = $model->packageType;
            
            if (str_contains($packageName, ' ')) {
                return  LtResponse::json('Package name must not contain spaces.', 3303, 100); 
            } 
                     
            $exist = $model->select()->where('packageName', '=', $packageName)->get();
            
            if(count($exist) > 0){
                return  LtResponse::json('Package Record already exist', 3304, 100); 
            }
            
            if ($packageType == 'module' && !str_starts_with($packageName, 'Md')) {
                        // does NOT start with "md"
                return LtResponse::json('Module Package Should start with Md', 3305, 100);
            }
            
            if ($packageType == 'plugin' && !str_starts_with($packageName, 'Plg')) {
                // does NOT start with "md"
                return LtResponse::json('Plugin Package Should start with Plg', 3305, 100);
            }
            if ($packageType == 'theme' && !str_starts_with($packageName, 'Theme')) {
            // does NOT start with "md"
                 return LtResponse::json('Theme Package Should start with Theme ', 3307, 100);
            }

            $packageId = ltId();
            $model->ltId = $packageId;
            $model->updatedBy = $this->userId;
            $model->updatedAt = $this->ltNow;
            $model->createdAt = $this->ltNow;
            
            $model->insert();
            
            $data = [
                        "ltId" => $packageId,
                        "packageName" => $packageName,
                        "packageType" => $packageType,
                        "version"     => $model->version,
                        "dependsOn"     => $model->dependsOn,
                        "isPublished"     => $model->isPublished ?? '0',
                        "readme"     => $model->readme,
                        "languageUsed" => $model->languageUsed
                        ];
                        
             // checking if role of developer is available, then auto publish it for developer
            $roleModel = new TbRoleService();
            $developerRoleId = "accbc87e4b5ce2";
            $roleExist = $roleModel->roleExist($developerRoleId);
            
            
            $licenceContent = "MIT License
            
                            Copyright (c) 2024 , LifetechOCMS
                            
                            Permission is hereby granted, free of charge, to any person obtaining a copy
                            of this software and associated documentation files (the 'Software'), to deal
                            in the Software without restriction, including without limitation the rights
                            to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
                            copies of the Software, and to permit persons to whom the Software is
                            furnished to do so, subject to the following conditions:
                            
                            The above copyright notice and this permission notice shall be included in all
                            copies or substantial portions of the Software.
                            
                            THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
                            IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
                            FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
                            AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
                            LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
                            OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
                            SOFTWARE.";
                    
            $jsonData = json_encode($data, JSON_PRETTY_PRINT);

            if($model->responseCategory == '200'){
                
                 //creating file and ddm file for this page
                if($packageType == 'module' || $packageType == 'plugin'){
                     
                  
                    $path="packages/".$packageType.'s/';
                    LtFile::path($path)->createFolder($packageName);
                    $path="packages/".$packageType.'s/'.$packageName.'/';
                    LtFile::path($path)->createFolder('Controllers');
                    LtFile::path($path)->createFolder('Export');
                    LtFile::path($path)->createFolder('ddm');
                    LtFile::path($path)->createFolder('Media');
                    LtFile::path($path)->createFolder('Models');
                    LtFile::path($path)->createFolder('Services');
                    LtFile::path($path)->createFolder('Views');
                    LtFile::path($path)->createFile('LICENSE');
                    LtFile::path($path)->createFile('README.md');
                    LtFile::path($path)->createFile('package.json');
                    LtFile::path($path)->writeFile('package.json', $jsonData);
                    LtFile::path($path)->writeFile('LICENSE', $licenceContent);
                    $path="packages/".$packageType.'s/'.$packageName.'/Export';
                    LtFile::path($path)->createFile('page.json');
                    LtFile::path($path)->createFile('content.json');
                    LtFile::path($path)->createFile('menu.json');
                    LtFile::path($path)->createFile('table.json');
                    LtFile::path($path)->createFile('pageContent.json');
                    
                    $contentName = ucfirst($packageName).'Routes.php';
                    // LtFile::path($path)->createFile($routeFilename);
                    
                    
                    // Create menu for the package
                    $serviceMenuModel = [
                        'menuName'=> $packageName,
                        'packageName' => $packageName,   
                        'routePath' => '#',
                        'target' => 'self',
                        'menuLevel' => '1',
                        'isPublished' => '1',  
                        'sortOrder' => '8',  
                    ];
                    if($roleExist){
                        $serviceMenuModel[$developerRoleId] = 1;
                    }
                    
                    $menuService = new TbMenuService();
                    $result = $menuService->store($serviceMenuModel);
                    
                    $serviceModel = [
                        'contentName'=> $contentName, 
                        'packageName' => $packageName,   
                        'contentType' => $packageType,
                        'storeType' => 'folder',
                        'mvcType' => 'service',
                        'contentSource' => $packageType,
                        'isPublished' => '1',  
                        'sortOrder' => '8',  
                    ];

                     $contentService = new TbContentService();
                     $contentService->store($serviceModel, false);
                    
                     return LtResponse::json('Package created successfully', 3306, 200, $model->responseData);
                    
                }else if($packageType == 'theme'){
                     
                    $path="packages/".$packageType.'s/';
                    LtFile::path($path)->createFolder($packageName);
                    $path="packages/".$packageType.'s/'.$packageName.'/';
                    LtFile::path($path)->createFolder('Contents');
                    LtFile::path($path)->createFolder('Media');
                    LtFile::path($path)->createFolder('Export');
                    LtFile::path($path)->createFolder('ddm');
                    LtFile::path($path)->createFile('LICENSE');
                    LtFile::path($path)->createFile('README.md');
                    LtFile::path($path)->createFile('package.json');
                    LtFile::path($path)->writeFile('package.json', $jsonData);
                    LtFile::path($path)->writeFile('LICENSE', $licenceContent);
                    $path="packages/".$packageType.'s/'.$packageName.'/Export';
                    LtFile::path($path)->createFile('page.json');
                    LtFile::path($path)->createFile('content.json');
                    LtFile::path($path)->createFile('menu.json');
                    LtFile::path($path)->createFile('table.json');
                    LtFile::path($path)->createFile('pageContent.json');
                    return LtResponse::json('Package created successfully', 3306, 200, $model->responseData);
                    
                }
            }
            
        }

        
        public function updatePackage($model){
            
            $packageName = $this->request->packageName;
            $packageType = $this->request->packageType;
            $model->updatedBy = $this->userId;
            
            if($packageType == 'module'){
                $path="packages/".$packageType.'s/'.$packageName.'/';
                $packageJsonPath = $path . 'package.json';

                $packageJsonContent = file_get_contents($packageJsonPath);
                if($packageJsonContent === false){
                    return LtResponse::json("Failed to read package json file ", "3308");
                }
                $data = json_decode($packageJsonContent, true);
                $data["version"] = $model->version;
                $data["dependsOn"] = $model->dependsOn;
                $data["isPublished"] = $model->isPublished;
                $data["readme"] = $model->readme;
                $data["languageUsed"] = $model->languageUsed;
                $jsonData = json_encode($data, JSON_PRETTY_PRINT);
                LtFile::path($path)->writeFile('package.json', $jsonData);
            }

            $model->update('ltId', '=', $model->ltId);
            return $model->responseJson(); 
            
            
        }
        /// new
        public function filteredPackages()
        {
            $filtered = array_values(array_filter([
                $this->request->packageTypeModule ?? null,
                $this->request->packageTypeTheme ?? null,
                $this->request->packageTypePlugin ?? null
            ]));
        
            if (!$filtered) {
                return LtResponse::json("No record Found", '101', '100');
            }
        
            $sql = sprintf(
                "SELECT package_name AS packageName, lt_id AS ltId
                 FROM tb_package
                 WHERE package_type IN (%s)",
                implode(',', array_fill(0, count($filtered), '?'))
            );
        
            $stmt = $this->sqlConnect->prepare($sql);
            $stmt->execute($filtered);
            $result = $stmt->fetchAll(\PDO::FETCH_OBJ);
            if(empty($result)) return LtResponse::json("No record Found", '101', '100');
            return LtResponse::json("Filtered Record fetched successfully", '309', '200', $result);
        }
        
        function updatePackageModifiedDate($params){
            
            $this->packageModel->updatedBy = LtSession::get('ltUid');
            $this->packageModel->update('packageName', '=', $params);
            
        }
        
        public function exportTable(){

            $tableRecord = [];
            $sql = "SHOW TABLES LIKE '%tb%_'";
            $stmt = $this->sqlConnect->query($sql);
            $result = $stmt->fetchAll(\PDO::FETCH_NUM);
            
            forEach($result as $r){
                $tableRecord[] = ['tableName' => $r[0]];
            }
            
            
            return LtResponse::json("Table data retrieved successfully for export.", '3309', '200', $tableRecord);
            
        }
        
        //   public function exportPackage(){
        //     $packageName = $this->request->packageName;
        //     $packageType = $this->request->packageType;
        //     $useSourceCode = $this->request->useSourceCode;
        //     $slectedTables = json_decode($this->request->selectedTables);
        //     $pageJsonArr = [];
        //     $contentJsonArr = [];
        //     $menuJsonArr = [];
        //     $pageContentJsonArr = [];
        //     $tableJsonArr = [];
            
        //     if(!class_exists('ZipArchive')){
        //          return LtResponse::json('ZipArchive is not Activated on your PhP Configuration. You need to Activate ZipArchive! on your PhP Configuration!!!  RETRY', 1999, 100);
        //      }
        //     $pageModel = new TbPage();
        //     $pageRecord = $pageModel->select()->where('packageName', '=', $packageName)->get();
            
        //     foreach($pageRecord as $value){
        //         $pageJsonArr[] = [
        //             'ltId'=> $value->ltId,
        //             'packageName'=> $value->packageName,
        //             'pageName'=> $value->pageName,
        //             'pageType'=> $value->pageType,
        //             'routeContent'=> $value->routeContent,
        //             'routeFileName'=> $value->routeFileName,
        //             'routeHandler'=> $value->routeHandler,
        //             'routeMethod'=> $value->routeMethod,
        //             'routePath'=> $value->routePath,
        //             'routePayload'=> $value->routePayload,
        //             'routeResponse'=> $value->routeResponse,
        //             'routeUpdateStatus'=> $value->routeUpdateStatus,
        //             'apiToAllowRouters'=> $value->apiToAllowRouters,
        //             'description'=> $value->description,
        //         ];
        //     }
        //     $pageJsonData = json_encode($pageJsonArr, JSON_PRETTY_PRINT);
            
             
          
        //     $contentModel = new TbContent();
        //     $contentRecord = $contentModel->select()->where('packageName', '=', $packageName)->get();
    
        //     foreach($contentRecord as $value){
        //         $contentJsonArr[] = [
        //             'ltId'=> $value->ltId,
        //             'packageName'=> $value->packageName,
        //             'pageType'=> $value->pageType,
        //             'useLiferazor'=> $value->useLiferazor,
        //             'storeType'=> $value->storeType,
        //             'mvcType'=> $value->mvcType,
        //             'isThemeDefault'=> $value->isThemeDefault,
        //             'contentType'=> $value->contentType,
        //             'contentSource'=> $value->contentSource,
        //             'contentName'=> $value->contentName,
        //             'contentCategory'=> $value->contentCategory,
        //             'contentBody'=> $value->contentBody,
        //             'ccurrent'=> $value->ccurrent,
        //             'ccname'=> $value->ccname,
        //             'ccname'=> $value->ccname,
        //             'sortOrder'=> $value->sortOrder,
        //             'description'=> $value->description,
        //         ];
        //     }
        //     $contentJsonData = json_encode($contentJsonArr, JSON_PRETTY_PRINT);

        //     $menuModel = new TbMenu();
        //     $menuRecord = $menuModel->select()->where('packageName', '=', $packageName)->get();
        //       foreach($menuRecord as $value){
        //             $menuJsonArr[] = [
        //                 'ltId'=> $value->ltId,
        //                 'packageName'=> $value->packageName,
        //                 'menuLevel'=> $value->menuLevel,
        //                 'menuName'=> $value->menuName,
        //                 'contentName'=> $value->contentName,
        //                 'parentMenuId'=> $value->parentMenuId,
        //                 'routePath'=> $value->routePath,
        //                 'slug'=> $value->slug,
        //                 'target'=> $value->target,
        //                 'sortOrder'=> $value->sortOrder,
        //             ];
        //     }
        //     $menuJsonData = json_encode($menuJsonArr, JSON_PRETTY_PRINT);
            
        //     $pageContentModel = new TbPageContent();
        //     $pageContentRecord = $pageContentModel->select()->where('packageName', '=', $packageName)->andWhere('contentType', '<>', 'theme')->get();
        //     foreach($pageContentRecord as $value){
        //             $pageContentJsonArr[] = [
        //                 'ltId'=> $value->ltId,
        //                 'contentId'=> $value->contentId,
        //                 'contentType'=> $value->contentType,
        //                 'packageName'=> $value->packageName,
        //                 'pageId'=> $value->pageId,
        //                 'sortOrder'=> $value->sortOrder,
        //                 'publishSource' => $value->publishSource
        //             ];
        //     }
        //     $pageContentJsonData = json_encode($pageContentJsonArr, JSON_PRETTY_PRINT);
            
        //     foreach($slectedTables as $table){
        //           $sql = "SHOW CREATE TABLE $table";
        //           $stmt = $this->sqlConnect->query($sql);
        //           $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        //           $createStmt = $result['Create Table'];
        //           $createStmtModified = str_replace("CREATE TABLE","CREATE TABLE IF NOT EXISTS", $createStmt);
        //           $tableJsonArr[] = ['tableData'=>$createStmtModified];
        //     }
            
        //     $tableStructureJsonData = json_encode($tableJsonArr, JSON_PRETTY_PRINT);
            
        //      $path="packages/".$packageType.'s/'.$packageName.'/Export/';
        //      LtFile::path($path)->writeFile('content.json', $contentJsonData);
        //      LtFile::path($path)->writeFile('menu.json', $menuJsonData);
        //      LtFile::path($path)->writeFile('table.json', $tableStructureJsonData);
        //      LtFile::path($path)->writeFile('page.json', $pageJsonData);
        //      LtFile::path($path)->writeFile('pageContent.json', $pageContentJsonData);
            
        //       $dstPath="storage/tmp/export/";   
        //       $pathToPackage="packages/".$packageType.'s/'.$packageName;
             
        //       $res = LtFile::path($pathToPackage)->copy($dstPath);
               
        //       if(!$useSourceCode){
        //          $dstPath="storage/tmp/export/".$packageName;
        //          $result = LtFile::path($dstPath)->unlink('Views','no');
        //          $result = LtFile::path($dstPath)->unlink('Services','no');
        //          $result = LtFile::path($dstPath)->unlink('Models','no');
        //          $result = LtFile::path($dstPath)->unlink('Controllers','no');
        //          $result = LtFile::path($dstPath)->unlink('Contents','no');
        //       }
            
        //      // create Zip file
        //      $path="storage/tmp/export/"; 
           
        //     $zipName = $packageName. '.zip';
        //     $zipFilePath = $path . "/" . $zipName;
            
        //     $result = $this->zipFolder($dstPath, $zipFilePath);
        //     return json_encode($result);
        //     //  $result = LtFile::path($path)->download($packageName);
        //      $result = LtFile::path($path)->unlink($packageName); 
        //     // return LtResponse::json('Package exported successfully.', 3310, 200);
        //     // return json_encode($result);
            
            
        // }
        
        public function exportPackage(){
            $packageName = $this->request->packageName;
            $packageType = $this->request->packageType;
            $useSourceCode = $this->request->useSourceCode;
            $slectedTables = json_decode($this->request->selectedTables);
            $pageJsonArr = [];
            $contentJsonArr = [];
            $menuJsonArr = [];
            $pageContentJsonArr = [];
            $tableJsonArr = [];
            
            if(!class_exists('ZipArchive')){
                 return LtResponse::json('ZipArchive is not Activated on your PhP Configuration. You need to Activate ZipArchive! on your PhP Configuration!!!  RETRY', 1999, 100);
             }
            
            $pageModel = new TbPage();
            $pageRecord = $pageModel->select()->where('packageName', '=', $packageName)->get();
            
            foreach($pageRecord as $value){
                $pageJsonArr[] = [
                    'ltId'=> $value->ltId,
                    'packageName'=> $value->packageName,
                    'pageName'=> $value->pageName,
                    'pageType'=> $value->pageType,
                    'routeContent'=> $value->routeContent,
                    'routeFileName'=> $value->routeFileName,
                    'routeHandler'=> $value->routeHandler,
                    'routeMethod'=> $value->routeMethod,
                    'routePath'=> $value->routePath,
                    'routePayload'=> $value->routePayload,
                    'routeResponse'=> $value->routeResponse,
                    'routeUpdateStatus'=> $value->routeUpdateStatus,
                    'apiToAllowRouters'=> $value->apiToAllowRouters,
                    'description'=> $value->description,
                ];
            }
            $pageJsonData = json_encode($pageJsonArr, JSON_PRETTY_PRINT);
            
             
          
            $contentModel = new TbContent();
            $contentRecord = $contentModel->select()->where('packageName', '=', $packageName)->get();
    
            foreach($contentRecord as $value){
                $contentJsonArr[] = [
                    'ltId'=> $value->ltId,
                    'packageName'=> $value->packageName,
                    'pageType'=> $value->pageType,
                    'useLiferazor'=> $value->useLiferazor,
                    'storeType'=> $value->storeType,
                    'mvcType'=> $value->mvcType,
                    'isThemeDefault'=> $value->isThemeDefault,
                    'contentType'=> $value->contentType,
                    'contentSource'=> $value->contentSource,
                    'contentName'=> $value->contentName,
                    'contentCategory'=> $value->contentCategory,
                    'contentBody'=> $value->contentBody,
                    'ccurrent'=> $value->ccurrent,
                    'ccname'=> $value->ccname,
                    'ccname'=> $value->ccname,
                    'sortOrder'=> $value->sortOrder,
                    'description'=> $value->description,
                ];
            }
            $contentJsonData = json_encode($contentJsonArr, JSON_PRETTY_PRINT);

            $menuModel = new TbMenu();
            $menuRecord = $menuModel->select()->where('packageName', '=', $packageName)->get();
              foreach($menuRecord as $value){
                    $menuJsonArr[] = [
                        'ltId'=> $value->ltId,
                        'packageName'=> $value->packageName,
                        'menuLevel'=> $value->menuLevel,
                        'menuName'=> $value->menuName,
                        'contentName'=> $value->contentName,
                        'parentMenuId'=> $value->parentMenuId,
                        'routePath'=> $value->routePath,
                        'slug'=> $value->slug,
                        'target'=> $value->target,
                        'sortOrder'=> $value->sortOrder,
                    ];
            }
            $menuJsonData = json_encode($menuJsonArr, JSON_PRETTY_PRINT);
            
            $pageContentModel = new TbPageContent();
            $pageContentRecord = $pageContentModel->select()->where('packageName', '=', $packageName)->andWhere('contentType', '<>', 'theme')->get();
            foreach($pageContentRecord as $value){
                    $pageContentJsonArr[] = [
                        'ltId'=> $value->ltId,
                        'contentId'=> $value->contentId,
                        'contentType'=> $value->contentType,
                        'packageName'=> $value->packageName,
                        'pageId'=> $value->pageId,
                        'sortOrder'=> $value->sortOrder,
                        'publishSource' => $value->publishSource
                    ];
            }
            $pageContentJsonData = json_encode($pageContentJsonArr, JSON_PRETTY_PRINT);
            
            foreach($slectedTables as $table){
                  $sql = "SHOW CREATE TABLE $table";
                  $stmt = $this->sqlConnect->query($sql);
                  $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                  $createStmt = $result['Create Table'];
                  $createStmtModified = str_replace("CREATE TABLE","CREATE TABLE IF NOT EXISTS", $createStmt);
                  $tableJsonArr[] = ['tableData'=>$createStmtModified];
            }
            
            $tableStructureJsonData = json_encode($tableJsonArr, JSON_PRETTY_PRINT);
            
             $path="packages/".$packageType.'s/'.$packageName.'/Export/';
             LtFile::path($path)->writeFile('content.json', $contentJsonData);
             LtFile::path($path)->writeFile('menu.json', $menuJsonData);
             LtFile::path($path)->writeFile('table.json', $tableStructureJsonData);
             LtFile::path($path)->writeFile('page.json', $pageJsonData);
             LtFile::path($path)->writeFile('pageContent.json', $pageContentJsonData);
            
              $dstPath="storage/tmp/export/";   
              $pathToPackage="packages/".$packageType.'s/'.$packageName;
             
              $res = LtFile::path($pathToPackage)->copy($dstPath);
               
              if(!$useSourceCode){
                 $dstPath="storage/tmp/export/".$packageName;
                 $result = LtFile::path($dstPath)->unlink('Views','no');
                 $result = LtFile::path($dstPath)->unlink('Services','no');
                 $result = LtFile::path($dstPath)->unlink('Models','no');
                 $result = LtFile::path($dstPath)->unlink('Controllers','no');
                 $result = LtFile::path($dstPath)->unlink('Contents','no');
              }
            
             // create Zip file
             $path="storage/tmp/export/"; 
           
             $result = LtFile::path($path)->download($packageName);
             $result = LtFile::path($path)->unlink($packageName); 
            return LtResponse::json('Package exported successfully.', 3310, 200);
            // return json_encode($result);
            
            
        }
        
        public function zipFolder($folderPath, $zipFilePath)
            {
                if (!extension_loaded('zip')) {
                    return [
                        "status" => false,
                        "message" => "PHP ZipArchive extension is not enabled"
                    ];
                }
            
                if (!is_dir($folderPath)) {
                    return [
                        "status" => false,
                        "message" => "Folder does not exist"
                    ];
                }
            
                $zip = new ZipArchive();
            
                if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    return json_encode([
                        "responseCategory" => 100,
                        "responseResult" => "Could not create zip file"
                    ]);
                }
            
                $folderPath = realpath($folderPath);
            
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
            
                foreach ($files as $file) {
                    $filePath = $file->getRealPath();
            
                    // Path inside zip
                    $relativePath = substr($filePath, strlen($folderPath) + 1);
            
                    if ($file->isDir()) {
                        $zip->addEmptyDir($relativePath);
                    } else {
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            
                $zip->close();
                
                if (!file_exists($zipFilePath)) {
                    header("Content-Type: application/json");
                
                    return json_encode([
                        "responseCategory" => 100,
                        "responseResult" => "Zip file was not created"
                    ]);
                
                    exit;
                }
                
                // Clean previous output to prevent corrupt zip
                if (ob_get_length()) {
                    ob_clean();
                }
                
                header("Content-Type: application/zip");
                header("Content-Disposition: attachment; filename=\"" . basename($zipName) . "\"");
                header("Content-Length: " . filesize($zipFilePath));
                header("Cache-Control: no-cache");
                header("Pragma: no-cache");
                header("Expires: 0");
                
                readfile($zipFilePath);
                
                // Delete temporary zip after download
                unlink($zipFilePath);
                
                exit;
            
                
            }
        
        public function importPackage(){
            $pageModel = new TbPage();
            $menuModel = new TbMenu();
            $contentModel = new TbContent();
            $pageContentModel = new TbPageContent();
            $tmp_name = $_FILES['file']['tmp_name'];
            $batchSize = 100;
            $pageTypeThemeArr = [];
            // create Zip file
             $path="storage/tmp/"; 
             
             $result = LtFile::path($path)->unlink('import', 'no');   
             $path="storage/tmp/import/";
             
             if(!class_exists('ZipArchive')){
                  return LtResponse::json('ZipArchive is not Activated on your PhP Configuration. You need to Activate ZipArchive! on your PhP Configuration!!!  RETRY', 1999, 100);
              }
             
             $zip = new ZipArchive;
             $res = $zip->open($tmp_name); 
             
            if ($res === TRUE) { 
            
             $zip->extractTo($path);
             $zip->close();
                        
            }
            $packageDir = scandir($path);
            $packageDir = array_slice($packageDir, 2)[0];
            $pathToJson = $path.''.$packageDir.'/package.json';
            
            
            
            // read package.json file 
            $packageDetails = json_decode(file_get_contents($pathToJson), true);
            

            $this->packageModel->processRequest($packageDetails);
            $packageName =  $this->packageModel->packageName;
            $packageType =  $this->packageModel->packageType;
            $exist = $this->packageModel->select()->where('packageName', '=', $packageName)->get();
            
            if(count($exist) > 0){
                $this->packageModel->update('packageName', '=', $packageName);
            }else{
                $this->packageModel->insert();
            }
            
            
            $path = $path.''.$packageName.'/';  // import directory path
            
            $dstPath="packages/".$packageType.'s/'.$packageName.'/';
              //return LtResponse::json($path.'Package installation completed', 1715, 200, $packageDetails);
            
            $result = LtFile::path($path)->moveFolder($path,$dstPath);
            $packagePath ="packages/".$packageType.'s/'.$packageName.'/Export/';
            // // pages
            $pages = json_decode(file_get_contents($packagePath.'page.json'), true);
            
            
            
                        //get content with page_type theme 
            foreach($pages as $p){
                $p = (object)$p;
                if($p->pageType == 'theme'){
                    $pageTypeThemeArr[] = $p->ltId;
                }
            }
            
            $pageRecordArr = $pageModel->select()->where('packageName', '=', $packageName)->get();
            if(count($pageRecordArr) > 0){
                foreach($pages as $key => $page){
                    $pageModel->processRequest($page);
                    $ltId = $pageModel->ltId;
                    $pageRecord = $pageModel->select()->where('ltId', '=', $ltId)->get();
                    if(count($pageRecord) > 0){
                        $pageModel->update('ltId', '=', $ltId);
                    }else{
                        $pageModel->insert();
                    }
                }
            }else{

                $totalPageSize = is_array($pages) ? count($pages) : 0;
                $insertPageArr = is_array($pages) ? array_values($pages) : [];
                
                for($i = 0; $i < $totalPageSize; $i += $batchSize){
                    $batch = array_slice($insertPageArr, $i, $batchSize);
                    $placeholder = [];
                    $values = [];
                    
                    foreach($batch as $index => $row){
                            $placeholder[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $values[] = $row['ltId'];
                            $values[] = $row['packageName'];
                            $values[] = $row['pageName'];
                            $values[] = $row['pageType'];
                            $values[] = $row['routeContent'];
                            $values[] = $row['routeFileName'];
                            $values[] = $row['routeHandler'];
                            $values[] = $row['routeMethod'];
                            $values[] = $row['routePath'];
                            $values[] = $row['routePayload'];
                            $values[] = $row['routeResponse'];
                            $values[] = $row['routeUpdateStatus'];
                            $values[] = $row['apiToAllowRouters'];
                            $values[] = $row['description'];
                            $values[] = $this->ltNow;
                            $values[] = $this->ltNow;
                            $values[] = LtSession::get('ltUid');
                         
                        }
                        
                        $sql = "INSERT INTO tb_page (lt_id, package_name, page_name, page_type, route_content, route_file_name, route_handler, route_method, route_path, route_payload, route_response, route_update_status, api_to_allow_routers, description, created_at, updated_at, updated_by ) VALUES " . implode(", ", $placeholder);
                        $stmt = $this->sqlConnect->prepare($sql);
                        $stmt->execute($values);
                    
                }
                
            }
            
            // // menus
            $menus = json_decode(file_get_contents($packagePath.'menu.json'), true);
            $menuRecordArr = $menuModel->select()->where('packageName', '=', $packageName)->get();
            if(count($menuRecordArr) > 0){
                foreach($menus as $menu){
                    $menuModel->processRequest($menu);
                    $ltId = $menuModel->ltId;
                    $menuRecord = $menuModel->select()->where('ltId', '=', $ltId)->get();
                    if(count($menuRecord) > 0){
                        $menuModel->update('ltId', '=', $ltId);
                    }else{
                        $menuModel->insert();
                    }
                }
            }else{
                
                $totalMenuSize = is_array($menus) ? count($menus) : 0;
                $insertMenuArr = is_array($menus) ? array_values($menus) : [];

                
                for($i = 0; $i < $totalMenuSize; $i += $batchSize){
                    $batch = array_slice($insertMenuArr, $i, $batchSize);
                    $placeholder = [];
                    $values = [];
                    
                    foreach($batch as $index => $row){
                            $placeholder[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $values[] = $row['ltId'];
                            $values[] = $row['packageName'];
                            $values[] = $row['menuLevel'];
                            $values[] = $row['menuName'];
                            $values[] = $row['contentName'];
                            $values[] = $row['parentMenuId'];
                            $values[] = $row['routePath'];
                            $values[] = $row['slug'];
                            $values[] = $row['target'];
                            $values[] = $row['sortOrder'];
                            $values[] = $this->ltNow;
                            $values[] = $this->ltNow;
                            $values[] = LtSession::get('ltUid');
                         
                        }
                        
                        $sql = "INSERT INTO tb_menu (lt_id, package_name, menu_level, menu_name, content_name, parent_menu_id, route_path, slug, target, sort_order, created_at, updated_at, updated_by ) VALUES " . implode(", ", $placeholder);
                        $stmt = $this->sqlConnect->prepare($sql);
                        $stmt->execute($values);
                    
                }
                
            }
            

            // contents
            $contents = json_decode(file_get_contents($packagePath.'content.json'), true);
            
            
            $contentRecordArr = $contentModel->select()->where('packageName', '=', $packageName)->get();
            if(count($contentRecordArr) > 0){
                foreach($contents as $key => $content){
                    $contentModel->processRequest($content);
                    $ltId = $contentModel->ltId;
                    $contentRecord = $contentModel->select()->where('ltId', '=', $ltId)->get();
                    if(count($contentRecord) > 0){
                        $contentModel->update('ltId', '=', $ltId);
                    }else{
                        $contentModel->insert();
                    }
                }
            }else{
 

                $totalContentSize = is_array($contents) ? count($contents) : 0;
                $insertContentArr = is_array($contents) ? array_values($contents) : [];
                
                for($i = 0; $i < $totalContentSize; $i += $batchSize){
                    $batch = array_slice($insertContentArr, $i, $batchSize);
                    $placeholder = [];
                    $values = [];
                    
                    foreach($batch as $index => $row){
                            $placeholder[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $values[] = $row['ltId'];
                            $values[] = $row['packageName'];
                            $values[] = $row['pageType'];
                            $values[] = $row['useLiferazor'];
                            $values[] = $row['storeType'];
                            $values[] = $row['mvcType'];
                            $values[] = $row['isThemeDefault'];
                            $values[] = $row['contentType'];
                            $values[] = $row['contentSource'];
                            $values[] = $row['contentName'];
                            $values[] = $row['contentCategory'];
                            $values[] = $row['contentBody'];
                            $values[] = $row['ccurrent'];
                            $values[] = $row['ccname'];
                            $values[] = $row['description'];
                            $values[] = $row['sortOrder'];
                            $values[] = $this->ltNow;
                            $values[] = $this->ltNow;
                            $values[] = LtSession::get('ltUid');
                         
                        }
                        
                        $sql = "INSERT INTO tb_content (lt_id, package_name, page_type, use_liferazor, store_type, mvc_type, is_theme_default, content_type, content_source, content_name, content_category, content_body, ccurrent, ccname, description, sort_order, created_at, updated_at, updated_by) VALUES " . implode(", ", $placeholder);
                        $stmt = $this->sqlConnect->prepare($sql);
                        $stmt->execute($values);
                    
                }
                
            }
            
            
            
           
            // page contents
            $pageContents = json_decode(file_get_contents($packagePath.'pageContent.json'), true);
            
            // fetching dafault theme region for page content publishing 
            $stmtSelectContent = $this->sqlConnect->prepare("SELECT lt_id, sort_order, package_name FROM tb_content WHERE package_name = (
                SELECT package_name FROM tb_package WHERE package_type = 'theme' AND is_theme_default = 1
                ) AND is_theme_default = :isThemeDefault");
            $stmtSelectContent->execute([
                    ':isThemeDefault' => '1'
                ]);
            $themeContent = $stmtSelectContent->fetchAll(\PDO::FETCH_OBJ);
            
            $pageContentRecordArr = $pageContentModel->select()->where('packageName', '=', $packageName)->get();
            $pageRegion = [];
            $userId = LtSession::get('ltUid');
            
            if(count($pageContentRecordArr) > 0){   
                foreach($pageContents as $key => $pageContent){
                    $pageContentModel->processRequest($pageContent);
                    $ltId = $pageContentModel->ltId;
                    $pageContentRecord = $pageContentModel->select()->where('ltId', '=', $ltId)->get();
                    if(count($pageContentRecord) > 0){
                        $pageContentModel->update('ltId', '=', $ltId);
                      
                    }else{
                        /// page content insertion 
                        // type content
                        $pageContentModel->updatedBy = $userId;
                        $pageContentModel->insert();
                        // type region
                        if(in_array($pageContentModel->pageId, $pageTypeThemeArr)){
                            foreach($themeContent as $content){
                                $pageRegion[] = [
                                        'pageId' => $pageContentModel->pageId,
                                        'contentId' => $content->lt_id,
                                        'contentType' => 'theme',  //$pageContentModel->contentType,
                                        'packageName' => $content->package_name,
                                        'sortOrder' => $content->sort_order,
                                        'publishSource' => 'region',
                                        'createdAt' => $this->ltNow,
                                        'updatedAt' => $this->ltNow,
                                        'ltId' => ltId()
                                    ];
                            }
                        }
                        
                    }
                }
            }else{

                    // insert type content
                    
                    $this->batchInsertPageContent($pageContents, $batchSize, $userId);
                    
                    //insert type region
                    foreach($pageContents as $page){
                      $page = (object)$page;
                        if(in_array($page->pageId, $pageTypeThemeArr)){
                            foreach($themeContent as $content){
                                $pageRegion[] = [
                                        'pageId' => $page->pageId,
                                        'contentId' => $content->lt_id,
                                        'contentType' => 'theme', //$page->contentType
                                        'packageName' => $content->package_name,
                                        'sortOrder' => $content->sort_order,
                                        'publishSource' => 'region',
                                        'createdAt' => $this->ltNow,
                                        'updatedAt' => $this->ltNow,
                                        'ltId' => ltId()
                                    
                                    ];
                            }
                        }
                        
                    }

            }
            
            if(!empty($pageRegion)){
                 $this->batchInsertPageContent($pageRegion, $batchSize, $userId);
             };
             
            // //Tables
            
            $tables = json_decode(file_get_contents($packagePath.'table.json'), true);
            
            foreach($tables as $table){
                $sql = $table['tableData'];
                try {
                    $this->sqlConnect->exec($sql);
                    $succes = "Table created successfully.<br>";
                } catch (PDOException $e) {
                    $failed =  "Error creating table: " . $e->getMessage() . "<br>";
                }
            }

            return LtResponse::json('Package installation completed successfully.', 3311, 200, $packageDetails);
        }
        

        public function batchInsertPageContent(array $pageContents, int $batchSize, $userId){
                if (empty($pageContents)) {
                    return;
                }
            
                $insertData = array_values($pageContents);
                $totalSize  = count($insertData);
            
                for ($i = 0; $i < $totalSize; $i += $batchSize) {
            
                    $batch       = array_slice($insertData, $i, $batchSize);
                    $placeholders = [];
                    $values       = [];
            
                    foreach ($batch as $row) {
            
                        $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
                        $values[] = $row['ltId'];
                        $values[] = $row['contentId'];
                        $values[] = $row['contentType'];
                        $values[] = $row['packageName'];
                        $values[] = $row['pageId'];
                        $values[] = $row['sortOrder'];
                        $values[] = $row['publishSource'];
                        $values[] = $row['createdAt'];
                        $values[] = $row['updatedAt'];
                        $values[] = $userId;
                    }
            
                    $sql ="
                        INSERT INTO tb_page_content 
                        (lt_id, content_id, content_type, package_name, page_id, sort_order, publish_source, created_at, updated_at, updated_by)
                        VALUES " . implode(', ', $placeholders);
            
                    $stmt = $this->sqlConnect->prepare($sql);
            
                    if (!$stmt->execute($values)) {
                        throw new Exception('Batch insert failed');
                    }
                }
        }
        
        // public function importPackage(){
        //     $pageModel = new TbPage();
        //     $menuModel = new TbMenu();
        //     $contentModel = new TbContent();
        //     $pageContentModel = new TbPageContent();
        //     $tmp_name = $_FILES['file']['tmp_name'];
        //     $batchSize = 100;
        //     $pageTypeThemeArr = [];
        //     // create Zip file
        //      $path="storage/tmp/"; 
             
        //      $result = LtFile::path($path)->unlink('import', 'no');   
        //      $path="storage/tmp/import/";
             
        //      if(!class_exists('ZipArchive')){
        //           return LtResponse::json('ZipArchive is not Activated on your PhP Configuration. You need to Activate ZipArchive! on your PhP Configuration!!!  RETRY', 1999, 100);
        //       }
             
        //      $zip = new ZipArchive;
        //      $res = $zip->open($tmp_name); 
             
        //     if ($res === TRUE) { 
            
        //      $zip->extractTo($path);
        //      $zip->close();
                        
        //     }
        //     $packageDir = scandir($path);
        //     $packageDir = array_slice($packageDir, 2)[0];
        //     $pathToJson = $path.''.$packageDir.'/package.json';
            
            
            
        //     // read package.json file 
        //     $packageDetails = json_decode(file_get_contents($pathToJson), true);

        //     $this->packageModel->processRequest($packageDetails);
        //     $packageName =  $this->packageModel->packageName;
        //     $packageType =  $this->packageModel->packageType;
        //     $exist = $this->packageModel->select()->where('packageName', '=', $packageName)->get();
            
        //     if(count($exist) > 0){
        //         $this->packageModel->update('packageName', '=', $packageName);
        //     }else{
        //         $this->packageModel->insert();
        //     }
            
            
        //     $path = $path.''.$packageName.'/';  // import directory path
            
        //     $dstPath="packages/".$packageType.'s/'.$packageName.'/';
        //       //return LtResponse::json($path.'Package installation completed', 1715, 200, $packageDetails);
            
        //     $result = LtFile::path($path)->moveFolder($path,$dstPath);
        //     $packagePath ="packages/".$packageType.'s/'.$packageName.'/Export/';
        //     // // pages
        //     $pages = json_decode(file_get_contents($packagePath.'page.json'), true);
            
        //                 //get content with page_type theme 
        //     foreach($pages as $p){
        //         $p = (object)$p;
        //         if($p->pageType == 'theme'){
        //             $pageTypeThemeArr[] = $p->ltId;
        //         }
        //     }
            
        //     $pageRecordArr = $pageModel->select()->where('packageName', '=', $packageName)->get();
        //     if(count($pageRecordArr) > 0){
        //         foreach($pages as $key => $page){
        //             $pageModel->processRequest($page);
        //             $ltId = $pageModel->ltId;
        //             $pageRecord = $pageModel->select()->where('ltId', '=', $ltId)->get();
        //             if(count($pageRecord) > 0){
        //                 $pageModel->update('ltId', '=', $ltId);
        //             }else{
        //                 $pageModel->insert();
        //             }
        //         }
        //     }else{

        //         $totalPageSize = is_array($pages) ? count($pages) : 0;
        //         $insertPageArr = is_array($pages) ? array_values($pages) : [];
                
        //         for($i = 0; $i < $totalPageSize; $i += $batchSize){
        //             $batch = array_slice($insertPageArr, $i, $batchSize);
        //             $placeholder = [];
        //             $values = [];
                    
        //             foreach($batch as $index => $row){
        //                     $placeholder[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        //                     $values[] = $row['ltId'];
        //                     $values[] = $row['packageName'];
        //                     $values[] = $row['pageName'];
        //                     $values[] = $row['pageType'];
        //                     $values[] = $row['routeContent'];
        //                     $values[] = $row['routeFileName'];
        //                     $values[] = $row['routeHandler'];
        //                     $values[] = $row['routeMethod'];
        //                     $values[] = $row['routePath'];
        //                     $values[] = $row['routePayload'];
        //                     $values[] = $row['routeResponse'];
        //                     $values[] = $row['routeUpdateStatus'];
        //                     $values[] = $row['apiToAllowRouters'];
        //                     $values[] = $row['description'];
        //                     $values[] = $this->ltNow;
        //                     $values[] = $this->ltNow;
        //                     $values[] = LtSession::get('ltUid');
                         
        //                 }
                        
        //                 $sql = "INSERT INTO tb_page (lt_id, package_name, page_name, page_type, route_content, route_file_name, route_handler, route_method, route_path, route_payload, route_response, route_update_status, api_to_allow_routers, description, created_at, updated_at, updated_by ) VALUES " . implode(", ", $placeholder);
        //                 $stmt = $this->sqlConnect->prepare($sql);
        //                 $stmt->execute($values);
                    
        //         }
                
        //     }
            
        //     // // menus
        //     $menus = json_decode(file_get_contents($packagePath.'menu.json'), true);
        //     $menuRecordArr = $menuModel->select()->where('packageName', '=', $packageName)->get();
        //     if(count($menuRecordArr) > 0){
        //         foreach($menus as $menu){
        //             $menuModel->processRequest($menu);
        //             $ltId = $menuModel->ltId;
        //             $menuRecord = $menuModel->select()->where('ltId', '=', $ltId)->get();
        //             if(count($menuRecord) > 0){
        //                 $menuModel->update('ltId', '=', $ltId);
        //             }else{
        //                 $menuModel->insert();
        //             }
        //         }
        //     }else{
                
        //         $totalMenuSize = is_array($menus) ? count($menus) : 0;
        //         $insertMenuArr = is_array($menus) ? array_values($menus) : [];

                
        //         for($i = 0; $i < $totalMenuSize; $i += $batchSize){
        //             $batch = array_slice($insertMenuArr, $i, $batchSize);
        //             $placeholder = [];
        //             $values = [];
                    
        //             foreach($batch as $index => $row){
        //                     $placeholder[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        //                     $values[] = $row['ltId'];
        //                     $values[] = $row['packageName'];
        //                     $values[] = $row['menuLevel'];
        //                     $values[] = $row['menuName'];
        //                     $values[] = $row['contentName'];
        //                     $values[] = $row['parentMenuId'];
        //                     $values[] = $row['routePath'];
        //                     $values[] = $row['slug'];
        //                     $values[] = $row['target'];
        //                     $values[] = $row['sortOrder'];
        //                     $values[] = $this->ltNow;
        //                     $values[] = $this->ltNow;
        //                     $values[] = LtSession::get('ltUid');
                         
        //                 }
                        
        //                 $sql = "INSERT INTO tb_menu (lt_id, package_name, menu_level, menu_name, content_name, parent_menu_id, route_path, slug, target, sort_order, created_at, updated_at, updated_by ) VALUES " . implode(", ", $placeholder);
        //                 $stmt = $this->sqlConnect->prepare($sql);
        //                 $stmt->execute($values);
                    
        //         }
                
        //     }
            

        //     // contents
        //     $contents = json_decode(file_get_contents($packagePath.'content.json'), true);
            
            
        //     $contentRecordArr = $contentModel->select()->where('packageName', '=', $packageName)->get();
        //     if(count($contentRecordArr) > 0){
        //         foreach($contents as $key => $content){
        //             $contentModel->processRequest($content);
        //             $ltId = $contentModel->ltId;
        //             $contentRecord = $contentModel->select()->where('ltId', '=', $ltId)->get();
        //             if(count($contentRecord) > 0){
        //                 $contentModel->update('ltId', '=', $ltId);
        //             }else{
        //                 $contentModel->insert();
        //             }
        //         }
        //     }else{
 

        //         $totalContentSize = is_array($contents) ? count($contents) : 0;
        //         $insertContentArr = is_array($contents) ? array_values($contents) : [];
                
        //         for($i = 0; $i < $totalContentSize; $i += $batchSize){
        //             $batch = array_slice($insertContentArr, $i, $batchSize);
        //             $placeholder = [];
        //             $values = [];
                    
        //             foreach($batch as $index => $row){
        //                     $placeholder[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        //                     $values[] = $row['ltId'];
        //                     $values[] = $row['packageName'];
        //                     $values[] = $row['pageType'];
        //                     $values[] = $row['useLiferazor'];
        //                     $values[] = $row['storeType'];
        //                     $values[] = $row['mvcType'];
        //                     $values[] = $row['isThemeDefault'];
        //                     $values[] = $row['contentType'];
        //                     $values[] = $row['contentSource'];
        //                     $values[] = $row['contentName'];
        //                     $values[] = $row['contentCategory'];
        //                     $values[] = $row['contentBody'];
        //                     $values[] = $row['ccurrent'];
        //                     $values[] = $row['ccname'];
        //                     $values[] = $row['description'];
        //                     $values[] = $row['sortOrder'];
        //                     $values[] = $this->ltNow;
        //                     $values[] = $this->ltNow;
        //                     $values[] = LtSession::get('ltUid');
                         
        //                 }
                        
        //                 $sql = "INSERT INTO tb_content (lt_id, package_name, page_type, use_liferazor, store_type, mvc_type, is_theme_default, content_type, content_source, content_name, content_category, content_body, ccurrent, ccname, description, sort_order, created_at, updated_at, updated_by) VALUES " . implode(", ", $placeholder);
        //                 $stmt = $this->sqlConnect->prepare($sql);
        //                 $stmt->execute($values);
                    
        //         }
                
        //     }
            
            
            
           
        //     // page contents
        //     $pageContents = json_decode(file_get_contents($packagePath.'pageContent.json'), true);
            
        //     // fetching dafault theme region for page content publishing 
        //     $stmtSelectContent = $this->sqlConnect->prepare("SELECT lt_id, sort_order, package_name FROM tb_content WHERE package_name = (
        //         SELECT package_name FROM tb_package WHERE package_type = 'theme' AND is_theme_default = 1
        //         ) AND is_theme_default = :isThemeDefault");
        //     $stmtSelectContent->execute([
        //             ':isThemeDefault' => '1'
        //         ]);
        //     $themeContent = $stmtSelectContent->fetchAll(\PDO::FETCH_OBJ);
            
        //     $pageContentRecordArr = $pageContentModel->select()->where('packageName', '=', $packageName)->get();
        //     $pageRegion = [];
        //     $userId = LtSession::get('ltUid');
            
        //     if(count($pageContentRecordArr) > 0){   
        //         foreach($pageContents as $key => $pageContent){
        //             $pageContentModel->processRequest($pageContent);
        //             $ltId = $pageContentModel->ltId;
        //             $pageContentRecord = $pageContentModel->select()->where('ltId', '=', $ltId)->get();
        //             if(count($pageContentRecord) > 0){
        //                 $pageContentModel->update('ltId', '=', $ltId);
                      
        //             }else{
        //                 /// page content insertion 
        //                 // type content
        //                 $pageContentModel->updatedBy = $userId;
        //                 $pageContentModel->insert();
        //                 // type region
        //                 if(in_array($pageContentModel->pageId, $pageTypeThemeArr)){
        //                     foreach($themeContent as $content){
        //                         $pageRegion[] = [
        //                                 'pageId' => $pageContentModel->pageId,
        //                                 'contentId' => $content->lt_id,
        //                                 'contentType' => 'theme',  //$pageContentModel->contentType,
        //                                 'packageName' => $content->package_name,
        //                                 'sortOrder' => $content->sort_order,
        //                                 'publishSource' => 'region',
        //                                 'createdAt' => $this->ltNow,
        //                                 'updatedAt' => $this->ltNow,
        //                                 'ltId' => ltId()
        //                             ];
        //                     }
        //                 }
                        
        //             }
        //         }
        //     }else{

        //             // insert type content
                    
        //             $this->batchInsertPageContent($pageContents, $batchSize, $userId);
                    
        //             //insert type region
        //             foreach($pageContents as $page){
        //               $page = (object)$page;
        //                 if(in_array($page->pageId, $pageTypeThemeArr)){
        //                     foreach($themeContent as $content){
        //                         $pageRegion[] = [
        //                                 'pageId' => $page->pageId,
        //                                 'contentId' => $content->lt_id,
        //                                 'contentType' => 'theme', //$page->contentType
        //                                 'packageName' => $content->package_name,
        //                                 'sortOrder' => $content->sort_order,
        //                                 'publishSource' => 'region',
        //                                 'createdAt' => $this->ltNow,
        //                                 'updatedAt' => $this->ltNow,
        //                                 'ltId' => ltId()
                                    
        //                             ];
        //                     }
        //                 }
                        
        //             }

        //     }
            
        //     if(!empty($pageRegion)){
        //          $this->batchInsertPageContent($pageRegion, $batchSize, $userId);
        //      };
             
        //     // //Tables
            
        //     $tables = json_decode(file_get_contents($packagePath.'table.json'), true);
            
        //     foreach($tables as $table){
        //         $sql = $table['tableData'];
        //         try {
        //             $this->sqlConnect->exec($sql);
        //             $succes = "Table created successfully.<br>";
        //         } catch (PDOException $e) {
        //             $failed =  "Error creating table: " . $e->getMessage() . "<br>";
        //         }
        //     }

        //     return LtResponse::json('Package installation completed successfully.', 3311, 200, $packageDetails);
        // }
        

        // public function batchInsertPageContent(array $pageContents, int $batchSize, $userId){
        //         if (empty($pageContents)) {
        //             return;
        //         }
            
        //         $insertData = array_values($pageContents);
        //         $totalSize  = count($insertData);
            
        //         for ($i = 0; $i < $totalSize; $i += $batchSize) {
            
        //             $batch       = array_slice($insertData, $i, $batchSize);
        //             $placeholders = [];
        //             $values       = [];
            
        //             foreach ($batch as $row) {
            
        //                 $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
        //                 $values[] = $row['ltId'];
        //                 $values[] = $row['contentId'];
        //                 $values[] = $row['contentType'];
        //                 $values[] = $row['packageName'];
        //                 $values[] = $row['pageId'];
        //                 $values[] = $row['sortOrder'];
        //                 $values[] = $row['publishSource'];
        //                 $values[] = $row['createdAt'];
        //                 $values[] = $row['updatedAt'];
        //                 $values[] = $userId;
        //             }
            
        //             $sql ="
        //                 INSERT INTO tb_page_content 
        //                 (lt_id, content_id, content_type, package_name, page_id, sort_order, publish_source, created_at, updated_at, updated_by)
        //                 VALUES " . implode(', ', $placeholders);
            
        //             $stmt = $this->sqlConnect->prepare($sql);
            
        //             if (!$stmt->execute($values)) {
        //                 throw new Exception('Batch insert failed');
        //             }
        //         }
        // }

        
        
        
        public function togglePublish($model){
            // $packageModel->processRequest(['isPublished', 'ltId', 'packageName', 'packageType']);  
            $ltId = $this->request->ltId;
            $isPublished = $this->request->isPublished;
            $packageName =  $this->request->packageName;
            $packageType =  $this->request->packageType;
            $model->updatedBy = LtSession::get('ltUid');
            
                $model->isPublished = $isPublished;
                $model->update('ltId', '=', $ltId); 
            /// togggle-publish module content
                $contentModel = new TbContentService(); 
                $publishContent = $contentModel->togglePublished($packageName, $isPublished);
                return $model->responseJson();
        }
        
        
        public function isDefault($model){
                    // $packageModel->processRequest(['isPublished', 'ltId', 'packageName', 'packageType']);  
            $ltId = $this->request->ltId;
            $isDefault = $this->request->isThemeDefault;
            $packageName =  $this->request->packageName;
            $packageType =  $this->request->packageType;
            $model->updatedBy = LtSession::get('ltUid');
            $insertDataArr = [];
            $batchSize = 100;
            $totalRows = '';
            
                
            try{
                $this->sqlConnect->beginTransaction();
                
                 if($isDefault == '1'){
                    /// to check if theme is publish before enabling for default
                    $stmtSelectPackage = $this->sqlConnect->prepare("SELECT is_published FROM tb_package WHERE package_name = :packageName");
                    $stmtSelectPackage->execute([
                            ':packageName' => $packageName,
                            ]);
                    $packageRecord = $stmtSelectPackage->fetch(\PDO::FETCH_OBJ);
                    
                    if($packageRecord->is_published == '0'){
                        $this->sqlConnect->rollBack();
                        return LtResponse::json("The selected theme cannot be set as default because it is unpublished.", '3312', '100');
                    }

                  // 1 updating all table package where package type is theme to is_theme_default 0
                $stmt = $this->sqlConnect->prepare("UPDATE tb_package SET is_theme_default = :isDefault WHERE package_type = :packageType")
                        ->execute([
                            ':isDefault' => 0,
                            ':packageType' => $packageType,
                            ]);
                            
                // 3 delete all records from page_content where content type is theme
                
                $stmtDelete = $this->sqlConnect->prepare("DELETE FROM tb_page_content WHERE content_type= :contentType AND publish_source = :publishSource")
                                ->execute([
                                    ':contentType' => $packageType,
                                    ':publishSource' => 'region'
                                ]);
                // $stmtDelete = $this->sqlConnect->prepare("DELETE FROM tb_page_content")
                //                 ->execute();

                    
                // 2 updating package id 
                    $stmtUpdate = $this->sqlConnect->prepare("UPDATE tb_package SET is_theme_default = :isDefault WHERE lt_id = :ltId")
                        ->execute([
                            ':isDefault' => $isDefault,
                            ':ltId' => $ltId,
                            ]);
                    
                    // 4 select lt_id from page table where page_type ="theme"
                    $stmtSelect = $this->sqlConnect->prepare("SELECT lt_id FROM tb_page WHERE page_type = :packageType");
                    $stmtSelect->execute([':packageType' => $packageType]);
                    $pagesRecord = $stmtSelect->fetchAll(\PDO::FETCH_OBJ);
                    
                    // // 5 select lt_id from content table where packaganame = current theme and istheme_default =1

                    // $stmtSelectContent2 = $this->sqlConnect->prepare("SELECT pg.*, content_name, page_name FROM tb_page_content AS pg LEFT JOIN tb_content AS tc ON tc.lt_id = pg.content_id LEFT JOIN tb_page AS p ON p.lt_id = pg.page_id");
                    // $stmtSelectContent2->execute();
                    // $contentRecord2 = $stmtSelectContent2->fetchAll(PDO::FETCH_OBJ);
                    
                    $stmtSelectContent = $this->sqlConnect->prepare("SELECT lt_id, sort_order FROM tb_content WHERE package_name = :packageName AND is_theme_default = :isThemeDefault");
                    $stmtSelectContent->execute([
                            ':packageName' => $packageName,
                            ':isThemeDefault' => '1'
                        ]);
                    $contentRecord = $stmtSelectContent->fetchAll(\PDO::FETCH_OBJ);
                    
                    foreach($pagesRecord as $page){
                        $pageId = $page->lt_id;
                        
                        foreach($contentRecord as $content){
                            $insertDataArr[] = [
                                    'pageId' => $pageId,
                                    'contentId' => $content->lt_id,
                                    'contentType' => $packageType,
                                    'packageName' => $packageName,
                                    'sortOrder' => $content->sort_order,
                                    'ltId' => ltId()
                                
                                ];
                        }
                    }
                    $totalRows = count($insertDataArr);
                    $insertDataArr = array_values($insertDataArr);
                    
                    for($i = 0; $i < $totalRows; $i += $batchSize){
                        $batch = array_slice($insertDataArr, $i, $batchSize);
                        $placeholder = [];
                        $values = [];
                        
                        foreach($batch as $index => $row){
                            $placeholder[] = "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $values[] = $row['pageId'];
                            $values[] = $row['contentId'];
                            $values[] = $row['contentType'];
                            $values[] = $row['packageName'];
                            $values[] = $row['ltId'];
                            $values[] = $this->ltNow;
                            $values[] = $this->ltNow;
                            $values[] = LtSession::get('ltUid');
                            $values[] = "region";
                            $values[] = $row['sortOrder'];
                         
                        }
                        
                        $sql = "INSERT INTO tb_page_content (page_id, content_id, content_type, package_name, lt_id, created_at, updated_at, updated_by, publish_source, sort_order) VALUES " . implode(", ", $placeholder) . " ON DUPLICATE KEY UPDATE
                                    updated_at = VALUES(updated_at),
                                    updated_by = VALUES(updated_by),
                                    content_type = VALUES(content_type),
                                    publish_source = VALUES(publish_source),
                                    sort_order = VALUES(sort_order)";
                        $stmt = $this->sqlConnect->prepare($sql);
                        $stmt->execute($values);

                    }

                }
                $this->sqlConnect->commit();
                return LtResponse::json("Package theme set to default successfully.", '3313', '200', ['isThemeDefault' => $isDefault]);
                // return LtResponse::json("success", '201', '200', $contentRecord2);

            }catch (PDOException $e) {
                $this->sqlConnect->rollBack();
                return LtResponse::error("failed to set theme to default: " . $e->getMessage(), '3314', '100');
            }
            
        }
        
        public function dashboardAnalysis(){
            $packages = $this->packageModel->select()->get();
            
            $theme = $module = [];
            foreach($packages as $package){
                if($package->packageType === 'theme'){
                    $theme[] = $package;
                }
                if($package->packageType === 'module'){
                    $module[] = $package;
                }
            }
            $result = ['theme' => count($theme), 'module' => count($module), 'totalPackages' => count($packages)];
            return $result;
        }
     
        
}
