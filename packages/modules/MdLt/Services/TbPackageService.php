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
            $dependsOn = $this->request->dependsOn;
   
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
            $model->dependsOn = $dependsOn;
            $model->updatedBy = $this->userId;
            $model->updatedAt = $this->ltNow;
            $model->createdAt = $this->ltNow;
            
            $model->insert();
            
            $data = [
                        "ltId" => $packageId,
                        "packageName" => $packageName,
                        "packageType" => $packageType,
                        "version"     => $model->version,
                        "dependsOn"     => json_decode($dependsOn, true),
                        "isPublished"     => $model->isPublished ?? '0',
                        "readme"     => $model->readme,
                        "description"     => $model->description,
                        "languageUsed" => $model->languageUsed,
                        "ltVersion" => $model->ltVersion
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
                    LtFile::path($path)->createFolder('Storage');
                    LtFile::path($path)->createFile('LICENSE', $licenceContent);
                    LtFile::path($path)->createFile('package.json', $jsonData);
                    LtFile::path($path)->createFile('README.md');
                    $path="packages/".$packageType.'s/'.$packageName.'/Export';
                    LtFile::path($path)->createFile('page.json');
                    LtFile::path($path)->createFile('content.json');
                    LtFile::path($path)->createFile('menu.json');
                    LtFile::path($path)->createFile('table.json');
                    LtFile::path($path)->createFile('pageContent.json');
                    $storagePath ="packages/".$packageType.'s/'.$packageName.'/Storage';
                    LtFile::path($storagePath)->createFolder('Layouts');
                    $storageLayoutPath =$storagePath .'/Layouts';
                    LtFile::path($storageLayoutPath)->createFile('AppliedLayouts.json');
                    LtFile::path($storageLayoutPath)->createFile('PendingLayouts.json');
                    
                    $contentName = ucfirst($packageName).'Routes.php';
                    // LtFile::path($path)->createFile($routeFilename);
                    
                    
                    // Create menu for the package
                    $serviceMenuModel = [
                        'menuName'=> $packageName,
                        'packageName' => $packageName,   
                        'routePath' => '#',
                        'target' => '_self',
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
                    LtFile::path($path)->createFolder('Components');
                    LtFile::path($path)->createFolder('Views');
                    LtFile::path($path)->createFolder('Assets');
                    LtFile::path($path)->createFolder('Layouts');
                    LtFile::path($path)->createFolder('Export');
                    LtFile::path($path)->createFolder('ddm');
                    LtFile::path($path)->createFolder('Storage');
                    LtFile::path($path)->createFile('LICENSE', $licenceContent);
                    LtFile::path($path)->createFile('package.json', $jsonData);
                    LtFile::path($path)->createFile('README.md');
                    $path="packages/".$packageType.'s/'.$packageName.'/Export';
                    LtFile::path($path)->createFile('page.json');
                    LtFile::path($path)->createFile('content.json');
                    LtFile::path($path)->createFile('menu.json');
                    LtFile::path($path)->createFile('table.json');
                    LtFile::path($path)->createFile('pageContent.json');
                    $storagePath ="packages/".$packageType.'s/'.$packageName.'/Storage';
                    LtFile::path($storagePath)->createFolder('Layouts');
                    $storageLayoutPath =$storagePath .'/Layouts';
                    LtFile::path($storageLayoutPath)->createFile('AppliedLayouts.json');
                    LtFile::path($storageLayoutPath)->createFile('PendingLayouts.json');
                    
                    
                    // Create menu for the package
                    $serviceMenuModel = [
                        'menuName'=> $packageName,
                        'packageName' => $packageName,   
                        'routePath' => '#',
                        'target' => '_self',
                        'menuLevel' => '1',
                        'isPublished' => '1',  
                        'sortOrder' => '8',  
                    ];
                    if($roleExist){
                        $serviceMenuModel[$developerRoleId] = 1;
                    }
                    
                    $menuService = new TbMenuService();
                    $result = $menuService->store($serviceMenuModel);
                    
                    return LtResponse::json('Package created successfully', 3306, 200, $model->responseData);
                    
                }
            }
            
        }

        public function updatePackage($model){
            
            $packageName = $this->request->packageName;
            $packageType = $this->request->packageType;
            $model->updatedBy = $this->userId;
            $dependsOn = $this->request->dependsOn;
            $ltVersion = $this->request->ltVersion;
            
                $path="packages/".$packageType.'s/'.$packageName.'/';
                $packageJsonPath = $path . 'package.json';
    
                $packageJsonContent = file_get_contents($packageJsonPath);
                if($packageJsonContent === false){
                    return LtResponse::json("Failed to read package json file ", "3308");
                }
                $data = json_decode($packageJsonContent, true);
                $data["version"] = $model->version;
                $data["dependsOn"] = json_decode($dependsOn, true);
                $data["isPublished"] = $model->isPublished;
                $data["readme"] = $model->readme;
                $data["description"] = $model->description;
                $data["languageUsed"] = $model->languageUsed;
                $data["ltVersion"] = $ltVersion;
                $jsonData = json_encode($data, JSON_PRETTY_PRINT);
                LtFile::path($path)->writeFile('package.json', $jsonData);

            $model->dependsOn = $dependsOn;
            $model->ltVersion = $ltVersion;
            $model->update('ltId', '=', $model->ltId);
            return $model->responseJson(); 
            
            
        }
 
        public function filteredPackages(){
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
                 WHERE package_type IN (%s) AND is_published = 1",
                implode(',', array_fill(0, count($filtered), '?'))
            );
        
            $stmt = $this->sqlConnect->prepare($sql);
            $stmt->execute($filtered);
            $result = $stmt->fetchAll(\PDO::FETCH_OBJ);
            if(empty($result)) return LtResponse::json("No record Found", '101', '100');
            return LtResponse::json("Filtered Record fetched successfully", '309', '200', $result);
        }
        
        public function validateDependency(){
            // Decode and ensure it's an array (default to empty array if null)
            $inputDependencies = json_decode($this->request->dependencies, true);
            $dependencies = is_array($inputDependencies) ? $inputDependencies : [];
            $result = $this->checkDependencyRequirement($dependencies);
            return $result;
        }
        
        public function checkDependencyRequirement($dependencies){
            $dependencyResult = [];
            $allRequiredMeet = [];
                        
            foreach ($dependencies as $dependency) {
                $dependPackageName = $dependency['packageName'] ?? '';
                $dependPackageVersion = $dependency['version'] ?? '0';

                // Skip if package name is somehow empty
                if (empty($dependPackageName)) {
                    continue;
                }
        
                // Fetch securely and check if it exists before targeting index [0]
                $dbResult = $this->packageModel->select('version, is_published')
                                               ->where('packageName', '=', $dependPackageName)
                                               ->get();
                
                $exist = !empty($dbResult) ? $dbResult[0] : null;
                
        
                if ($exist) {
                    $installedVersion = $exist->version;
                    $isPublished = $exist->isPublished;
                    
                    // Check if the currently installed version is older (<) than the required version
                    if (version_compare($installedVersion, $dependPackageVersion, '<')) {
                        $dependencyResult[] = [
                            'packageName' => $dependPackageName,
                            'version'     => $dependPackageVersion,
                            'installed'   => $installedVersion,
                            'isInstalled' => true,
                            'isPublished' => $isPublished ? true : false,
                            'isUpdated'   => false // Needs an update
                        ];
                    } else {
                        
                        if($isPublished){
                            $dependencyResult[] = [
                                'packageName' => $dependPackageName,
                                'version'     => $dependPackageVersion,
                                'installed'   => $installedVersion,
                                'isInstalled' => true,
                                'isPublished' => $isPublished ? true : false,
                                'isUpdated'   => true // Requirement satisfied
                            ];
                            $allRequiredMeet[] = [
                                'packageName' => $dependPackageName,
                            ];
                        }else{
                            $dependencyResult[] = [
                                'packageName' => $dependPackageName,
                                'version'     => $dependPackageVersion,
                                'installed'   => $installedVersion,
                                'isInstalled' => true,
                                'isPublished' => $isPublished ? true : false,
                                'isUpdated'   => true // Requirement satisfied
                            ];
                        }
                        
                    }
                } else {
                    // Package is not installed at all
                    $dependencyResult[] = [
                        'packageName' => $dependPackageName,
                        'version'     => $dependPackageVersion,
                        'installed'   => null,
                        'isInstalled' => false,
                        'isPublished' => false,
                        'isUpdated'   => false
                    ];
                }
            }
            
            // return $newR;
            // checking if number of dependency is equal to number of all required array
           if(count($dependencies) === count($allRequiredMeet)){
                return LtResponse::json("Requirements satisfy", '3319', '200', $dependencyResult);
           }else{
                return LtResponse::json("validated; action required", '3319', '100', $dependencyResult);
           }
        }
        
        public function browseProductFromMarkePlace() {
            $tab = $this->request->tab ?? 'all';
            $search = $this->request->search ?? '';
            
            // 1. Build the remote URL dynamically
            // Removed the double slash // before products
            $remoteServerUrl = "https://www.lifetech.host/hubs/api/v1/v2/products/browse/" . urlencode($tab);
            
            // Append search query if provided (assuming the main server supports ?search=...)
            if (!empty($search)) {
                $remoteServerUrl .= "?search=" . urlencode($search);
            }
            
            $remotePackages = []; 
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $remoteServerUrl,
                CURLOPT_RETURNTRANSFER => true, 
                CURLOPT_FOLLOWLOCATION => true, 
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                ],
                CURLOPT_CONNECTTIMEOUT => 5,  
                CURLOPT_TIMEOUT => 15,        
                CURLOPT_SSL_VERIFYPEER => true, 
                CURLOPT_SSL_VERIFYHOST => 2     
            ]);
        
            $curlResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
        
            if ($curlResponse !== false && $httpCode === 200) {
                $decodedData = json_decode($curlResponse, true);
        
                if (isset($decodedData['responseCategory']) && $decodedData['responseCategory'] == 100) {
                    // Pass the remote server's exact error directly to the frontend
                    return json_encode($decodedData);
                } else {
                    $remotePackages = $decodedData['responseData'] ?? [];
                }
            } else {
                return LtResponse::json("Failed to connect to the main marketplace server.", '3319', '100');
            }
            
            // 2. Fetch locally installed packages
            $localPackages = $this->packageModel->select('package_name, version')->get();
            
            $localMap = [];
            if (!empty($localPackages)) {
                foreach ($localPackages as $lp) {
                    $localMap[$lp->packageName] = $lp->version;
                }
            }
        
            $marketplaceResults = [];
            
            // return json_encode($remotePackages);
            // 3. Compare Remote vs Local
            foreach ($remotePackages as $remotePkg) {
                $name = $remotePkg['packageName'];
                $remoteVersion = $remotePkg['version'];
                
                $isInstalled = array_key_exists($name, $localMap);
                $isUpdated = false;
                $localVersion = null;
        
                if ($isInstalled) {
                    $localVersion = $localMap[$name];
                    if (version_compare($localVersion, $remoteVersion, '>=')) {
                        $isUpdated = true;
                    }
                }
        
                $marketplaceResults[] = [
                    'packageName'       => $name,
                    'description'       => $remotePkg['description'] ?? 'No description provided.',
                    'type'              => $remotePkg['packageType'] ?? 'module',
                    'approvalStatus'    => $remotePkg['approvalStatus'] ?? 'review',
                    'payment'           => $remotePkg['paymentCategory'] ?? 'free',
                    'price'             => $remotePkg['productPrice'] ?? 0, // depends on if payment is free or paid if is paid it will display the price tag else free
                    'dependency'        => json_decode($remotePkg['dependency']) ?? [], // depends on array with e.g [packageName => 'name', version => '1.12.3']
                    'latestVersion'     => $remoteVersion,
                    'localVersion'      => $localVersion,
                    'isInstalled'       => $isInstalled,
                    // 'isUpdated'         => false
                    'isUpdated'         => $isUpdated
                ];
            }
        
            return LtResponse::json("Marketplace packages retrieved successfully", '3319', '200', $marketplaceResults);
        }
        
        public function installFromMarketplace() {
            $packageName = $this->request->packageName;
            $state = $this->request->state ?? 'download'; // Default to download if not provided
            $path = "storage/tmp/import/";
            
            // Define the tag name at the top so it is available for validation
            $tagName = $this->request->version; 
            
            if (empty($packageName) || empty($tagName)) {
                return LtResponse::json('Package name and version are required.', '3310', '100');
            }
            
            // ==========================================
            // PHASE 1: DOWNLOAD & EXTRACT
            // ==========================================
            if ($state === 'download') {
                $tempDir = "storage/tmp/" . uniqid('lt_install_', true);
                if (!mkdir($tempDir, 0755, true)) {
                    return LtResponse::json("Failed to create local workspace.", '3311', '100');
                }
            
                $tempZip = $tempDir . DIRECTORY_SEPARATOR . "download.zip";
                $mainServerDownloadUrl = "https://www.lifetech.host/hubs/api/v1/v2/products/local/download";
            
                $postData = json_encode(['packageName' => $packageName, 'tagName' => $tagName]);
            
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $mainServerDownloadUrl,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $postData,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_POSTREDIR      => CURL_REDIR_POST_ALL,
                    CURLOPT_HTTPHEADER     => [
                        'Content-Type: application/json',
                        'Content-Length: ' . strlen($postData),
                        'Accept: application/zip, application/json'
                    ],
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_CONNECTTIMEOUT => 15,
                    CURLOPT_TIMEOUT        => 180,
                ]);
            
          // 1. Execute cURL
                $curlResponse = curl_exec($ch);
                $curlError    = curl_error($ch);
                $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                
            
                // 2. Network level check
                if ($curlResponse === false) {
                    $this->deleteFolder($tempDir);
                    return LtResponse::json("Download request failed: {$curlError}", '3312', '100');
                }
            
                // 3. Inspect for ZIP signature (PK\x03\x04)
                $zipSignature = "\x50\x4b\x03\x04";
                $zipStart = strpos($curlResponse, $zipSignature);

                if ($zipStart === false) {
                    // NOT a ZIP file: do not strip anything, clean up temp dir and return exact remote response
                    $this->deleteFolder($tempDir);
                    return $curlResponse;
                }
            
                // IS a ZIP file: strip any leading spaces, PHP notices, or BOM prefixes
                if ($zipStart > 0) {
                    $curlResponse = substr($curlResponse, $zipStart);
                }
            
                // Proceed with saving and extracting the ZIP
                $bytesWritten = file_put_contents($tempZip, $curlResponse);
                if ($bytesWritten === false) {
                    $this->deleteFolder($tempDir);
                    return LtResponse::json("Could not save downloaded ZIP file.", '3314', '100');
                }
            
                if (!class_exists('ZipArchive')) {
                    $this->deleteFolder($tempDir);
                    return LtResponse::json('ZipArchive is not Activated in your PHP Configuration.', 1999, 100);
                }
            
                $zip = new \ZipArchive();
                $zipStatus = $zip->open($tempZip);
            
                if ($httpCode !== 200 || $zipStatus !== true) {
                    $this->deleteFolder($tempDir);
                    return LtResponse::json("Marketplace returned a corrupted package (Zip Status: {$zipStatus}).", '3315', '100');
                }
            
                // 4. Extract verified ZIP
                $importBase = "storage/tmp/";
                LtFile::path($importBase)->unlink('import', 'no');
            
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }
            
                $zip->extractTo($path);
                $zip->close();
            
                $this->deleteFolder($tempDir);
            
                return LtResponse::json("Package downloaded and extracted successfully.", '3316', '200');
            }
            
            // ==========================================
            // PHASE 2: IMPORT & REGISTER
            // ==========================================
            
            elseif ($state === 'install') {
                if (!is_dir($path)) {
                    return LtResponse::json("Import folder is not found.", '3319', '100');
                }
                
                // --- NEW: VALIDATE PACKAGE PREFIX ---
                $isValidPrefix = false;
                
                // We use an array so you can easily add more prefixes later.
                $allowedPrefixes = ['theme', 'md', 'plg']; 
                
                foreach ($allowedPrefixes as $prefix) {
                    // stripos === 0 ensures the string STARTS with the prefix (case-insensitive)
                    if (stripos($packageName, $prefix) === 0) {
                        $isValidPrefix = true;
                        break;
                    }
                }
                
                if (!$isValidPrefix) {
                    // Clean up the invalid folder so it doesn't waste disk space
                    $this->deleteFolder($path);
                    return LtResponse::json("Invalid package type. Name must start with 'Theme', 'Md', or 'Plg'.", '3310', '100');
                }
                
                // Since $path already ends with '/', just concatenate $packageName
                $filePath = $path . $packageName; 
                if (!file_exists($filePath)) {
                     return LtResponse::json("Installation source missing. Please restart the download.", '3311', '100');
                }
            
                // Run framework installation logic
                $result = $this->importPackageAbstract($path);
                
                return $result;
            }
        
            // Fallback for invalid states
            return LtResponse::json("Invalid installation state.", '3312', '100');
        }
        
        
        private function deleteFolder($dir) {
            if (!is_dir($dir)) return;
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? $this->deleteFolder("$dir/$file") : unlink("$dir/$file");
            }
            rmdir($dir);
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
                    'themeLayout'=> $value->themeLayout,
                    'themeLayoutPackage'=> $value->themeLayoutPackage,
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
                    'themeResourceType'=> $value->themeResourceType,
                    'additionalPath'=> $value->additionalPath,
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
            // $pageContentRecord = $pageContentModel->select()->where('packageName', '=', $packageName)->andWhere('contentType', '<>', 'theme')->get();
            $pageContentRecord = $pageContentModel->select()->where('packageName', '=', $packageName)->get();
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
                 $result = LtFile::path($dstPath)->unlink('Services','no');
                 $result = LtFile::path($dstPath)->unlink('Models','no');
                 $result = LtFile::path($dstPath)->unlink('Controllers','no');
                 $result = LtFile::path($dstPath)->unlink('Contents','no');
                 // for all
                 $result = LtFile::path($dstPath)->unlink('Views','no');
                 // theme
                 $result = LtFile::path($dstPath)->unlink('Components','no');
                 $result = LtFile::path($dstPath)->unlink('Assets','no');
                 $result = LtFile::path($dstPath)->unlink('Layouts','no');
              }
            
             // create Zip file
             $path="storage/tmp/export/"; 
           
             $result = LtFile::path($path)->download($packageName);
             $result = LtFile::path($path)->unlink($packageName); 
            return LtResponse::json('Package exported successfully.', 3310, 200);
            // return json_encode($result);
            
            
        }
        
        public function zipFolder($folderPath, $zipFilePath){
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
            $tmp_name = $_FILES['file']['tmp_name'];
            $path="storage/tmp/"; 
             
            $result = LtFile::path($path)->unlink('import', 'no');   // to clean import folder before extracting inside
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
            
            $result = $this->importPackageAbstract($path);
            return $result;
        }    
        
        public function togglePublish($model) {
            $sqlConnect = DbConnect::dbDriver();
            $ltId = $this->request->ltId;
            $isPublished = (int) $this->request->isPublished; // Cast to int for safety
            $packageName = $this->request->packageName;
            
            $model->updatedBy = LtSession::get('ltUid');
            $updatePublish = false;
            
            // Check if package exists. 
            $dbResult = $model->select('depends_on')->where('packageName', '=', $packageName)->get();
            
            if (empty($dbResult)) {
                return LtResponse::json("Failed to publish; package is not found", '3319', '100');
            }
            
            $exist = $dbResult[0];
        
            // If attempting to PUBLISH, we must check dependencies
            if ($isPublished === 1) {
                // Safely extract and decode the dependencies JSON
                $rawDependencies = $exist->dependsOn ?? '[]';
                $inputDependencies = json_decode($rawDependencies, true);
                $dependencies = is_array($inputDependencies) ? $inputDependencies : [];
                
                // Run the dependency check
                $result = $this->checkDependencyRequirement($dependencies);
                $check = json_decode($result, true);
                $returnDependencies = $check['responseData'];

                if ($check['responseCategory'] == 100) {
                    $failedDependency = [];
                    foreach($returnDependencies as $dependency){
                        if(!$dependency['isInstalled'] || !$dependency['isPublished'] || !$dependency['isUpdated']){
                             $failedDependency[] = $dependency['packageName'];
                        }
                    }
                    
                    $failedDependencyString = implode(',', $failedDependency);
                    $msg = count($failedDependency) > 1 ? "dependencies are" : "dependency is";
                    return LtResponse::json("Failed to publish; please check [$failedDependencyString] if this $msg installed, updated or published", '3319', '100');
                } else {
                    $updatePublish = true;
                }
            } else {
                // If UNPUBLISHING (0), no dependency check is needed
                $updatePublish = true;
            }
            
            // Execute the publish/unpublish action
            if ($updatePublish) {
                $model->isPublished = $isPublished;
                $model->update('ltId', '=', $ltId); 
                // for content
                $contentModel = new TbContentService(); 
                $publishContent = $contentModel->togglePublishedContent($packageName, $isPublished);
                // for menu
                $menuModel = new TbMenuService(); 
                $publishMenu = $menuModel->togglePublishedMenu($packageName, $isPublished);
                // for page
                $pageModel = new TbPageService(); 
                $publishPage = $pageModel->togglePublished($packageName, $isPublished);
                
                return $model->responseJson();
            }
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
        
        
        
        
        
public function importPackageAbstract($path){
    $batchSize = 100;
    $developerRoleId = 'accbc87e4b5ce2';
    $userId = LtSession::get('ltUid');
    $now = $this->ltNow;

    try {
        /*
         * STEP 1: Locate the extracted package directory.
         *
         * Unlike scandir()[2], glob() explicitly selects
         * directories rather than arbitrary entries.
         */
        $directories = glob(
            rtrim($path, '/\\') . '/*',
            GLOB_ONLYDIR
        );

 
        if (!$directories || count($directories) !== 1) {
            throw new \RuntimeException(
                'Invalid package directory structure.'
            );
        }

        $sourcePath = rtrim($directories[0], '/\\') . '/';

        /*
         * STEP 2: Read package metadata.
         */
       
        $packageDetails = $this->readImportJson($sourcePath . 'package.json');

        $dependsOn = $packageDetails['dependsOn'] ?? [];

        if (!is_array($dependsOn)) {
            throw new \RuntimeException(
                'Invalid package dependencies.'
            );
        }

        // Avoid binding the dependsOn array in processRequest().
        unset($packageDetails['dependsOn']);

        $this->packageModel->processRequest($packageDetails);

        $packageName = $this->packageModel->packageName;
        $packageType = $this->packageModel->packageType;

        /*
         * Protect destination paths from unsafe package names.
         * Adjust allowed characters to your package specification.
         */
        // foreach ([$packageName, $packageType] as $name) {
        //     if (!is_string($name) ||
        //         !preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
        //         throw new \RuntimeException(
        //             'Invalid package name or type.'
        //         );
        //     }
        // }

        /*
         * STEP 3: Check the developer role.
         */
        $roleModel = new TbRoleService();

        $rolePublished = $roleModel->roleExist($developerRoleId) ? 1 : 0;
    
        /*
         * STEP 4: Validate dependencies.
         *
         * Preserve the existing special rule:
         * mdlt is published regardless of dependency response.
         */
        $dependencyResponse = json_decode($this->checkDependencyRequirement($dependsOn), true, 512, JSON_THROW_ON_ERROR);

        $dependenciesSatisfied = (
            (int)($dependencyResponse['responseCategory'] ?? 0)
            === 200
        );

        $isPublished = (strtolower($packageName) === 'mdlt' || $dependenciesSatisfied) ? 1 : 0;

        /*
         * STEP 5: Read and validate all exported JSON.
         *
         * Read before moving files or changing database data,
         * so malformed JSON cannot cause a partial data import.
         */
        $exportPath = $sourcePath . 'Export/';

        $pages = $this->readImportJson($exportPath . 'page.json', true);

        $menus = $this->readImportJson($exportPath . 'menu.json', true);

        $contents = $this->readImportJson($exportPath . 'content.json', true);

        $pageContents = $this->readImportJson($exportPath . 'pageContent.json',true);

        $tables = $this->readImportJson($exportPath . 'table.json',true);
        
        /*
         * STEP 6: Move package into its destination.
         *
         * Verify that moveFolder() can safely replace an
         * existing destination during package upgrades.
         */
        $destinationPath = 'packages/'. $packageType . 's/'. $packageName . '/';

        $moveResult = LtFile::path($sourcePath)->moveFolder($sourcePath, $destinationPath);

        if ($moveResult === false) {
            throw new \RuntimeException(
                'Failed to move package files.'
            );
        }


        /*
         * STEP 7: Create package database tables.
         *
         * Exported tables may reference other package tables
         * through foreign-key constraints.
         *
         * Temporarily disable foreign-key checks while processing
         * the package's table definitions.
         *
         * IMPORTANT:
         * - Applies only to the current PDO database session.
         * - Always restore the previous setting.
         * - Execute only SQL from trusted packages.
         * - MySQL DDL operations cannot be rolled back normally.
         */

    // Preserve the session's original setting.
        $originalForeignKeyChecks = (int) $this->sqlConnect->query('SELECT @@SESSION.foreign_key_checks')->fetchColumn();
        
        try {
        
            // Disable foreign-key checks for this connection.
            $this->sqlConnect->exec("SET SESSION FOREIGN_KEY_CHECKS = 0");
        
            foreach ($tables as $index => $table) {
        
                $sql = $table['tableData'] ?? null;
        
                // Validate table definition.
                if (!is_string($sql) || trim($sql) === '') {
                    throw new \RuntimeException(
                        "Invalid table definition at index {$index}."
                    );
                }
        
                /*
                 * Prevent accidental execution of unrelated SQL.
                 *
                 * This is a basic format check, not a security
                 * sandbox for untrusted SQL.
                 */
                if (!preg_match('/^\s*CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\b/i', $sql)) {
                    throw new \RuntimeException("Unsupported table statement at index {$index}.");
                }
        
                // Execute CREATE TABLE statement.
                $this->sqlConnect->exec($sql);
            }
        
        } finally {
        
            /*
             * Restore the original foreign-key setting,
             * whether table creation succeeded or failed.
             */
            $this->sqlConnect->exec("SET SESSION FOREIGN_KEY_CHECKS = ". $originalForeignKeyChecks);
        }


        /*
         * STEP 8: Start transactional database operations.
         *
         * Package model operations participate only if they
         * use the same PDO connection.
         */
        $this->sqlConnect->beginTransaction();

        /*
         * STEP 9: Insert or update package metadata.
         *
         * Keep LifeTech model methods for this operation.
         */
        $this->packageModel->dependsOn = json_encode($dependsOn,JSON_THROW_ON_ERROR);

        $this->packageModel->isPublished = $isPublished;
        $this->packageModel->$developerRoleId = $rolePublished;

        $existingPackage = $this->packageModel->select()->where('packageName', '=', $packageName)->get();

        if (count($existingPackage) > 0) {
            $this->packageModel->update('packageName','=', $packageName);
            
        } else {
            $this->packageModel->insert();
        }

        if ((int)$this->packageModel->responseCategory !== 200) {
            throw new \RuntimeException(
                'Failed to save package metadata: '
                . $this->packageModel->responseResult
            );
        }
        
        $returnData = $this->packageModel->responseData;
        /*
         * STEP 10: Batch UPSERT pages.
         */
        $this->importPackageRecords(
            'tb_page',
            $pages,
            [
                'lt_id' => 'ltId',
                'package_name' => 'packageName',
                'page_name' => 'pageName',
                'page_type' => 'pageType',
                'route_content' => 'routeContent',
                'route_file_name' => 'routeFileName',
                'route_handler' => 'routeHandler',
                'route_method' => 'routeMethod',
                'route_path' => 'routePath',
                'route_payload' => 'routePayload',
                'route_response' => 'routeResponse',
                'route_update_status' => 'routeUpdateStatus',
                'api_to_allow_routers' => 'apiToAllowRouters',
                'description' => 'description',
                'theme_layout'=> 'themeLayout',
                'theme_layout_package'=> 'themeLayoutPackage',
                
            ],
            [
                'is_published' => $isPublished,
                $developerRoleId => $rolePublished
            ],
            $batchSize,
            $userId,
            $now
        );

        /*
         * STEP 11: Batch UPSERT menus.
         */
        $this->importPackageRecords(
            'tb_menu',
            $menus,
            [
                'lt_id' => 'ltId',
                'package_name' => 'packageName',
                'menu_level' => 'menuLevel',
                'menu_name' => 'menuName',
                'content_name' => 'contentName',
                'parent_menu_id' => 'parentMenuId',
                'route_path' => 'routePath',
                'slug' => 'slug',
                'target' => 'target',
                'sort_order' => 'sortOrder'
            ],
            [
                'is_published' => $isPublished,
                $developerRoleId => $rolePublished
            ],
            $batchSize,
            $userId,
            $now
        );

    /*
     * STEP 12: Batch UPSERT contents.
     *
     * Assign developer role only when:
     * - mvcType === 'View'
     * OR
     * - themeResourceType === 'View'
     */
    $this->importPackageRecords(
        'tb_content',
        $contents,
        [
            'lt_id' => 'ltId',
            'package_name' => 'packageName',
            'page_type' => 'pageType',
            'use_liferazor' => 'useLiferazor',
            'store_type' => 'storeType',
            'mvc_type' => 'mvcType',
            'is_theme_default' => 'isThemeDefault',
            'content_type' => 'contentType',
            'content_source' => 'contentSource',
            'content_name' => 'contentName',
            'content_category' => 'contentCategory',
            'content_body' => 'contentBody',
            'ccurrent' => 'ccurrent',
            'ccname' => 'ccname',
            'description' => 'description',
            'sort_order' => 'sortOrder',
            'theme_resource_type' => 'themeResourceType',
            'additional_path' => 'additionalPath'
        ],
        [
            'is_published' => $isPublished
        ],
        $batchSize,
        $userId,
        $now,
        function (array $record) use (
            $developerRoleId,
            $rolePublished
        ): array {
    
            $isView = (
                strcasecmp((string)($record['mvcType'] ?? ''), 'View') === 0
                ||
                strcasecmp((string)($record['themeResourceType'] ?? ''), 'View') === 0
            );
    
            return [
                $developerRoleId => $isView ? $rolePublished : 0
            ];
        }
    );

        /*
         * STEP 13: Batch UPSERT normal page-content records.
         */
        $this->batchInsertPageContent($pageContents,$batchSize,$userId);

        /*
         * STEP 14: Generate missing default-theme regions.
         */
        $this->insertThemePageRegions($pages, $pageContents, $batchSize, $userId, $now);

        /*
         * STEP 15: Commit changes when all operations succeed.
         */
        $this->sqlConnect->commit();

        return LtResponse::json('Package installation completed successfully.', 3311, 200, $returnData);

    } catch (\Throwable $e) {

        if ($this->sqlConnect->inTransaction()) {
            $this->sqlConnect->rollBack();
        }

        return LtResponse::error('Package installation failed. Check server logs.'.$e->getMessage(), 3319, 100);
    }
}


/**
 * Insert or update page-content records in batches.
 *
 * Uses the 10 columns from your existing implementation.
 * Existing created_at values are preserved by batchUpsert().
 *
 * This method remains public for compatibility with any
 * existing LifeTech code that calls it directly.
 */
public function batchInsertPageContent(array $pageContents, int $batchSize, $userId): void {

    if (empty($pageContents)) {
        return;
    }

    if ($batchSize < 1) {
        throw new \InvalidArgumentException(
            'Batch size must be greater than zero.'
        );
    }

    $requiredFields = [
        'ltId',
        'contentId',
        'contentType',
        'packageName',
        'pageId',
        'sortOrder',
        'publishSource'
    ];

    $batch = [];

    foreach ($pageContents as $row) {

        if (!is_array($row)) {
            throw new \RuntimeException(
                'Invalid page-content record.'
            );
        }

        // Check required JSON properties.
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $row)) {
                throw new \RuntimeException(
                    "Missing page-content property: {$field}"
                );
            }
        }

        // Map the exported properties to SQL columns.
        $batch[] = [
            'lt_id' => $row['ltId'],
            'content_id' => $row['contentId'],
            'content_type' => $row['contentType'],
            'package_name' => $row['packageName'],
            'page_id' => $row['pageId'],
            'sort_order' => $row['sortOrder'],
            'publish_source' => $row['publishSource'],
            'created_at' => $row['createdAt'] ?? $this->ltNow,
            'updated_at' => $this->ltNow,
            'updated_by' => $userId
        ];

        // Write one batch when the limit is reached.
        if (count($batch) >= $batchSize) {

            $this->batchUpsert(
                'tb_page_content',
                $batch
            );

            $batch = [];
        }
    }

    // Write any remaining records.
    if (!empty($batch)) {
        $this->batchUpsert(
            'tb_page_content',
            $batch
        );
    }
}


/**
 * Reusable batch UPSERT helper.
 *
 * Inserts records that do not exist.
 * Updates existing records when MySQL finds a unique-key conflict.
 *
 * REQUIRES:
 * Suitable UNIQUE or PRIMARY indexes in the database.
 *
 * IMPORTANT:
 * ON DUPLICATE KEY UPDATE reacts to any unique constraint,
 * not specifically lt_id. Review other unique indexes.
 */
private function batchUpsert(string $table, array $rows, array $skipUpdate = ['lt_id', 'created_at']): void {

    if (empty($rows)) {
        return;
    }

    /*
     * Allow only known application tables.
     * Table names cannot be bound using PDO placeholders.
     */
    $allowedTables = ['tb_page','tb_menu','tb_content','tb_page_content'];

    if (!in_array($table, $allowedTables, true)) {
        throw new \InvalidArgumentException(
            "Unsupported import table: {$table}"
        );
    }

    // Use the first row to determine the column ordering.
    $columns = array_keys($rows[0]);

    if (empty($columns)) {
        throw new \RuntimeException(
            'Cannot UPSERT records with no columns.'
        );
    }

    // Validate column names before placing them in SQL.
    foreach ($columns as $column) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new \InvalidArgumentException(
                "Invalid SQL column: {$column}"
            );
        }
    }

    $quotedColumns = array_map(
        static fn($column) => "`{$column}`",
        $columns
    );

    $placeholders = [];
    $values = [];

    /*
     * Convert the records into:
     *
     * VALUES (?, ?, ...), (?, ?, ...), ...
     *
     * Keep all actual data in bound parameters.
     */
    foreach ($rows as $row) {

        if (array_keys($row) !== $columns) {
            throw new \RuntimeException(
                'Inconsistent batch columns.'
            );
        }

        $placeholders[] = '(' . implode(
            ', ',
            array_fill(0, count($columns), '?')
        ) . ')';

        foreach ($columns as $column) {

            $value = $row[$column];

            // Explicitly encode structured JSON values.
            if (is_array($value)) {
                $value = json_encode(
                    $value,
                    JSON_THROW_ON_ERROR
                );
            }

            $values[] = $value;
        }
    }

    /*
     * Generate UPDATE expressions for all columns except
     * identifiers and fields that should be preserved.
     */
    $updates = [];

    foreach ($columns as $column) {

        if (in_array($column, $skipUpdate, true)) {
            continue;
        }

        $updates[] = "`{$column}` = VALUES(`{$column}`)";
    }

    if (empty($updates)) {
        throw new \RuntimeException(
            'No columns available for UPSERT updates.'
        );
    }

    /*
     * VALUES(column) is supported by MySQL/MariaDB.
     * Newer MySQL versions deprecate this form in favor
     * of row aliases; adapt it for your deployed version
     * if necessary.
     */
    $sql = "
        INSERT INTO `{$table}` (
            " . implode(', ', $quotedColumns) . "
        )
        VALUES " . implode(', ', $placeholders) . "
        ON DUPLICATE KEY UPDATE
        " . implode(', ', $updates);

    $stmt = $this->sqlConnect->prepare($sql);

    if (!$stmt->execute($values)) {
        throw new \RuntimeException(
            "Batch UPSERT failed for {$table}."
        );
    }
}



/**
 * Import package records using batch UPSERT.
 *
 * $rowCallback optionally generates additional values
 * based on each individual imported record.
 */
private function importPackageRecords(
    string $table,
    array $records,
    array $columnMap,
    array $fixedValues,
    int $batchSize,
    $userId,
    string $now,
    ?callable $rowCallback = null
): void {

    if (empty($records)) {
        return;
    }

    if ($batchSize < 1) {
        throw new \InvalidArgumentException(
            'Batch size must be greater than zero.'
        );
    }

    $batch = [];

    foreach ($records as $record) {

        if (!is_array($record)) {
            throw new \RuntimeException(
                "Invalid record for {$table}."
            );
        }

        $row = [];

        // Convert JSON properties into database columns.
        foreach ($columnMap as $column => $property) {

            if (!array_key_exists($property, $record)) {
                throw new \RuntimeException(
                    "Missing {$property} in {$table}."
                );
            }

            $value = $record[$property];

            if (is_array($value)) {
                $value = json_encode(
                    $value,
                    JSON_THROW_ON_ERROR
                );
            }

            $row[$column] = $value;
        }

        // Apply values common to all records.
        foreach ($fixedValues as $column => $value) {
            $row[$column] = $value;
        }

        /*
         * Apply conditional values per record.
         *
         * For tb_content:
         * Assign developer role when mvcType or
         * themeResourceType is View.
         */
        if ($rowCallback !== null) {

            $additionalValues = $rowCallback($record);

            foreach ($additionalValues as $column => $value) {
                $row[$column] = $value;
            }
        }

        // Audit fields.
        $row['created_at'] = $now;
        $row['updated_at'] = $now;
        $row['updated_by'] = $userId;

        $batch[] = $row;

        if (count($batch) >= $batchSize) {

            $this->batchUpsert($table, $batch);

            $batch = [];
        }
    }

    // Process remaining records.
    if (!empty($batch)) {
        $this->batchUpsert($table, $batch);
    }
}



/**
 * Generate missing region relationships for imported theme pages.
 *
 * A default theme may contain content elements configured as
 * theme regions. Theme pages should reference these elements.
 *
 * Existing region relationships are preserved.
 *
 * NOTE:
 * The SELECT-based duplicate check works for sequential imports.
 * For concurrent imports, enforce the appropriate relationship
 * uniqueness constraint in the database.
 */
private function insertThemePageRegions(
    array $pages,
    array $pageContents,
    int $batchSize,
    $userId,
    string $now
): void {

    if ($batchSize < 1) {
        throw new \InvalidArgumentException(
            'Batch size must be greater than zero.'
        );
    }

    /*
     * STEP 1: Build a lookup of imported theme pages.
     *
     * Associative lookups are more efficient than repeatedly
     * searching an array with in_array().
     */
    $themePageIds = [];

    foreach ($pages as $page) {

        if (($page['pageType'] ?? null) === 'theme') {
            $themePageIds[$page['ltId']] = true;
        }
    }

    if (empty($themePageIds)) {
        return;
    }

    /*
     * STEP 2: Identify theme pages with page-content records.
     *
     * Preserve the eligibility rule in your original importer.
     */
    $eligiblePageIds = [];

    foreach ($pageContents as $pageContent) {

        $pageId = $pageContent['pageId'] ?? null;

        if ($pageId !== null &&
            isset($themePageIds[$pageId])) {

            $eligiblePageIds[$pageId] = true;
        }
    }

    if (empty($eligiblePageIds)) {
        return;
    }

    /*
     * STEP 3: Retrieve default-theme region contents.
     *
     * The query assumes there is one active default theme.
     */
    $sql = "
        SELECT
            c.lt_id,
            c.sort_order,
            c.package_name
        FROM tb_content c
        WHERE c.package_name = (
            SELECT p.package_name
            FROM tb_package p
            WHERE p.package_type = 'theme'
              AND p.is_theme_default = 1
            LIMIT 1
        )
        AND c.is_theme_default = 1
    ";

    $stmt = $this->sqlConnect->prepare($sql);
    $stmt->execute();

    $themeContents = $stmt->fetchAll(
        \PDO::FETCH_ASSOC
    );

    if (empty($themeContents)) {
        return;
    }

    /*
     * STEP 4: Find region relationships already stored.
     *
     * These are identified by page_id and content_id
     * where publish_source is 'region'.
     */
    $existingRegions = [];

    foreach (
        array_chunk(array_keys($eligiblePageIds), $batchSize)
        as $pageIdBatch
    ) {

        $placeholders = implode(
            ', ',
            array_fill(0, count($pageIdBatch), '?')
        );

        $sql = "
            SELECT page_id, content_id
            FROM tb_page_content
            WHERE publish_source = 'region'
              AND page_id IN ({$placeholders})
        ";

        $stmt = $this->sqlConnect->prepare($sql);
        $stmt->execute($pageIdBatch);

        while ($record = $stmt->fetch(\PDO::FETCH_ASSOC)) {

            $existingRegions[
                $record['page_id']
            ][
                $record['content_id']
            ] = true;
        }
    }

    /*
     * STEP 5: Create only missing region records.
     */
    $regionBatch = [];

    foreach (array_keys($eligiblePageIds) as $pageId) {

        foreach ($themeContents as $content) {

            $contentId = $content['lt_id'];

            // Do not recreate an existing region.
            if (isset($existingRegions[$pageId][$contentId])) {
                continue;
            }

            $regionBatch[] = [
                'ltId' => ltId(),
                'contentId' => $contentId,
                'contentType' => 'theme',
                'packageName' => $content['package_name'],
                'pageId' => $pageId,
                'sortOrder' => $content['sort_order'],
                'publishSource' => 'region',
                'createdAt' => $now,
                'updatedAt' => $now
            ];

            /*
             * Mark the relationship as created in this import
             * so duplicate JSON inputs cannot repeat it.
             */
            $existingRegions[$pageId][$contentId] = true;

            if (count($regionBatch) >= $batchSize) {

                $this->batchInsertPageContent(
                    $regionBatch,
                    $batchSize,
                    $userId
                );

                $regionBatch = [];
            }
        }
    }

    /*
     * STEP 6: Insert the final region batch.
     */
    if (!empty($regionBatch)) {

        $this->batchInsertPageContent(
            $regionBatch,
            $batchSize,
            $userId
        );
    }
}


/**
 * Read and validate JSON files used by the package importer.
 *
 * $expectList = false:
 *     Accept a JSON object/array, typically package.json.
 *
 * $expectList = true:
 *     Require a JSON array, including an empty array.
 *
 * JSON_THROW_ON_ERROR provides useful failure messages.
 */
 
 private function readImportJson(string $file, bool $expectList = false): array{
    if (!is_file($file) || !is_readable($file)) {
        throw new \RuntimeException("Missing or unreadable JSON file: {$file}");
    }
    
    $content = file_get_contents($file);
    
    if ($content === false) {
        throw new \RuntimeException("Unable to read JSON file: {$file}");
    }
    
    $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    
    if (!is_array($data)) {
        throw new \RuntimeException("Invalid JSON structure in {$file}");
    }
    
    if ($expectList) {
        $isList = ($data === []);
    
        if (!$isList) {
            $isList = array_keys($data) === range(0, count($data) - 1);
        }
    
        if (!$isList) {
            throw new \RuntimeException("Expected a JSON list in {$file}");
        }
    }
    
     return $data;
}
        

     
        
}

