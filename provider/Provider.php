<?php

namespace daifuku\provider;

interface Provider {

    /**
     * Returns the name of the provider.
     * @return string the name of the provider
     */
    public function getName();

    /**
     * Initialize the provider.
     * @param $logger  logger
     * @return boolean whether it succeeded or not
     */
    public function init(Logger $logger);

    /**
     * Close the provider.
     * @return void
     */
    public function close();

    /**
     * Returns whether data exists.
     * @param  string  $name world name
     * @return boolean exists data of the world
     */
    public function existsWorldData($name);

    /**
     * Creates world data.
     * @param  string $name world name
     * @return boolean      returns true on success, false on failure
     *
     */
    public function createWorldData($name);

    public function getWorldInfo($world, $time, $correct = false);

    /**
     * Gets information of world.
     * @param  string $world world name
     * @param  int    $every the length of the interval (1 = every a hour, 24 = every 24 hour)
     * @param  int    $count number to get
     * @param  int    $time  base htime
     * @return array         gotten information
     */
    public function getWorldInfoEvery($world, $every, $count, $time);

    public function getWorldInfoRange($world, $every, $count, $from, $to);

    public function getWorldInfoAll($world);
}
