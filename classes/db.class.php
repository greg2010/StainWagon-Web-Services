<?php

class db {
    private $connection;
    private $selectdb;
    private $lastQuery;
    private $config = array();
    private static $_instance = null;
    
    static public function getInstance() {
        if(is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->config = array (
                'hostname' => config::hostname,
                'username' => config::username,
                'password' => config::password,
                'database' => config::database
        );
    }
    
    /**
     * 
     * @param string $var
     * @return string
     */
    public function sanitizeString($var) {
        if(empty($this->connection)) {
            $this->openConnection();
            $var = mysqli_real_escape_string($this->connection, $var);
            return $var;
        } else {
            $var = mysqli_real_escape_string($this->connection, $var);
            return $var;
        }
    }
    
    /**
     *
     * @return object
     * @throws mysqli_sql_exception
     */
    
    public function openConnection() {
        $this->connection = mysqli_connect($this->config[hostname], $this->config[username], $this->config[password]);
        $this->selectdb = mysqli_select_db($this->connection, $this->config[database]);
        if (mysqli_connect_error()) {
            throw new Exception(mysqli_connect_error(), mysqli_connect_errno());
        }
    }

    /**
     *
     * @return \Exception
     */
    
    public function closeConnection() {
        try {
            mysqli_close($this->connection);
        } catch (Exception $e) {
            return $e;
        }
    }

    /**
     *
     * @param string $query
     * @return string|\Exception
     */
    
    public function query($query) {
        if(empty($this->connection)) {
            $this->openConnection();
            $this->lastQuery = mysqli_query($this->connection, $query);
            if (mysqli_error($this->connection)) {
                throw new Exception(mysqli_error($this->connection), mysqli_errno($this->connection));
            } else {
                return $this->lastQuery;
            }
            $this->closeConnection();
        } else {
            $this->lastQuery = mysqli_query($this->connection, $query);
            if (mysqli_error($this->connection)) {
                throw new Exception(mysqli_error($this->connection), mysqli_errno($this->connection));
            } else {
                return $this->lastQuery;
            }
        }
    }
    

    /**
     * 
     * @return \Exception|boolean
     */
    
    public function hasRows($result) {
        if (gettype($result)<> "object") {
            throw new Exception("hasRows: Wrong input type. Object expected, " . gettype($result) . " given.");
        }
        if(mysqli_num_rows($result)>0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @param object $result
     * @return \Exception
     */
    
    public function countRows($result) {
        if (gettype($result)<> "object") {
            throw new Exception("countRows: Wrong input type. Object expected, " . gettype($result) . " given.");
        }
        return mysqli_num_rows($result);         
    }
    
    /**
     * 
     * @param object $result
     * @return \Exception
     */
    
    public function affectedRows($result) {
        if (gettype($result)<> "object" OR $result <> TRUE) {
            throw new Exception("affectedRows: Wrong input type. Object or boolean true expected, " . var_dump($result) . " given.");
        }
        return mysqli_affected_rows($result);       
    }
    
    /**
     * 
     * @param object $result
     * @return \Exception
     */
    
    public function fetchAssoc($result) {
        if (gettype($result)<> "object") {
            throw new Exception("fetchAssoc: Wrong input type. Object expected, " . gettype($result) . " given.");
        }
        $assoc = array();
        $numRows =$this->countRows($result);
        if ($numRows === 1) {
            $assoc = mysqli_fetch_assoc($result);
        } else {
            while ($array = mysqli_fetch_assoc($result)) {
                $assoc[] = $array;
            }
        }
        return $assoc;
    }
    
    /**
     * 
     * @param object $result
     * @return \Exception
     */
    
    public function fetchArray($result) {
        if (gettype($result)<> "object") {
            throw new Exception("fetchArray: Wrong input type. Object expected, " . gettype($result) . " given.");
        }
        $arrays = array();
        while ($array = mysqli_fetch_array($result)) {
            $arrays[] = $array;
        }
        return $arrays;
    }

    /**
     * 
     * @param object $result
     * @return \Exception
     */
    
    public function fetchRow($result) {
        if (gettype($result)<> "object") {
            throw new Exception("fetchRow: Wrong input type. Object expected, " . gettype($result) . " given.");
        }
        $rows = array();
        $numRows =$this->countRows($result);
        if ($numRows === 1) {
            $rows = mysqli_fetch_row($result);
        }  else {
            while ($array = mysqli_fetch_row($result)) {
                $rows[] = $array;
            }
        }
        return $rows;
    }
    
    /**
     * 
     * @param object $result
     * @param int $i
     * @return \Exception
     */
    
    public function getMysqlResult($result, $i = NULL) {
        if (gettype($result)<> "object") {
            throw new Exception("getMysqlResult: Wrong input type. Object expected, " . gettype($result) . " given.");
        }
        $row = $this->fetchRow($result);
        foreach ($row as $value) {
            if(gettype($value) == "array") throw new Exception("getMysqlResult: Wrong input type. Array deeper than expected.");
        }
        /*if (count($row) > 1) {
            throw new Exception("getMysqlResult: Wrong input type. Expected 1 row, got " . count($row) . " row(s).");
        }*/
        if ($i) {
            return $row[$i];
        } else {
            return $row[0];
        }
    }
    
    /**
     * 
     * @return object
     */
    
    public function lastQuery() {
        return $this->lastQuery;
    }
    
    /**
     * 
     * @return \Exception|boolean
     */
    
    public function pingServer() {
        if(!mysqli_ping($this->connection)) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * 
     * @param object $result
     * @return \Exception
     */
    
    public function toArray($result) {
        $results = array();
        while(($row = $result->fetch_assoc()) != false) {
            $results[] = $row;
        }
        return $results;
    }
    
    private function predefinedMySQLLogin($login, $passwordHash) {
        $stmt = mysqli_prepare($this->connection, "SELECT `id` FROM `users` WHERE `login`=? AND `passwordHash`=?");
        mysqli_stmt_bind_param($stmt, "ss", $login, $passwordHash);
        mysqli_stmt_execute($stmt);
        if (mysqli_error($this->connection)) {
            throw new Exception(mysqli_error($this->connection), mysqli_errno($this->connection));
        }
        mysqli_stmt_bind_result($stmt, $id);
        $success = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($success === True) {
            return $id;
        } else {
            $id = False;
            return $id;
        }
    }
    
    private function predefinedMySQLCheckIfUserRegistered($login) {
        $stmt = mysqli_prepare($this->connection, "SELECT `id` FROM `users` WHERE `login`=?");
        mysqli_stmt_bind_param($stmt, "s", $login);
        mysqli_stmt_execute($stmt);
        if (mysqli_error($this->connection)) {
            throw new Exception(mysqli_error($this->connection), mysqli_errno($this->connection));
        }
        mysqli_stmt_bind_result($stmt, $id);
        $success = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($success === True) {
            return $id;
        } else {
            return False;
        }
    }
    
    private function predefinedMySQLCookie($cookie) {
        $query = "SELECT `id` FROM `userCookies` WHERE ";
        $cookieNumber = config::cookieNumber;
        for ($i = 0; $i<=$cookieNumber; $i++) {
            $fields .= '`cookie' . $i . "`=?";
            if ($i<$cookieNumber) {
                $fields .= ' OR ';
            } else {
                $fields .= ' ';
            }
        }
        $query .= $fields;
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $cookie, $cookie, $cookie, $cookie, $cookie); //Repeat $cookie as many times as there cookies fields
        mysqli_stmt_execute($stmt);
        if (mysqli_error($this->connection)) {
            throw new Exception(mysqli_error($this->connection), mysqli_errno($this->connection));
        }
        mysqli_stmt_bind_result($stmt, $id);
        $success = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($success === True) {
            return $id;
        } else {
            $id = False;
            return $id;
        }
    }
    
    private function predefinedPopulateUsers($login, $passwordHash, $accessMask, $salt, $email = NULL) {
        $this->openConnection();
        $stmt = mysqli_prepare($this->connection, "INSERT INTO `users` SET `login`=?, `passwordHash`=?, `accessMask`=?, `email`=?, `salt`=?");
        mysqli_stmt_bind_param($stmt, "sssss", $login, $passwordHash, $accessMask, $email, $salt);
        $success = mysqli_stmt_execute($stmt);
        if (mysqli_error($this->connection)) {
            throw new Exception("predefinedPopulateUsers: " . mysqli_error($this->connection), mysqli_errno($this->connection));
        }
        mysqli_stmt_close($stmt);
        $this->closeConnection();
        return $success;
    }
    
    private function predefinedPopulateApiList($id, $keyID, $vCode, $characterID, $keyStatus) {
        $this->openConnection();
        $stmt = mysqli_prepare($this->connection, "INSERT INTO `apiList` SET `id`=?, `keyID`=?, `vCode`=?, `characterID`=?, `keyStatus`=?");
        mysqli_stmt_bind_param($stmt, "sssss", $id, $keyID, $vCode, $characterID, $keyStatus);
        $success = mysqli_stmt_execute($stmt);
        if (mysqli_error($this->connection)) {
            throw new Exception("predefinedPopulateApiList: " . mysqli_error($this->connection), mysqli_errno($this->connection));
        }
        mysqli_stmt_close($stmt);
        $this->closeConnection();
        return $success;
    }
    
    private function predefinedPopulatePilotInfo($id, $characterID, $characterName, $corporationID, $corporationName, $allianceID, $allianceName) {
        $this->openConnection();
        $stmt = mysqli_prepare($this->connection, "INSERT INTO `pilotInfo` SET `id`=?, `characterID`=?, `characterName`=?, `corporationID`=?, `corporationName`=?, `allianceID`=?, `allianceName`=?");
        mysqli_stmt_bind_param($stmt, "sssssss", $id, $characterID, $characterName, $corporationID, $corporationName, $allianceID, $allianceName);
        $success = mysqli_stmt_execute($stmt);
        if (mysqli_error($this->connection)) {
            throw new Exception("predefinedPopulatePilotInfo: " . mysqli_error($this->connection), mysqli_errno($this->connection));
        }
        mysqli_stmt_close($stmt);
        $this->closeConnection();
        return $success;
    }

    public function getUserByLogin($login, $passwordHash) {
        $this->openConnection();
        $id = $this->predefinedMySQLLogin($login, $passwordHash);
        return $id;
    }
    
    public function getUserByCookie($cookie) {
        $this->openConnection();
        $id = $this->predefinedMySQLCookie($cookie);
        return $id;
    }
    
    public function checkUserRegistered($login) {
        $this->openConnection();
        return $this->predefinedMySQLCheckIfUserRegistered($login);
    }
    
    public function registerNewUser($keyID, $vCode, $characterID, $keyStatus, $characterName, $corporationID, $corporationName, $allianceID, $allianceName, $passwordHash, $permissions, $email = NULL, $salt) {
        try {
            $this->predefinedPopulateUsers($characterName, $passwordHash, $permissions, $salt, $email);
            $id = $this->checkUserRegistered($characterName);
            $this->predefinedPopulateApiList($id, $keyID, $vCode, $characterID, $keyStatus);
            $this->predefinedPopulatePilotInfo($id, $characterID, $characterName, $corporationID, $corporationName, $allianceID, $allianceName);
        } catch (Exception $ex) {
            $firstException = $ex->getMessage();
            //Rolling Back...
            try {
                $query = "DELETE FROM `pilotInfo` WHERE `characterID` = '$characterID'";
                $thirdRes = strval($this->query($query));
                $query = "DELETE FROM `apiList` WHERE `vCode` = '$vCode'";
                $secondRes = strval($this->query($query));
                $query = "DELETE FROM `users` WHERE `login` = '$characterName'";
                $firstRes = strval($this->query($query));
            } catch (Exception $ex) {
                $secondException = $ex->getMessage();
            }
            throw new Exception("Something went terribly wrong. Message: " . $firstException . "\nRolling back... users: " . $firstRes . "\napiList: " . $secondRes . "\npilotInfo: " . $thirdRes . "\nErrors during rolling back: " . $secondException);
        }
    }
}