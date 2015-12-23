<?php

/* 
 DATE functions are here
 * All methods are STATIC here
 */

/**
 * PUBLIC STATIC methods:
 * - getTimestamp()
 * - getDayOfWeek()
 * - get24HoursAgo()
 * - getYesterdayDate()
 * - parseDatetime()
 */

class Helper_Date{
    
    
    
    
//-----------------------------------------------------------
//------------------ATTRIBUTES-------------------------------
//-----------------------------------------------------------
    
    
    
//-----------------------------------------------------------
//------------------METHODS----------------------------------
//-----------------------------------------------------------
    
    
    
    
    //------------PUBLIC--------------------
    
    
    /**
     * Get current time in format Y-m-d h:i:s FROM unix time
     * @param int $time unix time
     * @return timestamp Y-m-d h:i:s
     */
    public static function getTimestamp($time){
        return date('Y-m-d H:i:s',$time);
    }
    
    /**
     * @param timestamp $timestamp in format (Y-m-d h:i:s)
     * @return string ex. 'sunday'
     */
    public static function getDayOfWeek($timestamp){
        $w = date('N',strtotime($timestamp));
        switch ($w) {
            case 1:return 'monday';
            case 2:return 'tuesday';
            case 3:return 'wednesday';
            case 4:return 'thursday';
            case 5:return 'friday';
            case 6:return 'saturday';
            case 7:return 'sunday';
        }
    }
    
    
    /**
     * moment in -24hours (yesterday at the same time)
     * @return timestamp Y-m-d h:i:s
     */
    public static function get24HoursAgo(){
        return date('Y-m-d H:i:s',strtotime("-1 days"));
    }
    
    /**
     * yesterdayDate
     * @return timestamp Y-m-d 
     */
    public static function getYesterdayDate(){
        return date('Y-m-d',strtotime("-1 days"));
    }
    
    
    /**
     * gets 2015-11-23T04:23:42-0800 OR 2015-11-23T04:23:42+0800
     * @return timestamp 2015-11-23 04:23:42
     */
    public static function parseDatetime($timestamp){
        $timestamp = str_ireplace('T', ' ', $timestamp);
        $t = explode('-',$timestamp);
        if(count($t) == 4){
            unset($t[count($t)-1]);
            return implode('-', $t);
        }else{
            $t = explode('+',$timestamp);
            return $t[0];
        }
    }
    
    
}
