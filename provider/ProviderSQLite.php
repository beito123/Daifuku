<?php

namespace daifuku\provider;

use daifuku\utils\Logger;

use PDO;

class ProviderSQLite implements Provider {

    public $path;

    public $closed = true;

    private $pdo = null;

    public function __construct(Logger $logger, $path = null) {
        if(!$this->closed) {
            return;
        }

        $this->logger = $logger;
        $this->path = $path;

        $this->closed = false;

        if($this->path === null) {
            $this->logger->fatal("Set a path for database");
        }

        $this->pdo = new PDO('sqlite:' . $path);

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getName() {
        return "SQLite";
    }

    public function close() {
        if(!$this->closed) {
            $this->pdo = null;

            $this->closed = true;
        }
    }

    public function isClosed() {
        return $this->closed;
    }

    public function getLastTime($world, $off = 0) {
        $time = false;

        $b = [];
        if($off > 0) {
            $b = $this->pdo->query("SELECT addtime_hour FROM " . $world .
                " WHERE addtime_hour = (SELECT MAX(addtime_hour) FROM " . $world . " WHERE addtime_hour < " . $off . ") ORDER BY addtime_hour DESC LIMIT 1;")->fetchAll();
        } else {
            $b = $this->pdo->query("SELECT addtime_hour FROM " . $world . " WHERE addtime = (SELECT MAX(addtime) FROM " . $world . ");")->fetchAll();
        }


        $first = reset($b);
        if($first !== false) {
            $time = $first["addtime_hour"];
        }

        return $time;
    }

    public function existsWorldData($name) {
        if($this->closed) {
            return false;
        }

        $exists = $this->pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='" . $name . "';")->fetch()["count(*)"];

        if ($exists >= 1) {
            return true;
        }

        return false;

    }

    public function createWorldData($name) {
        if($this->closed) {
            return false;
        }

        $this->pdo->exec("CREATE TABLE [" . $world . "] (
            [asset] INTEGER NOT NULL,
            [salary] INTEGER NOT NULL,
            [people] INTEGER NOT NULL,
            [per_assets] INTEGER NOT NULL,
            [online_players] TEXT,
            [upd] INTEGER NOT NULL,
            [addtime] INTEGER NOT NULL,
            [addtime_hour] INTEGER NOT NULL,
            [addtime_date] TEXT NOT NULL
        );");
    }

    public function getWorldInfo($world, $time, $correct = false){
        if($this->closed) {
            return false;
        }

        $data = [];
        if($correct) {
            $data = $this->pdo->query("SELECT * FROM " . $world . " WHERE addtime = (SELECT MAX(addtime) FROM " . $world . " WHERE addtime_hour = " . $time . ");")->fetchAll();
        } else {
            $data = $this->pdo->query("SELECT * FROM " . $world . " WHERE addtime = (SELECT MAX(addtime) FROM " . $world . " WHERE addtime_hour <= " . $time . ");")->fetchAll();
        }

        return isset($data[0]) ? $data[0] : false;
    }

    public function getWorldInfoEvery($world, $every, $count, $time, $correct = false) {
        if($this->closed) {
            return false;
        }

        $data = [];
        for($i = 0; $i < $count; $i++) {
            $time = $time - ($i > 0 ? $every : 0);

            $d = $this->getWorldInfo($world, $time, $correct);
            if($d !== false) {
                $data[] = $d;
                $time = $d["addtime_hour"];
            } else {
                if(!$current) {
                    $time = $this->getLastTime($world, $time);
                    --$i;
                }
            }
        }

        return $data;
    }

    public function getWorldInfoRange($world, $every, $count, $from, $to) {

    }

    public function getWorldInfoAll($world) {

    }
}
