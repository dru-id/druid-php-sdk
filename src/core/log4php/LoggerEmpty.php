<?php

/**
 * This is the Mock Logger class to not include all logs if LOG_LEVEL = NONE
  *
 * The main logging methods are empty:
 * 	<ul>
 * 		<li>{@link trace()}</li>
 * 		<li>{@link debug()}</li>
 * 		<li>{@link info()}</li>
 * 		<li>{@link warn()}</li>
 * 		<li>{@link error()}</li>
 * 		<li>{@link fatal()}</li>
 * 	</ul>
 *
 * @package    log4php
 * @license	   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link	   http://logging.apache.org/log4php
 */

class Logger {
    public function __construct (){}

    public static function getLogger($name) {
        return new Logger();
    }

    public function error($string){}

    public function info($string){}

    public function debug($string) {}

    public function trace($string){}

    public function warn($string){}

    public function fatal($string){}

    public function showLog(){return '';}
}