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
class Configuration {

	/**
	 * get the plaintext width
	 *
	 * @return int
	 */
	static public function getPlainTextWith() {
		return isset($GLOBALS['TSFE']->config['config']['plainTextWith']) ? (int)$GLOBALS['TSFE']->config['config']['plainTextWith'] : 76;
	}

	/**
	 * get the table mode
	 *
	 * @return string
	 */
	static public function getTableMode() {
		return isset($GLOBALS['TSFE']->config['config']['tableMode']) && trim($GLOBALS['TSFE']->config['config']['tableMode']) !== '' ? trim($GLOBALS['TSFE']->config['config']['tableMode']) : 'default';
	}

}