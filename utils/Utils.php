<?php

namespace daifuku\utils;

class Utils {

    public static function removeDir($dir) {
        if (is_dir($dir) and !is_link($dir)) {
            array_map('self::removeDir',   glob($dir.'/*', GLOB_ONLYDIR));
            array_map('unlink', glob($dir.'/*'));
            rmdir($dir);
        }
    }

    public static function getAPI($url, $opts) {
    	$opts[CURLOPT_RETURNTRANSFER] = true;

	    $ch = curl_init($url);
	    curl_setopt_array($ch, $opts);

	    $data    = curl_exec($ch);
	    $info    = curl_getinfo($ch);
	    $errorNo = curl_errno($ch);

	    if ($errorNo !== CURLE_OK) {
	    	return false;
	    }

	    if ($info['http_code'] !== 200) {
	        return false;
	    }

	    return $data;
    }

    public static function fillArray($array, $fill) {
    	foreach($fill as $k => $v) {
    		if(!isset($array[$k])) {
    			$array[$k] = $v;
    		}
    	}

    	return $array;
    }
}
