<?php
namespace Lt\Modules\MdLt\Controllers;

use Lt\Modules\MdLt\Models\TbSite;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtResponse;




class TbSiteController
{
    
    public function getSite(){
        
        $siteInfoModel = new TbSite();
        $request = new LtRequest;
        $fetchAll = $request->fetchAll;
        $record = $siteInfoModel->select()->get();
        
        if($siteInfoModel->responseCategory == 200){
            if($fetchAll){
                $data = $record;
            }else{
                
                $data = [
                            'siteAlias' => $record[0]->siteAlias, 
                            'logo' => $record[0]->logo, 
                            "siteHostAddress"=> $record[0]->siteHostAddress, 
                            'pageTitle' => $record[0]->pageTitle,
                            'favicon' => $record[0]->favicon,
                            'siteCopyright' => $record[0]->siteCopyright
                        ];
            }
        }else{
            $data = [];
        }
    
        return  LtResponse::json($siteInfoModel->responseResult, $siteInfoModel->responseCode, $siteInfoModel->responseCategory, $data); 
    }
    
    public function updateSiteInfo(){
        $siteInfoModel = new TbSite();
        $siteInfoModel->processRequest();
        $sn = $siteInfoModel->sn;
        $siteInfoModel->update('sn', '=', $sn);
        return $siteInfoModel->responseJson();
    }

}