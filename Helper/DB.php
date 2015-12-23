<?php
/*
* Mysql DATABASE class - only one connection alowed
 * !IMPORTANT - You must change $_host, $_username ,$_password to access MySQL server
*/


/**
 * PUBLIC methods:
 * - exec()
 * - insert()
 * - insertOne()
 * - get()
 
 * - getDBName()
 */

class Helper_DB {

    
    
    
//-----------------------------------------------------------
//------------------ATTRIBUTES-------------------------------
//-----------------------------------------------------------

        /*------------SETTINGS -----------------*/
        /**
         * Change you MySQL server connection params
        */
        private $_database = "myDataBaseName";
        private $_host = "127.0.0.1";
	private $_username = "root";
	private $_password = "root";
        
        /*------------END SETTINGS -----------------*/
    
      
        
        
        
        
        private $_pdo;
        private static $_instance; //The single instance
	
        /**
         * Just some most used types
         * We can use them outside this class to create a table
         */
        const TYPE_INT = 'INT NULL';
        const TYPE_TINYINT = 'TINYINT NULL';
        const TYPE_VARCHAR_255 = 'VARCHAR(255) NULL';
        const TYPE_VARCHAR_1000 = 'VARCHAR(1000) NULL';
        const TYPE_VARCHAR_5000 = 'VARCHAR(5000) NULL';
        const TYPE_VARCHAR_10000 = 'VARCHAR(10000) NULL';
        const TYPE_DATETIME = 'DATETIME NULL';
        const TYPE_DATE = 'DATE NULL';
        const TYPE_TIMESTAMP = 'TIMESTAMP NULL';

        
        
//-----------------------------------------------------------
//------------------METHODS----------------------------------
//-----------------------------------------------------------
    
    
    
    
    //------------PUBLIC--------------------

        
        
        
        /** EXECUTE any SQL
         * Can be used with or without params 
         * @param str $sql 
         * @param arr $params
         * @return bool
         */
        public function exec($sql, $params =''){
		try {
                        $stmt = $this->_pdo->prepare($sql);
                        if($params != ''){
                            foreach($params as $key => $value){
                                $stmt->bindValue($key, $value);
                            }
                        }
                        return $stmt->execute();
                }
		catch (PDOException $e) {
                    $err_data = array('sql'=>$sql, 'params'=>$params);
                    Helper_Error::setError( Helper_Error::ERROR_TYPE_DB  , $e->getMessage() , $err_data , $e->getCode() );
                    return false;
		}
        }
        
        /** INSERT ONE
         * inserts only in one row
         * @param str $sql 
         * @param arr $params
         * @return bool
         */
        public function insertOne($sql, $params =''){
		try {
                        $stmt = $this->_pdo->prepare($sql);
                        if($params != ''){
                            foreach($params as $key => $value){
                                $stmt->bindValue($key, $value);
                            }
                        }
                        $stmt->execute();
                        return $this->_pdo->lastInsertId();
                }
		catch (PDOException $e) {
                    $err_data = array('sql'=>$sql, 'params'=>$params);
                    Helper_Error::setError( Helper_Error::ERROR_TYPE_DB  , $e->getMessage() , $err_data , $e->getCode() );
                    return false;
        	}
        }
        
        /**INSERT (multiple rows)
        * A custom function that automatically constructs a multi insert statement.
        * 
        * @param string $tableName Name of the table we are inserting into.
        * @param array $data An "array of arrays" containing our row data. 
        * @param bool $ignore. whether to ignore duplication error and not saving if there is duplications (INSERT IGNORE )
        * @return bool
        */
       public function insert($tableName, $data , $ignore = false){

           $rowsSQL = array();
           $toBind = array();
           $columnNames = array_keys($data[0]);
           //Loop through our $data array.
           foreach($data as $arrayIndex => $row){
               $params = array();
               foreach($row as $columnName => $columnValue){
                   $param = ":" . $columnName . $arrayIndex;
                   $params[] = $param;
                   $toBind[$param] = $columnValue; 
               }
               $rowsSQL[] = "(" . implode(", ", $params) . ")";
           }
           if($ignore === true)
               $sql = "INSERT IGNORE INTO `$tableName` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);
           else
               $sql = "INSERT INTO `$tableName` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);
           try{
                $stmt = $this->_pdo->prepare($sql);
                foreach($toBind as $param => $val){
                    $stmt->bindValue($param, $val);
                }
                $res=$stmt->execute();
                return $res;
           }
           catch (PDOException $e) {
                    $err_data = array('stmt'=>$stmt, 'params'=>$data);
                    Helper_Error::setError( Helper_Error::ERROR_TYPE_DB  , $e->getMessage() , $err_data , $e->getCode() );
                    return false;
            }
       }
        
        /**GET (select)
         * select from MySQL
         * @param str $sql 
         * @param arr $params
         * @return array/bool
         */
         public function get($sql,$params =''){
		try {   
			$stmt = $this->_pdo->prepare($sql);
                        if($params != ''){
                            foreach($params as $key => $value){
                                $stmt->bindValue($key, $value);
                            }
                        }
                        if($stmt->execute() === false){
                            return false;
                        }
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return $result;
                }
		catch (PDOException $e) {
                    $err_data = array('stmt'=>$stmt, 'params'=>$params);
                    Helper_Error::setError( Helper_Error::ERROR_TYPE_DB  , $e->getMessage() , $err_data , $e->getCode() );
                    return false;
                }
        }     
       
        
        
        /**
         * 
         * @return string
         */
        public function getDBName(){
            return $this->_database;
        }
        
        
        /**
         * construct 
         */
        public function __construct(){
            $this->initDB();
            $this->_pdo = new PDO( "mysql:dbname=$this->_database;host=$this->_host", $this->_username, $this->_password, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
            $this->_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        
        //------------PROTECTED|PRIVATE--------------------
        
        
        /**
         * getting the only instance
         * @return obj
         */
        public static function getConnection(){
            if (self::$_instance === null){
                self::$_instance = new DB();
            }
            return self::$_instance;
        }
        
        /**
         * Disabling __clone()
         * @return false
         */
        public function __clone(){
            return false;
        }
        
        
        /**
         * Disabling __wakeup()
         * @return false 
         */
        public function __wakeup(){
            return false;
        }
        
        
        
        
        
        /**
         * if DB not exist on MySQL SErver, we will create it
         */
        private function initDB(){
            $sql = "
                CREATE SCHEMA IF NOT EXISTS `".$this->_database."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
                USE `".$this->_database."` ;
             ";
            try {
                $dbh = new PDO("mysql:host=$this->_host", $this->_username, $this->_password,array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
                $dbh->exec($sql)
                or die(print_r($dbh->errorInfo(), true));
            } catch (PDOException $e) {
                $err_data = array('stmt'=>'on init');
                Helper_Error::setError( Helper_Error::ERROR_TYPE_DB  , $e->getMessage() , $err_data , $e->getCode() );
                die("DB init ERROR: ". $e->getMessage());
            }
        }
}
