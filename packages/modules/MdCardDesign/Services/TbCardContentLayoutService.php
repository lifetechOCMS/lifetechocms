<?php

namespace Lt\Modules\MdCardDesign\Services;

use Lt\Modules\MdCardDesign\Models\TbCardContentLayout;
use Lt\Modules\MdCardDesign\Models\TbCardSection;
use Lt\Modules\MdCardDesign\Models\TbCardSectionProfile;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
        
class TbCardContentLayoutService
{
    protected $contentModel;
    protected $sectionModel;
    protected $sectionProfileModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->contentModel = new TbCardContentLayout();
        $this->sectionModel = new TbCardSection();
        $this->sectionProfileModel = new TbCardSectionProfile();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
    }

    public function index(){
        $packageName = $this->request->packageName;
        $contentName = $this->request->contentName;
        $isEnabled = $this->request->isEnabled;

        $query = $this->contentModel->select();

        if($packageName !== null && $packageName !== ''){
            $query->where('packageName','=',$packageName);
        }

        if(!empty($contentName)){
            $query->where('contentName','=',$contentName);
        }

        if($isEnabled !== null && $isEnabled !== ''){
            $query->where('isEnabled','=',$isEnabled);
        }

        return $query->get();
    }

    public function store($model){
        $model = (object)$model;
        $contentName = $model->contentName;
        if($contentName === ''){
            return LtResponse::json('contentName is required', 3004, 100);
        }
     
        // Prevent duplicate (packageName, contentName) - packageName can be NULL
        $existing = $this->contentModel->select()->where('contentName','=',$contentName)->get();
        
        foreach($existing as $row){
            $rowPkg = $row->packageName ?? null;
            $newPkg = $model->packageName ?? null;
            if($rowPkg == $newPkg){
                return LtResponse::json('Content Layout record already exist for this package/content', 3004, 100);
            }
        }

        // cardSectionId is nullable but if provided, must exist
        if($model->cardSectionId !== null && $model->cardSectionId !== ''){
            $sec = $this->sectionModel->select()->where('ltId','=',$model->cardSectionId)->get();
            if(count($sec) < 1){
                return LtResponse::json('Invalid cardSectionId', 3004, 100);
            }
        }

         $this->contentModel->ltId = $this->ltId;
         $this->contentModel->cardSectionId = $model->cardSectionId;
         $this->contentModel->packageName = $model->packageName;
         $this->contentModel->contentName = $contentName;
         $this->contentModel->createdBy = $this->userId;
         $this->contentModel->updatedBy = $this->userId;
         $this->contentModel->createdAt = $this->ltNow;
         $this->contentModel->updatedAt = $this->ltNow;

        // $model->isEnabled = '1';

        $this->contentModel->insert();
        return  $this->contentModel->responseJson();
    }

    public function show($ltId){
        $row = $this->contentModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Content Layout not found', 3004, 100);
        }
        return $row[0];
    }

    public function update($model, $ltId){
        $exist = $this->contentModel->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Content Layout not found', 3004, 100);
        }

        if($model->cardSectionId !== null && $model->cardSectionId !== ''){
            $sec = $this->sectionModel->select()->where('ltId','=',$model->cardSectionId)->get();
            if(count($sec) < 1){
                return LtResponse::json('Invalid cardSectionId', 3004, 100);
            }
        }

        $model->updatedBy = $this->userId;
        $model->updatedAt = $this->ltNow;

        $model->update('ltId','=',$ltId);
        return $model->responseJson();
    }
    
    public function assignSection(){
        $request = new LtRequest;
        $ltId = $request->ltId;
        $sectionId = $request->sectionId;
        
        $this->contentModel->	card_section_id = $sectionId;
        $this->contentModel->updatedBy = $this->userId;
        $this->contentModel->updatedAt = $this->ltNow;
        $this->contentModel->update('ltId','=',$ltId);
        return $this->contentModel->responseJson();
    }
    
    public function toggleEnabled($ltId){
        $row = $this->contentModel->select()->where('ltId','=',$ltId)->get();
        if(count($row) < 1){
            return LtResponse::json('Content Layout not found', 3004, 100);
        }

        $current = $row[0]->isEnabled;
        $newVal = ($current == '1') ? '0' : '1';

        $this->contentModel->isEnabled = $newVal;
        $this->contentModel->updatedBy = $this->userId;
        $this->contentModel->updatedAt = $this->ltNow;
        $this->contentModel->update('ltId','=',$ltId);

        return LtResponse::json('Updated', 201, 200, ['ltId'=>$ltId,'isEnabled'=>$newVal]);
    }

    public function destroy($ltId){
        $exist = $this->contentModel->select()->where('ltId','=',$ltId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Content Layout not found', 3004, 100);
        }

        $this->contentModel->delete('ltId','=', $ltId);
        return LtResponse::json('Deleted', 201, 200);
    }
    
    public function renderPackage($packageName, $contentName){
        $sqlConnect = \DbConnect::dbDriver();
        $cardSectionId = null;
        $defaultSection = '176993384015575';
        // check for existing record 
        $existing = $this->contentModel->select()->where('contentName','=',$contentName)->andWhere('packageName','=',$packageName)->get();
        if(count($existing) > 0){
            $cardSectionId = $existing[0]->cardSectionId;
        }else{
            $cardSectionId = $defaultSection;
            $data = [
                'contentName' => $contentName,
                'packageName' => $packageName,
                'cardSectionId' => $cardSectionId
                ];
                
            $insertResult = $this->store($data);
        }
        
        // fetch section record
        $sectionRecord = $this->sectionModel->select("name, slug, description")->where('isEnabled', '=', 1)->andWhere('ltId', '=', $cardSectionId)->get()[0];
        
        if (!$sectionRecord) {
            return LtResponse::json('Section not found', 304, 104);
        }
        
        $cardSectionProfile = $this->sectionProfileModel->select('card_profile_id, display_order, badge, custom_title')->where('isEnabled', '=', 1)->andWhere('cardSectionId', '=', $cardSectionId)->get();
        
        if (empty($cardSectionProfile)) {
            $sectionRecord->profiles = [];
            return LtResponse::json('success', 200, 200, $section);
        }
        // Fetch Card Profiles Individually (Enabled Only) using profileID
        $profileIds = array_column($cardSectionProfile, 'cardProfileId');

        $placeholders = implode(',', array_fill(0, count($profileIds), '?'));
        
        $profileSql = "
            SELECT *
            FROM tb_card_profile
            WHERE lt_id IN ($placeholders) AND is_enabled = 1
        ";
        
        $profileStmt = $sqlConnect->prepare($profileSql);
        $profileStmt->execute($profileIds);
        $profiles = $profileStmt->fetchAll(\PDO::FETCH_ASSOC);

        // // Index profiles for fast lookup
        $profileMap = [];
        foreach ($profiles as $profile) {
            $profileMap[$profile['lt_id']] = $profile;
        }

        $sectionRecord->profiles = [];
        // $id = [];
        foreach ($cardSectionProfile as $assign) {
            $profileId = $assign->cardProfileId;
            if($profileMap[$profileId]){
                $sectionRecord->profiles[] = array_merge($profileMap[$profileId], [
                'displayOrder' => $assign->displayOrder,
                'badge'        => $assign->badge,
                'customTitle'  => $assign->custom_title]);
            }
            
        }

        
        if(empty($sectionRecord)) return LtResponse::json('Content not configured', 3004, 100);
        return LtResponse::json('Data Fetched', 3010, 200, $sectionRecord);
    }

    public function resolveDefault($contentName){
        $rows = $this->contentModel->select()
            ->where('contentName','=',$contentName)
            ->where('isEnabled','=','1')
            ->get();

        foreach($rows as $r){
            if(($r->packageName ?? null) === null){
                return $r;
            }
        }

        return LtResponse::json('Content not configured', 3004, 100);
    }

    public function resolvePackage($packageName, $contentName){
        $row = $this->contentModel->select()
            ->where('packageName','=',$packageName)
            ->where('contentName','=',$contentName)
            ->where('isEnabled','=','1')
            ->get();

        if(count($row) < 1){
            return LtResponse::json('Content not configured', 3004, 100);
        }

        return $row[0];
    }
}

