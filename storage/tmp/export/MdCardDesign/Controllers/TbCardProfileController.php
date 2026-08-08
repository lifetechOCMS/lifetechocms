<?php

namespace Lt\Modules\MdCardDesign\Controllers;

use Lt\Modules\MdCardDesign\Models\TbCardProfile;
use Lt\Modules\MdCardDesign\Services\TbCardProfileService;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
        
class TbCardProfileController
{

    public function index(){
        $service = new TbCardProfileService();
        $rows = $service->index();
        return LtResponse::json('success', 201, 200, $rows);
    }

    public function store(){
        ob_end_clean();

        $model = new TbCardProfile();
        $request = new LtRequest;
        $formType = $request->_formType;
        foreach($request as $key => $value){
            $model->$key = $value;
        }

        $service = new TbCardProfileService();
        $result = $service->store($model);
        
        if($formType === 'webview'){
            $responseResult = $model->responseResult;
            LtNavigate::to('CardProfileCreate.html', 'mdCardDesign')->withData('cardProfileCreate', $responseResult)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function show(){
        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbCardProfileService();
        $row = $service->show($ltId);

        return LtResponse::json('success', 201, 200, $row);
    }

    public function edit(){
        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbCardProfileService();
        $row = $service->show($ltId);

        $formType = $request->_formType;
        if($formType === 'webview'){
            LtNavigate::to('EditCardProfile.html', 'mdCardDesign')->withData('editCardProfile', $row)->redirect();
            exit();
        }

        return LtResponse::json('success', 201, 200, $row);
    }

    public function update(){
        ob_end_clean();

        $model = new TbCardProfile();
        $request = new LtRequest;
        $ltId = $request->ltId;
        $model->sectionId = $request->assignTo;
        $formType = $request->_formType;
        
        foreach($request as $key => $value){
            $model->$key = $value;
        }
    
     
        $service = new TbCardProfileService();
        $result = $service->update($model, $ltId);

        if($formType === 'webview'){
            $responseResult = $model->responseResult;
            LtNavigate::to('EditCardProfile.html', 'mdCardDesign')->withData('editCardProfileReturnMessage', $responseResult)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function toggleEnabled(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;
        $formType = $request->_formType;

        $service = new TbCardProfileService();
        $result = $service->toggleEnabled($ltId);

        if($formType === 'webview'){
            LtNavigate::to('CardProfiles.html', 'mdCardDesign')->withData('toggleCardProfile', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function toggleFeatured(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;
        $formType = $request->_formType;

        $service = new TbCardProfileService();
        $result = $service->toggleFeatured($ltId);

        if($formType === 'webview'){
            LtNavigate::to('CardProfiles.html', 'mdCardDesign')->withData('toggleCardProfileFeatured', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function destroy(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;
        $formType = $request->_formType;
        
        $service = new TbCardProfileService();
        $result = $service->destroy($ltId);
        
        if($formType === 'webview'){
            LtNavigate::to('DeleteCardProfile.html', 'mdCardDesign')->withData('deleteCardProfileReturnMessage', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }
}

