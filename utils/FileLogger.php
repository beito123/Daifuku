<?php

namespace daifuku\utils;

class FileLogger implements Logger {

    public static $dateFormat = "Y/m/d H:i:s";
    public static $levels = [
        Logger::LEVEL_INFO => "INFO",
        Logger::LEVEL_NOTICE => "NOTICE",
        Logger::LEVEL_WARNING => "WARNING",
        Logger::LEVEL_ERROR => "ERROR",
        Logger::LEVEL_DEBUG => "DEBUG",
    ];

    public $path;
    public $debug = false;

    private $fp = null;

    public function __construct($path, $debug = false) {
        $this->path = $path;
        $this->debug = $debug;

        $dir = dirname($path);
        if(!file_exists($dir) and !is_dir($dir)) {
            mkdir($dir);
        }

        $this->fp = fopen($path, "a");
        if(!$this->fp) {
            trigger_error("Cloud not open the file. path:" . $path, E_USER_ERROR);
        }

        if($debug){
            error_reporting(E_ALL);
        }else{
            error_reporting(0);
        }
    }

    public function __destruct() {
        if($this->fp !== null) {
            fclose($this->fp);
        }
    }

    public function info($msg) {
        $this->log($msg, Logger::LEVEL_INFO);
    }

    public function notice($msg) {
        $this->log($msg, Logger::LEVEL_NOTICE);
    }

    public function warning($msg) {
        $this->log($msg, Logger::LEVEL_WARNING);
    }

    public function error($msg) {
        $this->log($msg, Logger::LEVEL_ERROR);
    }

    public function fatal($msg, Exception $e = null) {
        $this->log($msg, Logger::LEVEL_FATAL);

        $dbg = debug_backtrace();

        $fun = isset($dbg[1]["function"]) ? $dbg[1]["function"] : "null";
        $class = isset($dbg[1]["class"]) ? $dbg[1]["class"] : "null";

        if($e !== null) {//TODO: msg
            $line = $e->getLine();
            $file = $e->getFile();
        } else {
            $line = isset($dbg[1]["line"]) ? $dbg[1]["line"] : "null";
            $file = isset($dbg[1]["file"]) ? $dbg[1]["file"] : "null";
        }

        $info = " line." . $line . " at " . $fun . " in " . $class . " file:" . $file;

        throw new Exception("Exception: " . $msg . $info);

        exit(0);
    }

    public function debug($msg) {
        $this->log($msg, Logger::LEVEL_DEBUG);
    }

    public function log($msg, $level = Logger::LEVEL_INFO) {
        if($level === Logger::LEVEL_DEBUG) {
            if(!$this->debug) {
                return;
            }
        }

        $date = date(FileLogger::$dateFormat);
        $lvl = isset(FileLogger::$levels[$level]) ? FileLogger::$levels[$level]:FileLogger::$levels[Logger::LEVEL_INFO];

        @fwrite($this->fp, "[" . $level . "]" . $date . ":" + $msg);//[LEVEL] 2017/07/01 12:15:12:MESSAGE
    }
}
