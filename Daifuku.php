<?php

namespace daifuku;

use daifuku\provider\Provider;
use daifuku\utils\CacheStore;
use daifuku\utils\FileLogger;
use daifuku\utils\Logger;

class Daifuku {

    private $worlds = [];

    private $provider;
    private $cache;

    private $debug = false;

    public function __construct(Provider $provider, $debug = false) {
        $this->provider = $provider;
        $this->debug = $debug;

        $this->cache = new CacheStore("./data", "data");
    }

    public function registerWorld($name, $url) {
        if(!isset($this->worlds[$name])) {
            $this->worlds[$name] = $url;
        }
    }

    public function getData($world, $every, $count, $cache = true) {
        if(!isset($this->worlds[$world])) {
            return false;
        }

        $time = floor(microtime(true) / 3600);

        $key = $this->getKey($world, $every, $count, $time);

        if($this->cache->isCached($key) and $cache) {
            return json_decode($this->cache->restore($key), true);
        }

        $data = $this->provider->getWorldInfoEvery($world, $every, $count, $this->provider->getLastTime($world));

        $this->cache->store($key, json_encode($data, JSON_UNESCAPED_UNICODE));

        return $data;
    }

    public function getAllData($every, $count, $cache) {
        //
    }

    public function collectData($world) {
        //
    }

    private function getKey($world, $every, $count, $time) {
        return $world . "-" . $every . "-" . $count . "-" . $time;
    }
}
