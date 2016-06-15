<?php
/**
 * Configuration object
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink;

/**
 * Configuration object
 */
class Configuration
{

    /**
     * get the plaintext width
     *
     * @return int
     */
    public static function getPlainTextWith()
    {
        return isset($GLOBALS['TSFE']->config['config']['plainTextWith']) ? (int)$GLOBALS['TSFE']->config['config']['plainTextWith'] : 76;
    }

    /**
     * get the table mode
     *
     * @return string
     */
    public static function getTableMode()
    {
        return isset($GLOBALS['TSFE']->config['config']['tableMode']) && trim($GLOBALS['TSFE']->config['config']['tableMode']) !== '' ? trim($GLOBALS['TSFE']->config['config']['tableMode']) : 'default';
    }

    /**
     * check if the plain tables are 100 displayed
     *
     * @return string
     */
    public static function isPlainTable100()
    {
        return isset($GLOBALS['TSFE']->config['config']['plainTable100']) && trim($GLOBALS['TSFE']->config['config']['plainTable100']) !== '' ? (bool)$GLOBALS['TSFE']->config['config']['plainTable100'] : true;
    }
}
