<?php
/**
 * 工具类:环境变量
 * @author: ahuazhu@gmail.com
 * Date: 16/7/18  下午10:27
 */

namespace PhpCat\Utils;


class Env
{
    public static function isThreadSupport() {
        return false;
    }

    public static function isLinux() {
        return strcmp(PHP_OS, 'Linux') == 0;
    }
}