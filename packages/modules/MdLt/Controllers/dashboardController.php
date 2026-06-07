<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Services\TbPageService;
use Lt\Modules\MdLt\Services\TbPackageService;
use Lt\Modules\MdLt\Services\TbUserService;
use Lt\Modules\MdLt\Services\TbMenuService;
use Lt\Modules\MdLt\Services\TbRoleService;
use Lt\Modules\MdLt\Services\TbContentService;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;


class dashboardController
{
    
    public function dashboardIndex(){
        // page content
        $pageService = new TbPageService();
        $pagesRecord = $pageService->dashboardAnalysis();
        
        // page content
        $packageService = new TbPackageService();
        $packageRecord = $packageService->dashboardAnalysis();
        // user content
        $userService = new TbUserService();
        $userRecord = $userService->dashboardAnalysis();
        // menu content
        $menuService = new TbMenuService();
        $menuRecord = $menuService->dashboardAnalysis();
        // menu content
        $contentService = new TbContentService();
        $contentRecord = $contentService->dashboardAnalysis();
        // role content
        $rolesService = new TbRoleService();
        $rolesRecord = $rolesService->dashboardAnalysis();
        
        $result = ['pages' => $pagesRecord, 'packages' => $packageRecord, 'users' => $userRecord, 'menus' => $menuRecord, 'content' => $contentRecord, 'roles' => $rolesRecord];
    
        return  LtResponse::json('Record Fetched Successfully', 201, 200, $result); 
    }
    

    
}