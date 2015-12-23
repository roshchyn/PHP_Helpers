<?php

/* 
 Error manipulations functions are here
 * All methods are STATIC here
 */

/**
 * PUBLIC STATIC methods:
 * - setError()
 */


class Helper_Error{
    
    
    
      
      
//-----------------------------------------------------------
//------------------ATTRIBUTES-------------------------------
//-----------------------------------------------------------
    /**
     * email of developer who will receive error info by email
     */
    const EMAIL_ADMIN = "you@email.com";
    
    /**
     * log folder settings
     */
    const ERROR_LOGS_DIRECTORY = '/../log/';
     
    /**
     * php const for error
     */
    const LOG_MESSAGE_TYPE_EMAIL = 3;
     /**
     * error type
     */
    const ERROR_TYPE_DB = 'DB_ERROR';
    
    
    static $exit_errors = array( 'Access denied for user');
    
    static $continue_errors = array( 'Base table or view not found');
    
//-----------------------------------------------------------
//------------------METHODS----------------------------------
//-----------------------------------------------------------
    
    
    
    
    //------------PUBLIC--------------------
    
    
    

    /**
     * sets Error in many possible ways
     * @param str $type my custom type(category) of error
     * @param  str $emessage
     * @param  str $edata (just some additional data if needed)
     * @param  str $ecode (just some additional code of error  if needed)
     * @return bool
     */
    public static function setError($errorType, $emessage , $edata='' ,$ecode =''){
        if(!is_string($edata)){
           $edata = Helper_File::getDump($edata,true);
        }
        $message = "\r\t".'['.date('Y-m-d H:i:s O') . '] '.$ecode.' '.$errorType.' '. $emessage .' {{' . $edata . '}} '."\n"  ;
        //WRITE to file
        self::setLog($errorType, $message);
        //ECHO to screen
        print($message);
        //SEND email
        Helper_Email::sendEmail(self::EMAIL_ADMIN , $errorType . ": ".date('Y-m-d H:i:s O') , $message);
        return false;
    }
    

    
    
    
    //------------PROTECTED--------------------
    
    
    
    /**
     * write to a log file with name $typeDb
     */
    protected static function setLog($filename,$message){
        $destination = self::getLogPathFolder().$filename.".log";
        return error_log($message, self::LOG_MESSAGE_TYPE_EMAIL, $destination);
    }
   
    
    /**
     * withour / at the end
     * @return str fullpath to directory
     */
    protected static function getLogPathFolder(){
        return dirname(__FILE__).self::ERROR_LOGS_DIRECTORY;
    }
    
    
    
    
}
