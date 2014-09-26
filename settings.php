<?php
$thisPage = "settings";
require_once 'auth.php';
include 'header.php';

$pageActive = "class=active";

$pagePermissions = array("webReg_Valid");

$templateName = $thisPage;
$page = $_GET[a];

switch ($page) {
    case 'api':
        $toTemplate['curForm'] = 'api';
        $toTemplate['active']['api'] = $pageActive;
        break;
    case 'teamSpeak':
        $toTemplate['curForm'] = 'teamspeak';
        $toTemplate['active']['teamspeak'] = $pageActive;
        break;
    default:
        $toTemplate['curForm'] = '';
        $toTemplate['active']['profile'] = $pageActive;
        
        $toTemplate['saveForm']['email'] = $_SESSION[userObject]->userInfo[email];
        if ($_POST[form] == 'sent') {
            try {
                $currPassword = $_POST[currentPassword];
                $_SESSION[userObject]->verifyCurrentPassword($currPassword);
                if ($_POST[email]) {
                    try {
                        $email = $_POST[email];
                        $toTemplate['saveForm']['email'] = $email;
                        $_SESSION[userObject]->userManagement->setNewEmail($email);
                    } catch (Exception $ex) {
                        switch ($ex->getCode()) {
                            case 11:
                                $toTemplate["errorMsgEmail"] = "There is a problem: " . $ex->getMessage();
                                break;
                            case 30:
                                $toTemplate["errorMsgEmail"] = "Internal server error. Please contact server administrators ASAP to resolve this issue! Please convey this information to server administator:" . $ex->getMessage();
                                break;
                        }
                    }
                }
                if ($_POST[password]) {
                    try {
                        $password = $_POST[password];
                        $passwordRepeat = $_POST[passwordRepeat];
                        $_SESSION[userObject]->userManagement->setNewPassword($password, $passwordRepeat);
                    } catch (Exception $ex) {
                        switch ($ex->getCode()) {
                            case 11:
                                $toTemplate["errorMsgPassword"] = "There is a problem: " . $ex->getMessage();
                                break;
                            case 30:
                                $toTemplate["errorMsgPassword"] = "Internal server error. Please contact server administrators ASAP to resolve this issue! Please convey this information to server administator:" . $ex->getMessage();
                                break;
                        }
                    }
                }
            } catch (Exception $ex) {
                switch ($ex->getCode()) {
                    case 13:
                        $toTemplate["errorMsg"] = "Wrong password!";
                        break;
                    case 30:
                        $toTemplate["errorMsg"] = "Internal server error. Please contact server administrators ASAP to resolve this issue! Please convey this information to server administator:" . $ex->getMessage();
                        break;
                }
            }
        }
}

require 'twigRender.php';

/**
 *  EMAIL:
 *  IF EMAIL
 *      SHOW EMAIL
 *      IF EMAIL CORRECT
 *          UPDATE EMAIL
 *      ELSE
 *          THROW ERROR EMAIL INCORRECT
 *      ENDIF     
 *  ENDIF
 * 
 * CHANGE PASSWORD:
 *  IF CURRENT PASSWORD CORRECT
 *      IF NEW PASSWORD CORRECT
 *          CHANGE CURRENT PASSWORD
 *      ELSE
 *          THROW ERROR BAD PASSWORD
 *      ENDIF
 *  ELSE
 *      THROW ERROR INCORRECT PASSWORD
 *  ENDIF
 * 
 * API:
 * Current API:
 *  IF CHANGED:
 *      IF GetChars PRESSED:
 *          IF CHAR ALLOWED:
 *              CHECK IF NOT ALREADY THERE:
 *                  CHANGE API, UPDATE ALL THE INFORMATION, UNSET & CREATE $_SESSION[userObject]
 *              ELSE
 *                  CHECK IF HAS keyStatus 0:
 *                      CHANGE API, UPDATE ALL THE INFORMATION, UNSET & CREATE $_SESSION[userObject]
 *                  ELSE
 *                      SHOW ERROR
 *                  ENDIF
 *              ENDIF
 *          ELSE
 *              SHOW ERROR
 *          ENDIF
 *      E:SE
 *          SHOW  ERROR
 *      ENDIF
 *  ENDIF
 * 
 * Secondary API:
 *  IF ADDED:
 *      CHECK IF NOT ALREADY THERE:
 *          ADD WITH keyStatus = 2
 *      ELSE
 *          CHECK IF HAS keyStatus 0:
 *              CHANGE keyStatus & vCode
 *          ELSE
 *              SHOW ERROR
 *          ENDIF
 *      ENDIF
 *  ENDIF
 */