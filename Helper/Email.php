<?php

/* 
 EMAIL manipulations functions are here
 * All methods are STATIC here
 */

/**
 * PUBLIC STATIC methods:
 * - sendEmail()
 */

class Helper_Email{
    

    
//-----------------------------------------------------------
//------------------ATTRIBUTES-------------------------------
//-----------------------------------------------------------
    
    /*------------SETTINGS -----------------*/
    /**
     * SET your email sender here
     */
    const EMAIL_FROM = "your@email.com";
    /*------------END SETTINGS -----------------*/


//-----------------------------------------------------------
//------------------METHODS----------------------------------
//-----------------------------------------------------------
    
    
    
    
    //------------PUBLIC--------------------

    /**
     * sends email
     * @param str $to email of receiver
     * @param str $subject
     * @param str $message
     * @return bool
     */
    public static function sendEmail($to , $subject , $message , $from = self::EMAIL_FROM){
        $headers = 'From: '.$from . "\r\n" ;
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        return mail($to, $subject, $message, $headers);
    }
    
    
  
    
    
    
}
