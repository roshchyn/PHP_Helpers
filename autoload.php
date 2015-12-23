<?php
/*
* Autoload for Helpers
Put in the same folder as Helpers folder
*/
 spl_autoload_register(
        function ($className) {
            $classPath = explode('_', $className);
            if (count($classPath) > 1) {
                $localPath = implode('/', $classPath) . '.php';
            }else{
                $localPath = $className . '.php';
            }
            $filePath = dirname(__FILE__) . '/' . $localPath;
            if (file_exists($filePath)) {
                require_once($filePath);
            }
        }
);
