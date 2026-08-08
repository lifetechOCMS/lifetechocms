<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbPageContent;
use Lt\Modules\MdLt\Services\TbPageContentService;

class TbPageContentController
{
    public function togglePublished(){
        $pageContentModel =new TbPageContent(); 
        $pageContentModel->processRequest();
        
        $pageContentService = new TbPageContentService();
        $publishedToPage = $pageContentService->publishPageContent($pageContentModel);
        return $publishedToPage;
    
    }
}