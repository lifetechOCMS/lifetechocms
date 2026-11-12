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

/**
 *  RESPONSE CODE 3300 - 3399
 */ 


class TbPackageController
{
    
    public function index(){
        
        $request = new LtRequest();

        // Collect query parameters
        $packageName = trim($request->package_name) ?? null;
        $packageType = strtolower(trim($request->package_type)) ?? null;
        
        
        $isPublished = LtService::getIsValue($request->is_published ?? null);
        
        $dataModelService = new TbPackageService();
        $response = $dataModelService->index([
                'packageName' => (string) $packageName,
                'packageType'       => (string) $packageType,
                'isPublished'     => $isPublished
            ]);
        return $response;
        
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

        $packageExist = $packageModel->select()->where('ltId', '=', $ltId)->get()[0];
        
        if(empty($packageExist)) return LtResponse()::json("Invalid Package Id", "3302");
        
        $packageName = $packageExist->packageName;
        $packageType = $packageExist->packageType;
        $isThemeDefault = $packageExist->isThemeDefault;
        $isProtected = $packageExist->isProtected;
        

        if($isProtected == '1'){
             return LtResponse()::json("Package [ $packageName ] cannot be deleted because it is Protected", "3302");
        }
        
        if($isThemeDefault == '1'){
             return LtResponse()::json("Package [ $packageName ] cannot be deleted because it is default", "3302");
        }
        
            // / delete from other table
            // PageContent Service
            $pageContentModel = new TbPageContentService(); 
            $deletePageContent = $pageContentModel->deletePageContent(packageName: $packageName);
            //  Content Service 
            $contentModel = new TbContentService(); 
            $deleteContent = $contentModel->deleteContent(packageName: $packageName);
            // // // Page service
            $pageModel = new TbPageService(); 
            $deletePage = $pageModel->deletePages(params: $packageName);
            // // // Menu Service
            $menuModel = new TbMenuService(); 
            $deleteMenu = $menuModel->deleteMenu(params: $packageName);
            /// delete folder
            $packageModel->delete('ltId', '=', $ltId);
            
            $path="packages/".$packageType.'s/';
            $yeah = LtFile::path($path)->deleteFolder($packageName);
            return $packageModel->responseJson();
            

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
    
    public function validateDependencies(){
        
        $dataModelService = new TbPackageService();
        $response = $dataModelService->validateDependency();
        
        return $response;
        
    }
    
    public function browseProducts(){
        
        $dataModelService = new TbPackageService();
        $response = $dataModelService->browseProductFromMarkePlace();
        
        return $response;
        
    }
    
    public function installUpdate(){
        
        $dataModelService = new TbPackageService();
        $response = $dataModelService->installFromMarketplace();
        
        return $response;
        
    }

    
}
