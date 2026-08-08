<?php
namespace Lt\Modules\MdCardDesign\Controllers;

use Lt\Modules\MdCardDesign\Services\TbCardSectionService;
use Lt\Modules\MdCardDesign\Models\TbCardSection;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtNavigate;
        
class TbCardSectionController
{

    public function index(){
        $service = new TbCardSectionService();
        $rows = $service->index();
        return LtResponse::json('success', 201, 200, $rows);
    }

    public function store(){
        ob_end_clean();

        $model = new TbCardSection();
        $model->processRequest();
        $formType = $model->_formType;

        $service = new TbCardSectionService();
        $result = $service->store($model);

        if($formType === 'webview'){
            $responseResult = $model->responseResult;
            LtNavigate::to('CardSectionCreate.html', 'mdCardDesign')->withData('cardSectionCreate', $responseResult)->redirect();
        }else{
            return $result;
        }

        exit();
    }

    public function show(){
        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbCardSectionService();
        $row = $service->show($ltId);

        return LtResponse::json('success', 201, 200, $row);
    }

    public function edit(){
        $request = new LtRequest;
        $ltId = $request->ltId;

        $service = new TbCardSectionService();
        $row = $service->show($ltId);

        // For webview, you can redirect to edit page with loaded data
        $formType = $request->_formType;
        if($formType === 'webview'){
            LtNavigate::to('EditCardSection.html', 'mdCardDesign')->withData('editCardSection', $row)->redirect();
            exit();
        }

        return LtResponse::json('success', 201, 200, $row);
    }

    public function update(){
        ob_end_clean();

        $request = new LtRequest;
        $ltId = $request->ltId;
        $formType = $request->_formType;

        $model = new TbCardSection();
        $model->name = $request->name;
        $model->slug = $request->slug;
        $model->description = $request->description;

        $service = new TbCardSectionService();
        $result = $service->update($model, $ltId);

        if($formType === 'webview'){
            $responseResult = $model->responseResult;
            LtNavigate::to('EditCardSection.html', 'mdCardDesign')->withData('editCardSectionReturnMessage', $responseResult)->redirect();
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

        $service = new TbCardSectionService();
        $result = $service->toggleEnabled($ltId);

        if($formType === 'webview'){
            LtNavigate::to('CardSections.html', 'mdCardDesign')->withData('toggleCardSection', $result)->redirect();
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

        $service = new TbCardSectionService();
        $result = $service->destroy($ltId);

        if($formType === 'webview'){
            LtNavigate::to('DeleteCardSection.html', 'mdCardDesign')->withData('deleteCardSectionReturnMessage', $result)->redirect();
        }else{
            return $result;
        }

        exit();
    }
}

