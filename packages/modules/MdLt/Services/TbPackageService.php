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
use Lt\Modules\MdLt\Services\CaseConverter;
use Lt\Modules\MdLt\Services\TbRoleService;

use DbConnect; 
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

// import package class
ob_end_clean();
ob_start();
ltImportEnd("d91c12c5a195c3605b3df1265761ce2d");


/**
 *  RESPONSE CODE 3300 - 3399
 */ 

// response_code 3300-3399

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
        
        
        public function index($data){
            
            $this->packageModel->select();
            $packageName = $data['packageName'];
            $packageType = $data['packageType'];
            $isPublished = $data['isPublished'];
            
            // Build conditions dynamically
            $hasCondition = false;

            if (!empty($packageName)) {
                $this->packageModel->where('package_name', '=', $packageName);
                $hasCondition = true;
            }
    
            if (!empty($packageType)) {
                // if there was a previous where, use andWhere
                if ($hasCondition) {
                    $this->packageModel->andWhere('package_type', '=', $packageType);
                } else {
                    $this->packageModel->where('package_type', '=', $packageType);
                    $hasCondition = true;
                }
            }
    
            if ($isPublished !== null) {
                if ($hasCondition) {
                    $this->packageModel->andWhere('is_published', '=', $isPublished);
                } else {
                    $this->packageModel->where('is_published', '=', $isPublished);
                    $hasCondition = true;
                }
            }
    
            // Default ordering
            $this->packageModel->orderBy('package_name', 'ASC');
    
            // Execute query
            $this->packageModel->get();
            ob_end_clean();
            
            // Assuming $packages is your array of returned objects/arrays from the query
            $packages = $this->packageModel->responseData;
            foreach ($packages as &$package) {
                // Standardize object or array access
                $isObject = is_object($package);
                $rawDependsOn = $package->dependsOn ?? '[]';
            
                // Default status
                $dependencyStatus = 'pass';
            
                // // Decode dependsOn JSON
                $dependencies = is_string($rawDependsOn) ? json_decode($rawDependsOn, true) : $rawDependsOn;
            
                // Check if dependencies exist and is not empty
                if (!empty($dependencies) && is_array($dependencies)) {
                    // Call checkDependencyRequirement
                    $result = $this->checkDependencyRequirement($dependencies);
                    $check = is_array($result) ? $result : json_decode($result, true);
                    
                    // If responseCategory is 100, at least one dependency failed requirements
                    if (isset($check['responseCategory']) && (int)$check['responseCategory'] === 100) {
                        $dependencyStatus = 'failed';
                    }
                }
            
                // // Attach status and details to the package
                    $package->dependencyStatus = $dependencyStatus;       // 'pass' or 'failed'
            }
            // unset($package); // Break reference
            
            return LtResponse()::json('success', 3301, 200, $packages);
            
        }
        

        public function store($model) {
            $packageName = trim($model->packageName ?? '');
            $packageType = trim($model->packageType ?? '');
            $rawDepends  = $this->request->dependsOn ?? '[]';
        
            // 1. Strict Package Name Validation (Fixes Bug #7: prevents spaces, slashes, path traversal)
            if (!preg_match('/^[A-Za-z0-9_-]+$/', $packageName)) {
                return LtResponse::json('Package name can only contain letters, numbers, underscores, and hyphens (no spaces).', 3303, 100);
            }
        
            // 2. Validate packageType and required prefix (Fixes Bug #6 & #8)
            $prefixRules = [
                'module' => ['prefix' => 'Md',    'code' => 3305, 'label' => 'Module'],
                'plugin' => ['prefix' => 'Plg',   'code' => 3305, 'label' => 'Plugin'],
                'theme'  => ['prefix' => 'Theme', 'code' => 3307, 'label' => 'Theme'],
            ];
        
            if (!isset($prefixRules[$packageType])) {
                return LtResponse::json('Invalid package type specified.', 3308, 100);
            }
        
            $rule = $prefixRules[$packageType];
            if (!str_starts_with($packageName, $rule['prefix'])) {
                return LtResponse::json("{$rule['label']} Package should start with {$rule['prefix']}", $rule['code'], 100);
            }
        
            // 3. Check uniqueness using a cloned instance (Fixes Bug #2: prevents query state pollution on $model)
            $checkModel = new TbPackage();
            $exist = $checkModel->select()->where('packageName', '=', $packageName)->get();
            if (!empty($exist)) {
                return LtResponse::json('Package Record already exists', 3304, 100);
            }
        
            // 4. Safely normalize $dependsOn for both DB string and JSON array
            if (is_string($rawDepends)) {
                $dependsOnArray = json_decode($rawDepends, true) ?? [];
            } elseif (is_array($rawDepends)) {
                $dependsOnArray = $rawDepends;
            } else {
                $dependsOnArray = [];
            }
            $dependsOnJson = json_encode($dependsOnArray);
        
            // 5. Insert package record
            $packageId        = ltId();
            $model->ltId      = $packageId;
            $model->dependsOn = $dependsOnJson;
            $model->updatedBy = $this->userId;
            $model->updatedAt = $this->ltNow;
            $model->createdAt = $this->ltNow;
            $model->insert();
        
            // Guard against DB insert failure
            if ($model->responseCategory != '200') {
                return $model->responseJson();
            }
        
            // 6. Build package.json and LICENSE content
            $jsonData = json_encode([
                'ltId'         => $packageId,
                'packageName'  => $packageName,
                'packageType'  => $packageType,
                'version'      => $model->version,
                'dependsOn'    => $dependsOnArray,
                'isPublished'  => $model->isPublished ?? '0',
                'readme'       => $model->readme,
                'description'  => $model->description,
                'languageUsed' => $model->languageUsed,
                'ltVersion'    => $model->ltVersion,
            ], JSON_PRETTY_PRINT);
        
            $licenseContent = $this->getMitLicenseContent();
        
            // 7. Scaffold package directories and valid empty JSON files
            $this->scaffoldPackageFiles($packageType, $packageName, $jsonData, $licenseContent);
        
            // 8. Check Developer Role once and apply to BOTH Menu and Content (Fixes Bug #5)
            $developerRoleId = 'accbc87e4b5ce2';
            $roleExist       = (new TbRoleService())->roleExist($developerRoleId);
        
            $serviceMenuModel = [
                'menuName'    => $packageName,
                'packageName' => $packageName,
                'routePath'   => '#',
                'target'      => '_self',
                'menuLevel'   => '1',
                'isPublished' => '1',
                'sortOrder'   => '8',
            ];
        
            $serviceContentModel = [
                'packageName'   => $packageName,
                'contentType'   => $packageType,
                'storeType'     => 'folder',
                'contentSource' => $packageType,
                'isPublished'   => '1',
                'sortOrder'     => '8',
                'description'   => 'Auto Generated Content',
            ];
        
            if ($packageType === 'theme') {
                $serviceContentModel['contentName']       = 'Default.html';
                $serviceContentModel['themeResourceType'] = 'Layout';
                $serviceContentModel['isThemeDefault']    = 0;
            } else {
                $serviceContentModel['contentName'] = ucfirst($packageName) . 'Routes.php';
                $serviceContentModel['mvcType']     = 'Service';
            }
        
            if ($roleExist) {
                $serviceMenuModel[$developerRoleId]    = 1;
            }
        
            (new TbMenuService())->store($serviceMenuModel);
            (new TbContentService())->store($serviceContentModel, false);
        
            return LtResponse::json('Package created successfully', 3306, 200, $model->responseData);
        }
        
        /**
         * Creates the directory tree and boilerplate files with valid initial JSON arrays.
         */
        private function scaffoldPackageFiles(string $packageType, string $packageName, string $jsonData, string $licenseContent): void {
            $typeRoot = "packages/{$packageType}s/";
            $pkgRoot  = "{$typeRoot}{$packageName}/";
            
            LtFile::path($typeRoot)->createFolder($packageName);
        
            $commonFolders = ['Export', 'ddm', 'Views', 'Storage'];
            $typeFolders   = $packageType === 'theme'
                ? ['Components', 'Assets', 'Layouts']
                : ['Controllers', 'Media', 'Models', 'Services'];
        
            foreach (array_merge($commonFolders, $typeFolders) as $folder) {
                LtFile::path($pkgRoot)->createFolder($folder);
            }
        
            LtFile::path($pkgRoot)->createFile('LICENSE', $licenseContent);
            LtFile::path($pkgRoot)->createFile('package.json', $jsonData);
            LtFile::path($pkgRoot)->createFile('README.md', "# {$packageName}\n");
        
            // Initialize Export JSON files with valid empty JSON array '[]'
            $exportPath  = "{$pkgRoot}Export";
            $exportFiles = ['page.json', 'content.json', 'menu.json', 'table.json', 'pageContent.json', 'availableRoles.json'];
            foreach ($exportFiles as $file) {
                LtFile::path($exportPath)->createFile($file, '[]');
            }
        
            // Initialize Storage Layout JSON files with '[]'
            $storagePath       = "{$pkgRoot}Storage";
            $storageLayoutPath = "{$storagePath}/Layouts";
            LtFile::path($storagePath)->createFolder('Layouts');
            LtFile::path($storageLayoutPath)->createFile('AppliedLayouts.json', '[]');
            LtFile::path($storageLayoutPath)->createFile('PendingLayouts.json', '[]');
        }

/**
 * Returns clean, unindented MIT license text (Fixes Bug #8).
 */
private function getMitLicenseContent(): string {
    $year = date('Y');
    return <<<LICENSE
MIT License

Copyright (c) {$year}, LifetechOCMS

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
SOFTWARE.
LICENSE;
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
                    'downloadCount'     => $remotePkg['totalDownload'] ?? 0,
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
            $installWithRoles = $this->request->installWithRoles ?? null; // user permission state
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
            
                // Run framework installation logic ImportLtClass
 
                if(class_exists('\ImportLtClass')) {
                    $instance = new \ImportLtClass();
                    $result = $instance->importPackageAbstract($path, $installWithRoles);
                } else {
                    return LtResponse::json("ImportLtClass does not exist", '3322', '100');
                }
                
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
        
        public function exportPackage() {
            $packageName    = $this->request->packageName ?? null;
            $packageType    = $this->request->packageType ?? null;
            $bumpVersion    = filter_var($this->request->bumpVersion ?? false, FILTER_VALIDATE_BOOLEAN);
            $exportWithRoles = filter_var($this->request->exportWithRoles ?? false, FILTER_VALIDATE_BOOLEAN);
            $useSourceCode  = filter_var($this->request->useSourceCode ?? true, FILTER_VALIDATE_BOOLEAN);
            $exportAction   = $this->request->exportAction ?? 'download';
            $selectedTables = !empty($this->request->selectedTables) ? json_decode($this->request->selectedTables, true) : [];
        
            if (empty($packageName) || empty($packageType)) {
                return LtResponse::json('PackageName and PackageType is required', 3010, 100);
            }
        
            if (!class_exists('ZipArchive')) {
                return LtResponse::json('ZipArchive is not Activated on your PHP Configuration. You need to Activate ZipArchive on your PHP Configuration! RETRY', 1999, 100);
            }
            
            if(class_exists('\ImportLtClass')) {
                $instance = new \ImportLtClass();
               $response = $instance->exportPackageAbstract($packageName, $packageType, $useSourceCode, $exportAction, $selectedTables, $bumpVersion, $exportWithRoles);
               if($response){
                   return $response;
               }
            } else {
                $msg =  LtResponse::json("ImportLtClass does not exist", '3322', '100');
                return $msg;
            } 
 
        }
        
        
        // public function zipFolder($folderPath, $zipFilePath){
        //         if (!extension_loaded('zip')) {
        //             return [
        //                 "status" => false,
        //                 "message" => "PHP ZipArchive extension is not enabled"
        //             ];
        //         }
            
        //         if (!is_dir($folderPath)) {
        //             return [
        //                 "status" => false,
        //                 "message" => "Folder does not exist"
        //             ];
        //         }
            
        //         $zip = new ZipArchive();
            
        //         if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        //             return json_encode([
        //                 "responseCategory" => 100,
        //                 "responseResult" => "Could not create zip file"
        //             ]);
        //         }
            
        //         $folderPath = realpath($folderPath);
            
        //         $files = new RecursiveIteratorIterator(
        //             new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
        //             RecursiveIteratorIterator::SELF_FIRST
        //         );
            
        //         foreach ($files as $file) {
        //             $filePath = $file->getRealPath();
            
        //             // Path inside zip
        //             $relativePath = substr($filePath, strlen($folderPath) + 1);
            
        //             if ($file->isDir()) {
        //                 $zip->addEmptyDir($relativePath);
        //             } else {
        //                 $zip->addFile($filePath, $relativePath);
        //             }
        //         }
            
        //         $zip->close();
                
        //         if (!file_exists($zipFilePath)) {
        //             header("Content-Type: application/json");
                
        //             return json_encode([
        //                 "responseCategory" => 100,
        //                 "responseResult" => "Zip file was not created"
        //             ]);
                
        //             exit;
        //         }
                
        //         // Clean previous output to prevent corrupt zip
        //         if (ob_get_length()) {
        //             ob_clean();
        //         }
                
        //         header("Content-Type: application/zip");
        //         header("Content-Disposition: attachment; filename=\"" . basename($zipName) . "\"");
        //         header("Content-Length: " . filesize($zipFilePath));
        //         header("Cache-Control: no-cache");
        //         header("Pragma: no-cache");
        //         header("Expires: 0");
                
        //         readfile($zipFilePath);
                
        //         // Delete temporary zip after download
        //         unlink($zipFilePath);
                
        //         exit;
            
                
        //     }
        
        public function importPackage() {
            $installWithRoles = $this->request->installWithRoles ?? null; // user permission state
            $path = "storage/tmp/import/";
        
            // If file is provided, this is the initial upload, to avoid cleaning import folder when requesting confirmation from user
            if (isset($_FILES['file']['tmp_name'])) {
                $tmp_name = $_FILES['file']['tmp_name'];
                $cleanPath = "storage/tmp/"; 
                 
                LtFile::path($cleanPath)->unlink('import', 'no');
                 
                if (!class_exists('ZipArchive')) {
                    return LtResponse::json('ZipArchive is not Activated on your PHP Configuration.', 1999, 100);
                }
                 
                $zip = new ZipArchive;
                $res = $zip->open($tmp_name); 
                 
                if ($res === TRUE) { 
                    $zip->extractTo($path);
                    $zip->close();
                } else {
                    return LtResponse::json('Failed to extract package archive.', 1998, 100);
                }
            }
        
            if (class_exists('\ImportLtClass')) {
                $instance = new \ImportLtClass();
                $result = $instance->importPackageAbstract($path, $installWithRoles);
            } else {
                return LtResponse::json("ImportLtClass does not exist", '3322', '100');
            } 
        
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
                        if(!$dependency['isInstalled']){
                            $packageName = $dependency['packageName'];
                             $failedDependency[] = "please check [$packageName] if this dependency is installed";
                             
                        }elseif(!$dependency['isPublished']){
                            $packageName = $dependency['packageName'];
                             $failedDependency[] = "please check [$packageName] if this dependency is published";
                             
                        }elseif(!$dependency['isUpdated']){
                            $packageName = $dependency['packageName'];
                             $failedDependency[] = "please check [$packageName] if this dependency is updated";
                             
                        }
                        
                    }
                    
                    $failedDependencyString = implode(', ', $failedDependency);
                    
                    return LtResponse::json("Failed to publish; $failedDependencyString", '3319', '100');
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
        
        public function isDefault($model) {
            $ltId        = trim($this->request->ltId ?? '');
            $isDefault   = (string) ($this->request->isThemeDefault ?? '');
            $packageName = trim($this->request->packageName ?? '');
            $packageType = trim($this->request->packageType ?? '');
            $updatedBy   = LtSession::get('ltUid');
        
            // =========================================================================
            // PHASE 1: Validation & Layout Verification (Read-Only)
            // =========================================================================
        
            // 1. Validate request parameters
            if ($isDefault !== '1') {
                return LtResponse::json("Invalid request: theme default flag must be set to 1.", '3310', '100');
            }
        
            if (empty($ltId) || empty($packageName) || $packageType !== 'theme') {
                return LtResponse::json("Invalid package parameters provided. Package type must be 'theme'.", '3311', '100');
            }
        
            try {
                // 2. Fetch package and verify existence, DB type, and publish state
                $stmtPkg = $this->sqlConnect->prepare("
                    SELECT lt_id, package_name, package_type, is_published, is_theme_default 
                    FROM tb_package 
                    WHERE lt_id = :ltId AND package_name = :packageName
                    LIMIT 1
                ");
                $stmtPkg->execute([
                    ':ltId'        => $ltId,
                    ':packageName' => $packageName
                ]);
                $packageRecord = $stmtPkg->fetch(\PDO::FETCH_OBJ);
        
                if (!$packageRecord || $packageRecord->package_type !== 'theme') {
                    return LtResponse::json("The specified theme package does not exist.", '3311', '100');
                }
        
                if ((string) $packageRecord->is_published === '0') {
                    return LtResponse::json("The selected theme cannot be set as default because it is unpublished.", '3312', '100');
                }
        
                // 3. Verify Default.html exists in both tb_content and the filesystem
                $stmtLayout = $this->sqlConnect->prepare("
                    SELECT lt_id, content_name 
                    FROM tb_content 
                    WHERE package_name = :packageName 
                      AND content_name = :contentName 
                      AND theme_resource_type = :resourceType
                    LIMIT 1
                ");
                $stmtLayout->execute([
                    ':packageName'  => $packageName,
                    ':contentName'  => 'Default.html',
                    ':resourceType' => 'Layout'
                ]);
                $defaultLayout = $stmtLayout->fetch(\PDO::FETCH_OBJ);
        
                if (!$defaultLayout) {
                    return LtResponse::json("Default.html content record not found. Please create one.", '3315', '100');
                }
        
                $path = "packages/{$packageType}s/{$packageName}/Layouts/Default.html";
                if (!file_exists($path)) {
                    return LtResponse::json("Default.html file not found on disk. Please create one.", '3316', '100');
                }
        
                // =====================================================================
                // PHASE 2: Atomic Database Update (Inside Transaction)
                // =====================================================================
                $this->sqlConnect->beginTransaction();
        
                // 4. Single-Query Atomic Toggle on tb_package (unique placeholder names for PDO compatibility)
                $stmtTogglePkg = $this->sqlConnect->prepare("
                    UPDATE tb_package
                    SET is_theme_default = CASE WHEN lt_id = :targetLtId1 THEN 1 ELSE 0 END,
                        updated_by       = CASE WHEN lt_id = :targetLtId2 THEN :updatedBy ELSE updated_by END,
                        updated_at       = CASE WHEN lt_id = :targetLtId3 THEN :updatedAt ELSE updated_at END
                    WHERE package_type = :packageType
                ");
                $stmtTogglePkg->execute([
                    ':targetLtId1' => $ltId,
                    ':targetLtId2' => $ltId,
                    ':targetLtId3' => $ltId,
                    ':updatedBy'   => $updatedBy,
                    ':updatedAt'   => $this->ltNow,
                    ':packageType' => 'theme'
                ]);
        
                // 5. Clean up legacy theme region records in tb_page_content (to be removed later)
                $stmtDeleteRegions = $this->sqlConnect->prepare("
                    DELETE FROM tb_page_content 
                    WHERE content_type = :contentType 
                      AND publish_source = :publishSource
                ");
                $stmtDeleteRegions->execute([
                    ':contentType'   => 'theme',
                    ':publishSource' => 'region'
                ]);
                
                
                // 6. Apply 'Default.html' and package name to non-theme pages and to only page type theme
                $stmtUpdatePages = $this->sqlConnect->prepare("
                    UPDATE tb_page 
                    SET theme_layout         = :themeLayout,
                        theme_layout_package = :themeLayoutPackage,
                        updated_by           = :updatedBy,
                        updated_at           = :updatedAt
                    WHERE page_type = :pageType
                      AND (created_from != :excludedSource OR created_from IS NULL)
                ");
                $stmtUpdatePages->execute([
                    ':themeLayout'        => $defaultLayout->content_name,
                    ':themeLayoutPackage' => $packageName,
                    ':updatedBy'          => $updatedBy,
                    ':updatedAt'          => $this->ltNow,
                    ':pageType'           => 'theme',
                    ':excludedSource'     => 'theme'
                ]);
        
                // 7. Commit transaction and return response
                $this->sqlConnect->commit();
        
                return LtResponse::json(
                    "Package theme set to default successfully.",
                    '3313',
                    '200',
                    ['isThemeDefault' => 1]
                );
        
            } catch (\Throwable $e) {
                if ($this->sqlConnect->inTransaction()) {
                    $this->sqlConnect->rollBack();
                }
                return LtResponse::error("Failed to set theme to default: " . $e->getMessage(), '3314', '100');
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

