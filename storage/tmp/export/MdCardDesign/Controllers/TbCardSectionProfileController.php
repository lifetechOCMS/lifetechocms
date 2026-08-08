<?php

namespace Lt\Modules\MdCardDesign\Controllers;

use Lt\Modules\MdCardDesign\Models\TbCardSectionProfile;
use Lt\Modules\MdCardDesign\Services\TbCardSectionProfileService;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtNavigate;
        
class TbCardSectionProfileController
{

    public function index(){

        $service = new TbCardSectionProfileService();
        $rows = $service->index();

        // service may return LtResponse::json on error, so just return it
        // if(is_object($rows) && property_exists($rows,'code')) return $rows;
        return $rows;
        
    }

    public function attach(){
        ob_end_clean();

        $request = new LtRequest;
        $formType = $request->_formType;

         $data = [
            'sectionId' => $request->sectionId,
            'cardProfileId' => $request->profileId
        ];
        $service = new TbCardSectionProfileService();
        $result = $service->attach($data);

        if($formType === 'webview'){
            LtNavigate::to('CardSectionProfiles.html', 'mdCardDesign')->withData('attachProfileToSection', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function update(){
        ob_end_clean();

        $request = new LtRequest;
        $mapId = $request->mapId;
        $formType = $request->_formType;

        $model = new TbCardSectionProfile();
        $model->displayOrder = $request->displayOrder;
        $model->badge = $request->badge;
        $model->customTitle = $request->customTitle;
        $model->isEnabled = $request->isEnabled;

        $service = new TbCardSectionProfileService();
        $result = $service->update($model, $mapId);

        if($formType === 'webview'){
            LtNavigate::to('CardSectionProfiles.html', 'mdCardDesign')->withData('updateSectionProfileReturnMessage', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function toggleEnabled(){
        ob_end_clean();

        $request = new LtRequest;
        $mapId = $request->mapId;
        $formType = $request->_formType;

        $service = new TbCardSectionProfileService();
        $result = $service->toggleEnabled($mapId);

        if($formType === 'webview'){
            LtNavigate::to('CardSectionProfiles.html', 'mdCardDesign')->withData('toggleSectionProfile', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function detach(){
        ob_end_clean();

        $request = new LtRequest;
        $formType = $request->_formType;

        $service = new TbCardSectionProfileService();
        $result = $service->detach();

        if($formType === 'webview'){
            LtNavigate::to('CardSectionProfiles.html', 'mdCardDesign')->withData('detachSectionProfileReturnMessage', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function reorder(){
        ob_end_clean();

        $request = new LtRequest;
        $sectionId = $request->sectionId;
        $formType = $request->_formType;

        // Expecting array of items in request
        // e.g $request->items OR direct post array; adjust if your LtRequest differs
        $items = $request->items;

        $service = new TbCardSectionProfileService();
        $result = $service->reorder($sectionId, $items);

        if($formType === 'webview'){
            LtNavigate::to('CardSectionProfiles.html', 'mdCardDesign')->withData('reorderSectionProfilesReturnMessage', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }
}

