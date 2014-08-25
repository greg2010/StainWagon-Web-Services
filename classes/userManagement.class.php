<?php

interface IuserManagement {
    function getUserInfo();
    function getUserCorpName();
    function getUserAllianceName();
    function setNewPassword();
    function setUserInfo();
}
/**
 * Description of userManagement
 *
 * @author greg02010
 */
class userManagement implements IuserManagement {
    
    
    protected $id;
    protected $db;
    protected $permissions;
    protected $pilotInfo;

    public function __construct($id) {
        $this->db = db::getInstance();
        $this->permissions = new permissions($id);
         if (!isset($id)) {
                $this->id = -1;
            } else {
                $this->id = $id;
                $this->getDbPilotInfo();
            }
    }

    private function getDbPilotInfo() {
        //Populates $dbPilotInfo
        try {
            $query = "SELECT * FROM `pilotInfo` WHERE `id` = '$this->id'";
            $result = $this->db->query($query);
            $this->pilotInfo = $this->db->fetchAssoc($result);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getPilotInfo() {
        return $this->pilotInfo;
    }

    public function getApiKey($keyStatus) {
        try {
            $query = "SELECT `keyID`, `vCode`, `characterID` FROM `apiList` WHERE `id` = '$this->id' AND `keyStatus` = '$keyStatus'";
            $result = $this->db->query($query);
            $apiKey = $this->db->fetchRow($result);
            return $apiKey;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getAllowedListMask($maskOwner = NULL) {
        try {
            if (isset($maskOwner)) {
                $characterID = $maskOwner[characterID];
                $corporationID = $maskOwner[corporationID];
                $allianceID = $maskOwner[allianceID];
            } else {
                $characterID = $this->pilotInfo[characterID];
                $corporationID = $this->pilotInfo[corporationID];
                $allianceID = $this->pilotInfo[allianceID];
            }
            $query = "SELECT `accessMask` FROM `allowedList` WHERE "
                    . "(`characterID` = '$characterID' AND `corporationID` IS NULL AND `allianceID` IS NULL)"
                    . " OR "
                    . "(`characterID` IS NULL AND `corporationID` = '$corporationID' AND `allianceID` IS NULL)"
                    . " OR "
                    . "(`characterID` IS NULL AND `corporationID` IS NULL AND `allianceID` = '$allianceID')"
                    . " OR "
                    . "(`characterID` = '$characterID' AND `corporationID` = '$corporationID' AND `allianceID` IS NULL)"
                    . " OR "
                    . "(`characterID` = '$characterID' AND `corporationID` IS NULL AND `allianceID` = '$allianceID')"
                    . " OR "
                    . "(`characterID` IS NULL AND `corporationID` = '$corporationID' AND `allianceID` = '$allianceID')"
                    . " OR "
                    . "(`characterID` = '$characterID' AND `corporationID` = '$corporationID' AND `allianceID` IS NULL)"
                    . " OR "
                    . "(`characterID` = '$characterID' AND `corporationID` = '$corporationID' AND `allianceID` = '$allianceID')";
            $result = $this->db->query($query);
            $userMasks = $this->db->fetchRow($result);
            foreach ($userMasks as $userMask) {
                $accessMask = $accessMask | $userMask;
            }
            if ($accessMask == '') {
                $accessMask = 0;
            }
            return $accessMask;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function getUserInfo() {
        
    }
    
    public function getUserCorpName() {
        
    }
    
    public function getUserAllianceName(){
        
    }
    
    public function setNewPassword(){
        
    }
    
    public function setUserInfo() {
        
    }
}