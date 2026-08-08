<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbSoftwareUpdate;
use Lt\Modules\MdLt\Models\TbRole;
use Lt\Modules\MdLt\Services\LtService;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;

use Lt\Modules\MdLt\Models\TbPackage;

use LtDdm;

class TbSoftwareUpdateController
{
    
    public function index(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $softwareUpdateModel->select('editorId, content_name, ccurrent, lastupdate, content_work, route_path, download_status, depends_on')->get();

        return $softwareUpdateModel->responseJson();
    }
    public function loadCode(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $request = new LtRequest;
        $contentName = $request->contentName;
        $result = LtDdm::ltBackendLayer1Loader($contentName);
        return $result;
    //   $result = $softwareUpdateModel->select('content')->where('content_name', '=', $contentName)->get();
    //     return $softwareUpdateModel->responseJson();
    }
    public function safeCode(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $request = new LtRequest;
        $contentName = $request->contentName;
        $codeContent = $request->codeContent;
        
        $result = LtDdm::ltBackendLayer1Saver($contentName,$codeContent);
        return $result;

    }
    public function updateStatus(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $request = new LtRequest;
        
        $contentName = $request->contentName;
        $updateStatus = $request->updateStatus;
        $packageDepends = $request->packageDepends;
        
        $softwareUpdateModel->dependsOn = json_encode($packageDepends);
        $softwareUpdateModel->downloadStatus = $updateStatus;
        
        // update last update datetime when update status is 1
        if($updateStatus == 1){
            $softwareUpdateModel->lastupdate = date('Y-m-d H:i:s');
        }
        $softwareUpdateModel->update('contentName', '=', $contentName);
        return $softwareUpdateModel->responseJson();
    }
    
    function validatePackageDetails($contentName){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $packageModel = new TbPackage();
        // $contentName = "Role_To_Login_121";
        //$contentName = "f47430484201d38c4c87e03aae5eb94a";
        $package = [
            'status'  => false,
            'priority' => 'low',
            'package' => ''
        ];
        $messages = [];
        
        $existContent = $softwareUpdateModel->select('depends_on')->where('contentName', '=', $contentName)->get()[0];
        $content = json_decode($existContent->dependsOn, true);

        foreach ($content as $c) {
            $packageName     = $c['packageName'] ?? '';
            $packageVersion  = $c['packageVersion'] ?? '';
            $packagePriority = $c['priority'] ?? 'low';
            $existPackage = $packageModel->select('package_name, version, package_type')->where('packageName', '=', $packageName)->get()[0];
            
            // package not installed
            if (!$existPackage) {
        
                $messages[] = "Package {$packageName} is required to be INSTALLED with version {$packageVersion}+ ";
    
                $package['status'] = true;
    
                if ($packagePriority === 'high') {
                    $package['priority'] = 'high';
                }
    
                continue;
            }
            
            $installedVersion = ltrim($existPackage->version, 'v'); // remove leading "v" from version e.g v2.5 to 2.5
            if (version_compare($installedVersion, $packageVersion, '<')) {

                $messages[] = "Package {$packageName} requires version {$packageVersion} or higher";
    
                $package['status'] = true;
    
                if ($packagePriority === 'high') {
                    $package['priority'] = 'high';
                }
            }
        }
        
        $package['package'] = implode('<br>', $messages);

        return json_encode($package);
    }
    
     public function checkUpdate(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $request = new LtRequest;
        $lastUpdated = $request->lastUpdated;
        $contentName = $request->contentName;
        $softwareUpdateModel->select('lastupdate, content_name, content, dependsOn');
        
        if(!empty($contentName)){
            $updatedRecord = $softwareUpdateModel->select('lastupdate, content_name, content, depends_on')->where('content_name', '=', $contentName)->andWhere('downloadStatus', '=', 1)->get()[0];
        }else{
            $updatedRecord = $softwareUpdateModel->select('lastupdate, content_name, content, depends_on')->where('lastupdate', '>', $lastUpdated)->andWhere('downloadStatus', '=', 1)->get();
        }
        
        if(!empty($updatedRecord)){
            return LtResponse::json('success', 205, 200, $updatedRecord);
        }else{
            return LtResponse::json('failed', 106, 100);
        }
    }
     public function checkAllUpdate(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $updatedRecord = $softwareUpdateModel->select('lastupdate, content_name, download_status, depends_on')->get();
        if(!empty($updatedRecord)){
            return LtResponse::json('success', 205, 200, $updatedRecord);
        }else{
            return LtResponse::json('failed', 106, 100);
        }
    }
     public function backendPermision(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $roleModel = new TbRole();
        $roles = $roleModel->select('lt_id')->where('isEnabled', '=', 1)->get();
        $roleId = [];
        foreach($roles as $id){
            $roleId[] = $id->ltId;
        };

        $roleString = implode(',', $roleId);
        $result = $softwareUpdateModel->select("content_name, content_work, {$roleString}")->get();

        return $softwareUpdateModel->responseJson();
        
    }
    
    public function toggleRole(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $softwareUpdateModel->processRequest(); 
        $contentName = $softwareUpdateModel->contentName;
        $roleId = $softwareUpdateModel->roleId;

        $publishValue = LtService::getIsValue($softwareUpdateModel->publishValue);
        
        // toggle role publishing for content
        $softwareUpdateModel->updatedBy = LtSession::get('ltUid');
        $softwareUpdateModel->$roleId = $publishValue;
        $softwareUpdateModel->update('contentName', '=', $contentName);
    
        
        return $softwareUpdateModel->responseJson();  

        
    }

    public function loadUpdate(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $request = new LtRequest;
        $serverLastUpdate = $request->serverLastUpdate ?? [];
        
        foreach ($serverLastUpdate as $server) {
            if (!isset($server['contentName'])) {
                continue;
            }
    
            $serverMap[$server['contentName']] = [
                'lastUpdate'     => $server['lastupdate'] ?? null,
                'downloadStatus' => $server['downloadStatus'] ?? 0
            ];
        }
        
        $contents = $softwareUpdateModel->select('editorId, content_name, lastupdate, content_work, depends_on')->get();
        $newContent = [];
        
        foreach($contents as $content){
            $contentName = $content->contentName;
             $serverLast = $serverMap[$contentName]['lastUpdate'];
             $downloaded = $serverMap[$contentName]['downloadStatus'];
            
            if(isset($serverLast)){
                if($content->lastupdate < $serverLast){
                    $updateStatus = ($downloaded == 1) ? 0 : 2; 
                }else{
                    $updateStatus = 1; 
                }
                // $updateStatus = ($content->lastupdate < $serverMap[$contentName]) ? 0 : 1;
                $newContent[] = [
                    'editorId' => $content->editorId,
                    'contentName' => $content->contentName,
                    'lastupdate' => $content->lastupdate,
                    'contentWork' => $content->contentWork,
                    'updateStatus' => $updateStatus,
                    'onlineUpdate' => $serverLast,
                    'dependsOn' => $content->dependsOn
                    ];
            }

        }
        
        if(!empty($newContent)){
            return LtResponse::json('success', 200, 200, $newContent);
        }else{
            return LtResponse::json('failed', 104, 100);
        }
        
    }
    
    public function update(){
        $softwareUpdateModel = new TbSoftwareUpdate();
        $request = new LtRequest;
        $content = $request->content;
        $lastupdate = $request->lastupdate;
        $contentName = $request->contentName;
        
        $softwareUpdateModel->content = $content;
        $softwareUpdateModel->lastupdate = $lastupdate;
        $softwareUpdateModel->update('contentName', '=', $contentName);
        
        return $softwareUpdateModel->responseJson();
    }
    
    //  $sqlConn = DbConnect::dbDriver();
    //     $sql = "SELECT content_name FROM bked_content_editor";
    //     $stmt = $sqlConn->prepare($sql);
    //     $stmt->execute();
    //     $result = $stmt->fetchAll(PDO::FETCH_OBJ);    
    //     // $result = $stmt->fetchAll();    
        
    //     return json_encode($result);
    
}