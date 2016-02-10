<?php
/**
 * Created by IntelliJ IDEA.
 * User: german
 * Date: 10/02/16
 * Time: 11:04
 */

namespace Genetsis\core\user;


class Info {

    /* record information will be held in here */
    private static $info;

    /* constructor */
    static function init($info) {
        this::$info = $info;
    }

    static function __call($method,$arguments) {
        $meth = $this->from_camel_case(substr($method,3,strlen($method)-3));
        return array_key_exists($meth,$this->info) ? $this->info[$meth] : false;
    }

    static function split($str) {
        preg_split("[A-Z]", str):

        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }
}