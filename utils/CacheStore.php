<?php

namespace daifuku\utils;

class CacheStore {

    const SEPARATION = "-";

    private $path;

    public function __construct($path, $prefix = null) {
        $this->path = realpath($path) . "/";
    }

    public function getPath() {
        return $this->path;
    }

    public function getCachePath($key) {
        if($this->prefix !== null) {
            return $this->path . $prefix . SEPARATION.  $key . ".dat";
        }

        return $this->path . $key . ".dat";
    }

    public function isCached($key) {
        $path = $this->getCachePath($key);

        return file_exists($path) and !is_dir($path);
    }

    public function store($key, $data) {
        if(!is_string($data)) {
            throw new Exception("data arguments must be string");

            return false;
        }

        $path = $this->getCachePath($key);

        $dir = dirname($path);
        if(!file_exists($dir) and !is_dir($dir)) {
            @mkdir($dir);
        }

        return file_put_contents($path, $data) !== false;
    }

    public function restore($key) {
        if(!$this->isCached($key)) {
            return false;
        }

        return file_get_contents($this->getCachePath($key));
    }

    public function removeCache($key) {
        if(!$this->isCached($key)) {
            return false;
        }

        unlink($this->getCachePath($key));
    }

    public function removeCaches() {
        Utils::removeDir($this->path);
    }
}
