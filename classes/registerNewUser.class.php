<?php


use Pheal\Pheal;

class registerNewUser {
    
    private $apiPilotInfo = array();
    
    private $error;
    private $errorType;
    
    protected $id;
    protected $db;
    protected $permissions;
    protected $APIUserManagement;
    protected $userManagement;

    public function __construct() {
        $this->db = db::getInstance();
        $this->permissions = new permissions();
        $this->APIUserManagement = new APIUserManagement();
        $this->userManagement = new userManagement;
    }
    
    private function getInfoFromKey() {
            $this->apiPilotInfo = $this->APIUserManagement->getCharsInfo();
            if ($this->apiPilotInfo === NULL) {
                $errorArray = $this->APIUserManagement->log->get();
                $this->error = $errorArray[getApiPilotInfo];
            }
            
//            $c = $e->getCode();
//            $this->error = $e->getMessage();
//            if($c == 105 || $c == 106 || $c == 108 || $c == 112 || $c == 201 || $c == 202 || $c == 203 || $c == 204 || $c == 205 || $c == 210
//             || $c == 211 || $c == 212 || $c == 221 || $c == 222 || $c == 223 || $c == 516 || $c == 522){
//                $this->errorType = "user";
//            } else {
//                $this->errorType = "CCP";
//            }
    }
    
    private function makeRegisterArray() {
        $i = 0;
        for ($i = 0; $i < count($this->apiPilotInfo); $i++) {
            $mask = $this->userManagement->getAllowedListMask($this->apiPilotInfo[$i]);
            $this->permissions->setCustomMask($mask);
            $this->apiPilotInfo[$i]['canRegister'] = $this->permissions->hasPermission('webReg_Valid');
            $this->apiPilotInfo[$i]['permissions'] = $this->permissions->getAllPermissions();
        }
        print_r($this->apiPilotInfo);
    }
    
    public function setUserData($login, $password, $apiKey, $vCode) {
        try {
            $this->login = $login;
            $this->passwordHash = hash(sha512, $password);
            $this->apiKey = $apiKey;
            $this->vCode = $vCode;
            $this->getInfoFromKey();
            $apiError = $this->error;
            if ($apiError) {
                throw new Exception('Here is problem with your api: ' . $apiError);
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
    
    public function registerNewUser () {
        $regCheck = $this->makeRegisterArray();
    }
    
    public function getError() {
        return $this->error;
    }
    
    public function getErrorType() {
        return $this->errorType;
    }
}