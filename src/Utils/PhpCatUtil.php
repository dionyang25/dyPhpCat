<?php
namespace PhpCat\Utils;
use PhpCat\PhpCat;

/**
 * laravel util
 * Class PhpCatUtil
 * @package PhpCat\Utils
 */

class PhpCatUtil
{
    private static $phpCatInstance;

    private static $transaction;

    /**
     * @return PhpCat|null
     */
    public static function getPhpCat()
    {
        if (self::$phpCatInstance != null && self::$phpCatInstance instanceof PhpCat) {
            return self::$phpCatInstance;
        }
        if (!function_exists("env")) {
            return null;
        }
        $domain = env("CAT_DOMAIN");
        $server = env("CAT_SERVER");
        $ip = env("CAT_PORT");
        $timeout = env("CAT_TIMEOUT");
        if (empty($domain) || empty($server) || empty($ip)) {
            return null;
        }
        if (empty($timeout)) {
            $timeout = 300000;
        }
        $servers = [[$server, $ip]];
        self::$phpCatInstance = new PhpCat($domain, $servers, $timeout);
        return self::$phpCatInstance;
    }

    public static function setTransaction($transaction)
    {
        self::$transaction = $transaction;
    }

    public static function getTransaction()
    {
        return self::$transaction;
    }
}