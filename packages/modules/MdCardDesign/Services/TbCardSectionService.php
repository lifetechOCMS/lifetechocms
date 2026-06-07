<?php
namespace Lt\Modules\MdCardDesign\Services;

use Lt\Modules\MdCardDesign\Models\TbCardSection;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
        
class TbCardSectionService
{
    protected $sectionModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->sectionModel = new TbCardSection();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
    }

    public function index(){
        $isEnabled = $this->request->isEnabled;
        $searchQuery = $this->request->searchQuery;

        $query = $this->sectionModel->select();

        if($isEnabled !== null && $isEnabled !== ''){
            $query->where('isEnabled','=', $isEnabled);
        }

        if(!empty($searchQuery)){
            $query->where('name','LIKE','%'.$q.'%');
        }

        return $query->get();
    }

    public function store($model){
        $name = trim((string)$model->name);
        if($name === ''){
            return LtResponse::json('Section name is required', 3004, 100);
        }

        $model->ltId = $this->ltId;
        $model->createdBy = $this->userId;
        $model->updatedBy = $this->userId;
        $model->createdAt = $this->ltNow;
        $model->updatedAt = $this->ltNow;

        if($model->isEnabled === null || $model->isEnabled === ''){
            $model->isEnabled = '1';
        }

        $model->insert();
        return $model->responseJson();
    }

    public function show($ltId){
        $row = $this->sectionModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Section not found', 3004, 100);
        }
        return $row[0];
    }

    public function update($model, $ltId){
        $exist = $this->sectionModel->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Section not found', 3004, 100);
        }

        $model->updatedBy = $this->userId;
        $model->updatedAt = $this->ltNow;

        $model->update('ltId','=',$ltId);
        return $model->responseJson();
    }

    public function toggleEnabled($ltId){
        $row = $this->sectionModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Section not found', 3004, 100);
        }

        $current = $row[0]->isEnabled;
        $newVal = ($current == '1') ? '0' : '1';

        $this->sectionModel->isEnabled = $newVal;
        $this->sectionModel->updatedBy = $this->userId;
        $this->sectionModel->updatedAt = $this->ltNow;
        $this->sectionModel->update('ltId','=',$ltId);

        return LtResponse::json('Updated', 201, 200, ['ltId'=>$ltId,'isEnabled'=>$newVal]);
    }

    public function destroy($ltId){
        $exist = $this->sectionModel->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Section not found', 3004, 100);
        }

        $this->sectionModel->delete('ltId','=',$ltId);
        return LtResponse::json('Deleted', 201, 200);
    }
}

