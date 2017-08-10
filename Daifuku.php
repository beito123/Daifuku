<?php

namespace daifuku;

use daifuku\utils\CacheStore;
use daifuku\utils\FileLogger;
use daifuku\utils\Logger;

class Daifuku {

    private $debug = false;

    public function __construct(Provider $provider, $debug = false) {
        $this->provider = $provider;
        $this->debug = $debug;
    }

    public function getData($world, $every, $count, $cache = true) {
        //
    }

    public function collectData() {
        //
    }
}
