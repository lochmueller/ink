<?php
/**
 * General ext_tables file
 *
 * @author   Tim Lochmüller
 */

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\HDNET\Autoloader\Loader::extTables('FRUIT', 'ink');
