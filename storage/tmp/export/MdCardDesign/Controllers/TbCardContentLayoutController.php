<?php

namespace Lt\Modules\MdCardDesign\Controllers;

use Lt\Modules\MdCardDesign\Models\TbCardContentLayout;
use Lt\Modules\MdCardDesign\Services\TbCardContentLayoutService;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtNavigate;
        
class TbCardContentLayoutController
{

    public function index(){

        $service = new TbCardContentLayoutService();
        $rows = $service->index();
        return LtResponse::json('success', 201, 200, $rows);
    }

    public function store(){
        $model = new TbCardContentLayout();
        $model->processRequest();
        $formType = $model->_formType;
        $service = new TbCardContentLayoutService();
        $result = $service->store($model);

        if($formType === 'webview'){
            $responseResult = $model->responseResult;
            LtNavigate::to('CardContentLayoutCreate.html', 'mdCardDesign')->withData('cardContentLayoutCreate', $responseResult)->redirect();
        }else{
            return $result;
        }

    }

    public function show(){
        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbCardContentLayoutService();
        $row = $service->show($ltId);

        return LtResponse::json('success', 201, 200, $row);
    }

    public function edit(){
        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbCardContentLayoutService();
        $row = $service->show($ltId);

        $formType = $request->_formType;
        if($formType === 'webview'){
            LtNavigate::to('EditCardContentLayout.html', 'mdCardDesign')->withData('editCardContentLayout', $row)->redirect();
            exit();
        }

        return LtResponse::json('success', 201, 200, $row);
    }

    public function update(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;
        $formType = $request->_formType;

        $model = new TbCardContentLayout();
        $model->packageName = $request->packageName;   // nullable
        $model->contentName = $request->contentName;
        $model->cardSectionId = $request->cardSectionId; // nullable
        $model->userGuide = $request->userGuide; // usage_note

        $service = new TbCardContentLayoutService();
        $result = $service->update($model, $ltId);

        if($formType === 'webview'){
            $responseResult = $model->responseResult;
            LtNavigate::to('EditCardContentLayout.html', 'mdCardDesign')->withData('editCardContentLayoutReturnMessage', $responseResult)->redirect();
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

        $service = new TbCardContentLayoutService();
        $result = $service->toggleEnabled($ltId);

        if($formType === 'webview'){
            LtNavigate::to('CardContentLayout.html', 'mdCardDesign')->withData('toggleCardContentLayout', $result)->redirect();
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

        $service = new TbCardContentLayoutService();
        $result = $service->destroy($ltId);

        if($formType === 'webview'){
            LtNavigate::to('DeleteCardContentLayout.html', 'mdCardDesign')->withData('deleteCardContentLayoutReturnMessage', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }
    

    public function renderPackage(){
        $request = new LtRequest;
        $packageName = $request->packageName;
        $contentName = $request->contentName;

        $service = new TbCardContentLayoutService();
        return $service->renderPackage($packageName, $contentName);
    }

    /* ===== Resolve endpoints ===== */

    public function resolveDefault(){
        $request = new LtRequest;
        $contentName = $request->contentName;

        $service = new TbCardContentLayoutService();
        return $service->resolveDefault($contentName);
    }

    public function resolvePackage(){
        $request = new LtRequest;
        $packageName = $request->packageName;
        $contentName = $request->contentName;

        $service = new TbCardContentLayoutService();
        return $service->resolvePackage($packageName, $contentName);
    }
    
    public function assignSection(){

        $service = new TbCardContentLayoutService();
        return $service->assignSection();
    }
    
}

