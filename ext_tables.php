<?php
/**
 * General ext_tables file and also an example for your own extension
 *
 * @category Extension
 * @author   Tim Lochmüller
 */

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\HDNET\Autoloader\Loader::extTables('FRUIT', 'ink');