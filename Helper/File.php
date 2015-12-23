<?php

/* 
 File system functions are here
 */

/**
 * PUBLIC STATIC methods:
 * - fileSearch()
 * - csvToArray()
 * - getFileFromUrl()
 * - extractZip()
 * - utf8EncodeDeep()
 * - trimAny()
 * - getDump()
 */


class Helper_File{

    
    
    
        
//-----------------------------------------------------------
//------------------ATTRIBUTES-------------------------------
//-----------------------------------------------------------
    
    /**
     * Memory that we will use for our parse files
     * in bytes
     */
    const PARSE_ALLOCATED_MEMORY = 10000000; // About 10 MB
    const ERROR_FILE = 'FILE ERROR';
    /**
     * default filename
     */
    const ZIP_FILE_NAME = 'zipfile.zip';
    /**
     * operation time limit
     */
    const TIME_LIMIT = 3600; // 1 hour = 3600 seconds
    
//-----------------------------------------------------------
//------------------METHODS----------------------------------
//-----------------------------------------------------------
    
    
    
    
    //------------PUBLIC--------------------

    /**
     * Search for a file in subfolders
     * @param type $folder
     * @param type $pattern
     * @return type
     */
    public static function fileSearch($f,$p=null,$l=1000){
            $cd=$p==null?getcwd():$p;
            if(substr($cd,-1,1)!="/")$cd.="/";
            if(is_dir($cd))
            {
                    $dh=opendir($cd);
                    while($fn=readdir($dh))
                    {	// traverse directories and compare files:
                            if(is_file($cd.$fn)&&$fn==$f){closedir($dh);return $cd.$fn;}
                            if($fn!="."&&$fn!=".."&&is_dir($cd.$fn)){$m=self::fileSearch($f,$cd.$fn,$l);if($m){closedir($dh);return $m;}}
                    }
                    closedir($dh);
            }
            return false;
    }
    
    
    /**
      * Makes array from a csv file
      * @param str $filename full path to filename
      * @param str $delimiter
      * @param int $startRow
      * @return array 'data' =>$data,
                      'isFileEnd'=> $isFileEnd,
                      'rowsAlreadyProcessed'=> $i 
      */
     public static function csvToArray($path='' , $filename='',  $delimiter=',', $startRow = false){
        $filePath = $path.$filename;
        if(!file_exists($filePath) || !is_readable($filePath)){
           //We search in subfolders for this file
           $filePath = self::fileSearch($filename ,$path);
           if(!$filePath){
               echo 'Error - Cannot find or write into '.$filename;
               return false;      
           }
        }
        $header = NULL;
        $data = array();
        if (($handle = fopen($filePath, 'r')) !== FALSE){   
            $i=0; // because we use header
            $isFileEnd = true;
            $memory_start = memory_get_usage();
            while (($row = fgetcsv($handle, 2000, $delimiter)) !== FALSE){
                if(!$header){
                    $header = $row;
                }
                else{
                    if( $i++ < $startRow && $startRow !== false ){
                        continue;
                    }
                    if ( (memory_get_usage() - $memory_start) > self::PARSE_ALLOCATED_MEMORY ) {
                        echo 'BREAK - parsing memory limit is riched. Cannot continue.';
                        $isFileEnd = false;
                        break;
                    }
                    //check if empty
                    if(count($row) > 1){
                        //replacing all hidden characters that causes problem
                        for($ik=0;$ik<count($header);$ik++){
                            $header[$ik] = preg_replace("/[^\w\d]/","",strtolower($header[$ik]));
                        }
                        self::trimAny($header);
                        self::trimAny($row);
                        $count = min(count($header), count($row));
                        $data[] = array_combine(array_slice($header, 0, $count), array_slice($row, 0, $count));
                    }
                }
            }
            fclose($handle);
        }
        usleep(10);flush();//wait for 0,01 sec/ we let CPU do its job
        return array( 'data' =>$data,
                      'isFileEnd'=> $isFileEnd,
                      'rowsAlreadyProcessed'=> $i   
            );
     }
     
    
     /**
     * Download a file from a given URL 
     * @param str $url 
     * @param str $savePath
     * @return bool/str filepath to new zip 
     */
    public static function getFileFromUrl($url, $savePath){
        $file = $savePath . self::ZIP_FILE_NAME;
        $fileResource = fopen($file, "w");
        // Get The Zip File From Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
        curl_setopt($ch, CURLOPT_FILE, $fileResource);
        $page = curl_exec($ch);
        if(!$page) {
            echo "Error :- ".curl_error($ch);
            return false;
        }
        curl_close($ch);
        return $file;
    }
    
    
    /**
     * @param str $zipFile (filepath to zipfile) 
     * @param str $extractPath  (path of the folder to extract to)
     * @param bool $delete (if true, deletes zipfile after )
     */
    public static function extractZip($zipFile ,$extractPath = false, $delete = true ){
        if(!$extractPath)
            $extractPath = dirname($zipFile);
        //Openning the Zip file ...
        $zip = new ZipArchive;
        if($zip->open($zipFile) != "true"){
         echo "\r"."Error :- Unable to open the Zip File";
         return false;
        }
        //Extracting Zip File ...
        set_time_limit (self::TIME_LIMIT);
        $zip->extractTo($extractPath);
        $zip->close();
        //deleting...
        if($delete)
            unlink($zipFile);
    }
    
    
    /**
     * Deleting all  files and folders in specified folder
     * @param $dir path to directory
     * @param bool $delete if true deletes also folders
     */
    public static function deleteAllTempFiles($dir, $delete = false){
       if(is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
              if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") 
                   self::deleteAllTempFiles($dir."/".$object, true); 
                else unlink   ($dir."/".$object);
              }
            }
            reset($objects);
            if($delete === true)
                rmdir($dir);
        }
     }
     
     
     
     /**
      * encodes in UTF-8 objects,arrays, string
      * @param type $input
      */
    public static function utf8EncodeDeep(&$input) {
        if (is_string($input)) {
            $input = utf8_encode($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
               self::utf8_encode_deep($value);
            }

            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));

            foreach ($vars as $var) {
                self::utf8_encode_deep($input->$var);
            }
        }
        
    }  
    
    
    /**
     * Just formating error output
     * @param str $mes
     * @return array with error
     */
    public static function  setError($mes){
          return array('error'=> $mes,
                       'isFileEnd'=> true
              );
    }
    
    
    
    
    
    /**
     * trims string, array, object
     * @param mixed $input
     */
    public static function trimAny(&$input){
        if (is_string($input)) {
            $input = trim($input);
        } else if (is_array($input)) {
            foreach ($input as &$value) {
               self::trimAny($value);
            }
            unset($value);
        } else if (is_object($input)) {
            $vars = array_keys(get_object_vars($input));
            foreach ($vars as $var) {
                self::trimAny($input->$var);
            }
        }
   }
   
   
   /**
    * HELPER return dump of a variable
    * @param any $var
    * @return str
    */
   public static function getDump($var , $isStr = false){
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if($isStr){
            //removing new line and tab formatting
            $output = trim(preg_replace('/\s\s+/', ' ', $output));
        }
        return $output;
    }
    
    
    
    
}
