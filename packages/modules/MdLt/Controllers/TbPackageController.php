<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbPackage;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtService;
use Lt\Modules\MdLt\Services\LtFile;
use Lt\Modules\MdLt\Services\TbPackageService;
use Lt\Modules\MdLt\Services\TbContentService;
use Lt\Modules\MdLt\Services\TbPageContentService;
use Lt\Modules\MdLt\Services\TbPageService;
use Lt\Modules\MdLt\Services\TbMenuService;


class TbPackageController
{
    
    public function index(){
        
        
        $packageModel = new TbPackage();
        $packageModel->select();

        $request = new LtRequest();

        // Collect query parameters
        $packageName = $request->package_name ?? null;
        $packageType = $request->package_type ?? null;
        
        
        $isPublished = LtService::getIsValue($request->is_published ?? null);

        // Build conditions dynamically
        $hasCondition = false;

        if (!empty($packageName)) {
            $packageModel->where('package_name', '=', $packageName);
            $hasCondition = true;
        }

        if (!empty($packageType)) {
            // if there was a previous where, use andWhere
            if ($hasCondition) {
                $packageModel->andWhere('package_type', '=', $packageType);
            } else {
                $packageModel->where('package_type', '=', $packageType);
                $hasCondition = true;
            }
        }

        if ($isPublished !== null) {
            if ($hasCondition) {
                $packageModel->andWhere('is_published', '=', $isPublished);
            } else {
                $packageModel->where('is_published', '=', $isPublished);
                $hasCondition = true;
            }
        }

        // Default ordering
        $packageModel->orderBy('package_name', 'ASC');

        // Execute query
        $packageModel->get();
        ob_end_clean();
        return LtResponse()::json('success', 3301, 200, $packageModel->responseData);
    }


    public function store(){

        $packageModel = new TbPackage();
        $packageModel->processRequest();

        $packageModel->validateRequest([
            'packageName' => 'required'
        ]);

        $dataModelService = new TbPackageService();
        $response = $dataModelService->store($packageModel);

        return $response;

    }

    public function update(){
        $packageModel = new TbPackage();
        $packageModel->processRequest(['dependsOn', 'description', 'isProtected','isPublished','readme', 'version', 'ltId', 'languageUsed']);

        $dataModelService = new TbPackageService();
        $response = $dataModelService->updatePackage($packageModel);

        return $response;

    }

    public function togglePublished(){
        $packageModel = new TbPackage();

        $dataModelService = new TbPackageService();
        $response = $dataModelService->togglePublish($packageModel);

        return $response;


    }
    public function toggleDefault(){
        $packageModel = new TbPackage();

        $dataModelService = new TbPackageService();
        $response = $dataModelService->isDefault($packageModel);

        return $response;


    }

    public function destroy(){
        $packageModel = new TbPackage();
        $packageModel->processRequest();

        $ltId =  $packageModel->ltId;
        $packageName =  $packageModel->packageName;
        $packageType =  $packageModel->packageType;

        $packageModel->select()->where('ltId', '=', $ltId)->andWhere('isProtected', '1')->get();
        if($packageModel->responseCategory === '200'){
            $packageName = $packageModel->responseData[0]->$packageName;
            return LtResponse()::json("Package [ $packageName ] cannot be deleted because it is Protected", "3302");
        } else {
           
         
            
            // / delete from other table
            // PageContent Service
            $pageContentModel = new TbPageContentService(); 
            $deletePageContent = $pageContentModel->deletePageContent(packageName: $packageName);
            //  Content Service 
            $contentModel = new TbContentService(); 
            $deleteContent = $contentModel->deleteContent(packageName: $packageName);
            // // // Page service
            if($packageType == 'module' || $packageType == 'plugin'){
                $pageModel = new TbPageService(); 
                $deletePage = $pageModel->deletePages(params: $packageName);
                // // // Menu Service
                $menuModel = new TbMenuService(); 
                $deleteMenu = $menuModel->deleteMenu(params: $packageName);

            }
            /// delete folder
            $packageModel->delete('ltId', '=', $ltId);
            
            $path="packages/".$packageType.'s/';
            $yeah = LtFile::path($path)->deleteFolder($packageName);
            return $packageModel->responseJson();
            
            
        }

    }
    
    public function exportTables(){
        
        $dataModelService = new TbPackageService();
        $response = $dataModelService->exportTable();
        
        return $response;
        
    }
    public function exports(){
        
        $dataModelService = new TbPackageService();
        $response = $dataModelService->exportPackage();
        
        return $response;
        
    }
    public function imports(){
        
        $dataModelService = new TbPackageService();
        $response = $dataModelService->importPackage();
        
        return $response;
        
    }
    public function filteredPackages(){
        
        $dataModelService = new TbPackageService();
        $response = $dataModelService->filteredPackages();
        
        return $response;
        
    }

    
}
