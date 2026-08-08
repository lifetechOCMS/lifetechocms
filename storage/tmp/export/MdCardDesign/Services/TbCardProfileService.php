<?php
namespace Lt\Modules\MdCardDesign\Services;

use Lt\Modules\MdCardDesign\Models\TbCardProfile;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use Lt\Modules\MdLt\Services\LtFile;
        
class TbCardProfileService
{
    protected $profileModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->profileModel = new TbCardProfile();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
    }

    public function index(){
        
        $isEnabled = $this->request->isEnabled;
        $isFeatured = $this->request->isFeatured;
        $searchQuery = $this->request->query;

        $model = $this->profileModel->select();

        if($isEnabled !== null && $isEnabled !== ''){
            $model->where('isEnabled','=', $isEnabled);
        }

        if($isFeatured !== null && $isFeatured !== ''){
            $model->where('isFeatured','=', $isFeatured);
        }

        if(!empty($searchQuery)){
            $model->where('fullName','LIKE','%'.$searchQuery.'%');
        }

        return $model->get();
    }
    
    public function processUpload($fieldName, $pathMedia){
        if (!isset($_FILES[$fieldName])) {
            return null;
        }
    
        $file = $_FILES[$fieldName];
    
        if (strpos($fieldName, 'image') === 0) {
            $newPath = $pathMedia . 'image';
        } else {
            $newPath = $pathMedia . 'video';
        }
    
        return LtFile::path($newPath)->uploadFile($file);
    }
    
    public function processField($initial = true, $model, $dbMediaPath, $uploadMediaPath){
        $availableField = [];
         $expectedFields = ['image1', 'image2', 'image3', 'image4', 'video1', 'video2'];

            foreach ($expectedFields as $field) {
                if (!empty($_FILES[$field]['name'])) {
        
                    if (strpos($field, 'image') === 0) {
                        if($initial){
                            $imageUploadPath = $uploadMediaPath . 'image';
                            if (!is_dir($imageUploadPath)) {
                                LtFile::path($uploadMediaPath)->createFolder('image');
                            }
                        }
                        $imagePath = $dbMediaPath . 'image';
                        $model->$field = $imagePath . '/' . $_FILES[$field]['name'];
        
                    } else {
                        if($initial){
                            $videoUploadPath = $uploadMediaPath . 'video';
                            if (!is_dir($videoUploadPath)) {
                                LtFile::path($uploadMediaPath)->createFolder('video');
                            }
                        }
                        $videoPath = $dbMediaPath . 'video';
                        $model->$field = $videoPath . '/' . $_FILES[$field]['name'];
         
                    }
        
                    $availableField[] = $field;
                    
                }
            }
                    return $availableField;
    }

    
    public function store($model){
    $fullName = trim((string)$model->fullName);
    $sectionId = $model->assignTo;
    $cardProfileId = $this->ltId;
    
    if ($fullName === '') {
        return LtResponse::json('fullName is required', 3004, 100);
    }

    $basePath = '/media';
    $dbMediaPath = '/mdCardDesignMedia/';
    $uploadMediaPath = '/media/mdCardDesignMedia/';
    $result = [];

    if (!is_dir($uploadMediaPath)) {
        LtFile::path($basePath)->createFolder('mdCardDesignMedia');
    }

    $availableField = $this->processField(true, $model, $dbMediaPath, $uploadMediaPath);
   

    // Defaults
    $model->isEnabled  = $model->isEnabled  ?? '1';
    $model->isFeatured = $model->isFeatured ?? '0';
    $model->isVerified = $model->isVerified ?? '0';

    $model->ltId = $cardProfileId;
    $model->sectionId = $sectionId; // newly added
    $model->createdBy = $this->userId;
    $model->updatedBy = $this->userId;
    $model->createdAt = $this->ltNow;
    $model->updatedAt = $this->ltNow;

    // Upload first
    foreach ($availableField as $field) {
        $result[] = $this->processUpload($field, $uploadMediaPath);
    }
    
    // Then insert
    $model->insert();

    if($sectionId !== null || $sectionId !== ''){
        $cardSectionProfile = new TbCardSectionProfileService();
        $data = [
            'sectionId' => $sectionId,
            'cardProfileId' => $cardProfileId
        ];

        $cardSectionProfile->attach($data);
    }
        // return $result;
    return $model->responseJson();
}


    public function show($ltId){
        $row = $this->profileModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Profile not found', 3004, 100);
        }
        return $row[0];
    }

    public function update($model, $ltId){
         $dbMediaPath = '/mdCardDesignMedia/';
         $uploadMediaPath = 'storage/media/mdCardDesignMedia/';
         $result = [];
        $exist = $this->profileModel->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Profile not found', 3004, 100);
        }
        
        $availableField = $this->processField(false, $model, $dbMediaPath, $uploadMediaPath);
        $model->updatedBy = $this->userId;
        $model->updatedAt = $this->ltNow;
        
        // Upload first
        foreach ($availableField as $field) {
            $this->processUpload($field, $uploadMediaPath);
            $file = $exist[0]->$field;
            unlink('media' . $file);
        }

        $model->update('ltId','=',$ltId);
        return $model->responseJson();
    }

    public function toggleEnabled($ltId){
        $row = $this->profileModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Profile not found', 3004, 100);
        }

        $current = $row[0]->isEnabled;
        $newVal = ($current == '1') ? '0' : '1';

        $this->profileModel->isEnabled = $newVal;
        $this->profileModel->updatedBy = $this->userId;
        $this->profileModel->updatedAt = $this->ltNow;
        $this->profileModel->update('ltId','=',$ltId);

        return LtResponse::json('Profile Updated', 201, 200, ['ltId'=>$ltId,'isEnabled'=>$newVal]);
    }

    public function toggleFeatured($ltId){
        $row = $this->profileModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Profile not found', 3004, 100);
        }

        $current = $row[0]->isFeatured;
        $newVal = ($current == '1') ? '0' : '1';

        $this->profileModel->isFeatured = $newVal;
        $this->profileModel->updatedBy = $this->userId;
        $this->profileModel->updatedAt = $this->ltNow;
        $this->profileModel->where('ltId','=',$ltId)->update();

        return LtResponse::json('Updated', 200, 1, ['ltId'=>$ltId,'isFeatured'=>$newVal]);
    }

    public function destroy($ltId){
        $exist = $this->profileModel->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Profile not found', 3004, 100);
        }
        $expectedFields = ['image1', 'image2', 'image3', 'image4', 'video1', 'video2'];

        $this->profileModel->delete('ltId','=',$ltId);
        foreach($expectedFields as $field){
            $fieldToDelete= $exist[0]->$field;
            if($fieldToDelete !== null){
                unlink('media' . $fieldToDelete);
            }
        }
        
        return LtResponse::json('Deleted', 200, 200);
    }
}

