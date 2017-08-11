<?php

namespace daifuku;

use daifuku\parser\Parser;
use daifuku\provider\Provider;
use daifuku\utils\CacheStore;
use daifuku\utils\FileLogger;
use daifuku\utils\Logger;
use daifuku\utils\Utils;

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

        $time = floor(microtime(true) / 3600);//cache time

        $key = $this->getKey($world, $every, $count, $time);

        if($this->cache->isCached($key) and $cache) {
            return json_decode($this->cache->restore($key), true);
        }

        $data = $this->provider->getWorldInfoEvery($world, $every, $count);

        $this->cache->store($key, json_encode($data, JSON_UNESCAPED_UNICODE));

        return $data;
    }

    public function getAllData($every, $count, $cache = true) {//TODO: rewrite
        $time = floor(microtime(true) / 3600);//cache time

        $key = $this->getKey("all", $every, $count, $time);

        if($this->cache->isCached($key) and $cache) {
            return json_decode($this->cache->restore($key), true);
        }

        $base = [];
        $date = [];
        foreach($this->worlds as $world => $value) {
            $base[$world] = $this->provider->getWorldInfoEvery($world, $every, $count);
            
            foreach($base[$world] as $v) {
                $date[] = $v["addtime_hour"];//
            }
        }

        $date = array_unique($date, SORT_NUMERIC);

        sort($date, SORT_NUMERIC);

        $b = end($date);

        $ndate = [];
        for($i = 0; $i < $count; $i++) {
            $d = $b - ($every * $i);
            if(in_array($d, $date)) {
                $ndate[] = $d;
            } else {
                foreach($date as $v) {
                    if($v < $d) {
                        $b = $v;
                    }
                }
            }
        }

        $data = [];
        foreach($ndate as $k => $d) {
            foreach($base as $w => $val) {
                foreach($val as $v) {
                    if ($v["addtime_hour"] == $d) {
                        $data[$w][$k] = $v;
                        break;
                    }

                    $data[$w][$k] = "null";
                }
            }
        }

        $list = [
            "date" => $ndate,
            "data" => $data
        ];

        $this->cache->store($key, json_encode($list, JSON_UNESCAPED_UNICODE));

        return $list;
    }

    public function collectData(Parser $parser, $world, $try = 2) {
        if(!isset($this->worlds[$world])) {
            return false;
        }

        $url = $this->worlds[$world];
        $opts = array(
            CURLOPT_USERAGENT => "JagaBot/1.0",
            CURLOPT_TIMEOUT_MS => 5 * 1000
        );

        $buf = "";
        for($i = 0;$i < $try;$i++) {
            $buf = Utils::getAPI($url, $opts);
            if(strlen($buf) > 0) {
                break;
            }
        }

        if(strlen($buf) <= 0) {
            $this->logger->error("Could not get the data from " . $url);
            return false;
        }

        $data = $parser->parseHTML($buf);
        if(count($data) <= 0) {
            $this->logger->error("Failed to parse the html.");
            return false;
        }

        try {
            $this->provider->setWorldInfo($world, $data);
        } catch(Exception $e) {
            $this->logger->error("Could not set the data Exception:", $e);
        }

        return true;
    }

    private function getKey($world, $every, $count, $time) {
        return $world . "-e" . $every . "-c" . $count . "-t" . $time;
    }
}
