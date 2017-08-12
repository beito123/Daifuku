<?php

namespace daifuku\provider;

use daifuku\utils\Logger;
use daifuku\utils\Utils;

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

    public function existsWorldData($world) {
        if($this->closed) {
            return false;
        }

        $exists = $this->pdo->query("SELECT count(*) FROM sqlite_master WHERE type='table' AND name='" . $world . "';")->fetch()["count(*)"];

        if ($exists >= 1) {
            return true;
        }

        return false;

    }

    public function createWorldData($world) {
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

        if(!$this->existsWorldData($world)) {
            $this->createWorldData($world);
        }

        $data = [];
        if($correct) {
            $data = $this->pdo->query("SELECT * FROM " . $world . " WHERE addtime = (SELECT MAX(addtime) FROM " . $world . " WHERE addtime_hour = " . $time . ");")->fetchAll();
        } else {
            $data = $this->pdo->query("SELECT * FROM " . $world . " WHERE addtime = (SELECT MAX(addtime) FROM " . $world . " WHERE addtime_hour <= " . $time . ");")->fetchAll();
        }

        return isset($data[0]) ? $data[0] : false;
    }

    public function getWorldInfoEvery($world, $every, $count, $time = -1, $correct = false) {
        if($this->closed) {
            return false;
        }

        if(!$this->existsWorldData($world)) {
            $this->createWorldData($world);
        }

        if($time < 0) {
            $time = $this->getLastTime($world);
        }

        $data = [];
        for($i = 0; $i < $count; $i++) {
            $time = $time - ($i > 0 ? $every : 0);

            $d = $this->getWorldInfo($world, $time, $correct);
            if($d !== false) {
                $data[] = $d;
                $time = $d["addtime_hour"];
            } else {
                if(!$correct) {
                    $last = $this->getLastTime($world, $time);
                    if($last === false) {
                        break;
                    }

                    $time = $last;
                    --$i;
                }
            }
        }

        return array_reverse($data);
    }

    public function getWorldInfoRange($world, $every, $count, $from, $to) {
        //
    }

    public function getWorldInfoAll($world) {
        //
    }

    public function setWorldInfo($world, $data) {
        if($this->closed) {
            return false;
        }

        if(!$this->existsWorldData($world)) {
            $this->createWorldData($world);
        }

        $fill = [
            "asset" => 0,
            "people" => 0,
            "salary" => 0,
            "per_assets" => 0,
            "upd" => 0,
            "online_players" => [],
        ];

        $data = Utils::fillArray($data, $fill);

        $asset = str_replace(",", "", $data['asset']);
        $salary = $data['salary'];

        preg_match('/^[\d.]+/', $data['people'], $match);
        $people = (isset($match[0])) ? $match[0]:0;

        $per_assets = str_replace(",", "", $data['per_assets']);

        $names = array();
        foreach($data["online_players"] as $v) {
            $names[] = $v["name"];
        }
        $online_players = implode("/", $names);

        $upd = strtotime($data['upd']);
        $addtime = (int) microtime(true);
        $addtime_hour = floor($addtime / 3600);
        $addtime_date = date("Y/m/d", $addtime);

        $this->pdo->exec("INSERT INTO " . $world . "(asset, salary, people, per_assets, online_players, upd, addtime, addtime_hour, addtime_date) VALUES(
            " . $asset . ",
            " . $salary . ",
            " . $people . ",
            " . $per_assets . ",
            '" . $online_players . "',
            " . $upd . ",
            " . $addtime . ",
            " . $addtime_hour . ",
            '" . $addtime_date . "'
        );");
    }
}
