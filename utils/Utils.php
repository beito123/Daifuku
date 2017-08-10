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
}
