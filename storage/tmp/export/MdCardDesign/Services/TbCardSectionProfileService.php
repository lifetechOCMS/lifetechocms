<?php

namespace Lt\Modules\MdCardDesign\Services;

use Lt\Modules\MdCardDesign\Models\TbCardSectionProfile;
use Lt\Modules\MdCardDesign\Models\TbCardProfile;
use Lt\Modules\MdCardDesign\Models\TbCardSection;
use Lt\Modules\MdLt\Services\LtResponse;
use Lt\Modules\MdLt\Services\LtRequest;
use Lt\Modules\MdLt\Services\LtSession;
use DbConnect;
use PDO;
        
class TbCardSectionProfileService
{
    protected $mapModel;
    protected $profileModel;
    protected $sectionModel;
    protected $request;
    protected $ltId;
    protected $userId;
    protected $ltNow;

    public function __construct(){
        $this->mapModel = new TbCardSectionProfile();
        $this->profileModel = new TbCardProfile();
        $this->sectionModel = new TbCardSection();
        $this->request = new LtRequest;
        $this->ltNow = date('Y-m-d H:i:s');
        $this->ltId = ltId();
        $this->userId = LtSession::get('ltUid');
    }

    public function index(){
        $sqlConnect = DbConnect::dbDriver();
        $request = new LtRequest;
        $sectionId = $request->sectionId ?? null;
        $profileId = $request->profileId ?? null;
        
        if($sectionId !== null){
            $sec = $this->sectionModel->select()->where('ltId','=',$sectionId)->get();
            if(count($sec) < 1){
                return LtResponse::json('Section not found', 3004, 100);
            }
        }
        if($profileId !== null){
            $pro = $this->profileModel->select()->where('ltId','=', $profileId)->get();
            if(count($pro) < 1){
                return LtResponse::json('Profile not found', 3004, 100);
            }
        }
        
        
        $sql = "
            SELECT csp.display_order AS displayOrder, csp.card_profile_id AS cardProfileId, csp.card_section_id AS cardSectionId, csp.lt_id AS ltId, cp.full_name AS fullName, cs.name AS sectionName
            FROM tb_card_section_profile AS csp
            LEFT JOIN tb_card_profile AS cp 
                ON cp.lt_id = csp.card_profile_id
            LEFT JOIN tb_card_section AS cs 
                ON cs.lt_id = csp.card_section_id
            WHERE csp.card_profile_id = :cardProfileId 
               OR csp.card_section_id = :cardSectionId
            ";
        
        $stmt = $sqlConnect->prepare($sql);
        $stmt->execute([
            ':cardProfileId' => $profileId,
            ':cardSectionId' => $sectionId
        ]);
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
            return LtResponse::json('success', 201, 200, $results);
            
    }

    public function attach($model){
        $cardProfileId = $model['cardProfileId'];
        $sectionId = $model['sectionId'];
  
        if(empty($cardProfileId)){
            return LtResponse::json('cardProfileId is required', 3004, 100);
        }
        
        $sec = $this->sectionModel->select()->where('ltId','=',$sectionId)->get();
        if(count($sec) < 1){
            return LtResponse::json('Section not found', 3004, 100);
        }
            
        $prof = $this->profileModel->select()->where('ltId','=',$cardProfileId)->get();
        if(count($prof) < 1){
            return LtResponse::json('Profile not found', 3004, 100);
        }

        $exist = $this->mapModel->select()
            ->where('cardSectionId','=',$sectionId)
            ->andWhere('cardProfileId','=',$cardProfileId)
            ->get();

        if(count($exist) > 0){
            return LtResponse::json('Profile already attached to this section', 3004, 100);
        }
        $this->mapModel->select('MAX(display_order) AS highestDisplayOrder')->where('cardSectionId','=',$sectionId)
            ->andWhere('cardProfileId','=',$cardProfileId)
            ->get();
            
        $highestSortNo = $this->mapModel->responseData[0]->highestDisplayOrder ?? null; 
    
        if (empty($highestSortNo)) {
            $highestSortNo =   8;
        }else{ 
            $highestSortNo += 8;
        }
        
        $this->mapModel->ltId = $this->ltId;
        $this->mapModel->cardSectionId = $sectionId;
        $this->mapModel->cardProfileId = $cardProfileId;
        $this->mapModel->displayOrder = $highestSortNo ?? 0;
        
        // if($model->isEnabled === null || $model->isEnabled === ''){
        //     $model->isEnabled = '1';
        // }

        $this->mapModel->createdBy = $this->userId;
        $this->mapModel->updatedBy = $this->userId;
        $this->mapModel->createdAt = $this->ltNow;
        $this->mapModel->updatedAt = $this->ltNow;
        
        $this->mapModel->insert();
        
        // add label title instead of only foreign key
        $this->mapModel->responseData->fullName = $prof[0]->fullName;
        $this->mapModel->responseData->sectionName = $sec[0]->name;
        return $this->mapModel->responseJson();
    }

    public function update($model, $mapId){
        $exist = $this->mapModel->select()->where('ltId','=',$mapId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Mapping record not found', 3004, 100);
        }

        $model->updatedBy = $this->userId;
        $model->updatedAt = $this->ltNow;

        $model->where('ltId','=',$mapId)->update();
        return $model->responseJson();
    }

    public function toggleEnabled($mapId){
        $row = $this->mapModel->select()->where('ltId','=',$mapId)->get();
        if(count($row) < 1){
            return LtResponse::json('Mapping record not found', 3004, 100);
        }

        $current = $row[0]->isEnabled;
        $newVal = ($current == '1') ? '0' : '1';

        $this->mapModel->isEnabled = $newVal;
        $this->mapModel->updatedBy = $this->userId;
        $this->mapModel->updatedAt = $this->ltNow;
        $this->mapModel->where('ltId','=',$mapId)->update();

        return LtResponse::json('Updated', 200, 1, ['ltId'=>$mapId,'isEnabled'=>$newVal]);
    }
    

    public function detach(){
        $request = new LtRequest;
        $mapId = $request->mapId;
        
        $exist = $this->mapModel->select()->where('ltId','=',$mapId)->get();
        if(count($exist) < 1){
            return LtResponse::json('Mapping record not found', 3004, 100);
        }

        $this->mapModel->delete('ltId','=',$mapId);
        return LtResponse::json('Detached', 201, 200);
    }

    public function reorder($sectionId, $items){
        if(!is_array($items)){
            return LtResponse::json('Invalid reorder payload', 3004, 100);
        }

        foreach($items as $row){
            $mapId = $row['ltId'] ?? null;
            if(empty($mapId)) continue;

            $displayOrder = $row['displayOrder'] ?? 0;

            $this->mapModel->displayOrder = (int)$displayOrder;
            $this->mapModel->updatedBy = $this->userId;
            $this->mapModel->updatedAt = $this->ltNow;

            $this->mapModel->where('ltId','=',$mapId)->where('cardSectionId','=',$sectionId)->update();
        }

        return LtResponse::json('Reordered', 200, 1);
    }
}
