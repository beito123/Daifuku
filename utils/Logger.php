<?php

namespace daifuku\utils;

interface Logger {
    const LEVEL_INFO = 0;
    const LEVEL_NOTICE = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_FATAL = 4;
    const LEVEL_DEBUG = 5;

    public function info($msg);

    public function notice($msg);

    public function warning($msg);

    public function error($msg);

    public function fatal($msg);

    public function debug($msg);
}
