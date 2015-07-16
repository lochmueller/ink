<?php
/**
 * @todo    General file information
 *
 * @package ...
 * @author  Tim Lochmüller
 */

/**
 * @todo   General class information
 *
 * @author Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;


use TYPO3\CMS\Core\Utility\MailUtility;

abstract class AbstractRendering implements RenderingInterface {

	/**
	 * Function used to wrap the bodytext field content (or image caption) into lines of a max length of
	 *
	 * @param        string $str : The content to break
	 *
	 * @return        string                Processed value.
	 * @see main_plaintext(), breakLines()
	 */
	function breakContent($str) {
		$cParts = explode(chr(10), $str);
		$lines = array();
		foreach ($cParts as $substrs) {
			$lines[] = $this->breakLines($substrs, LF);
		}
		return implode(chr(10), $lines);
	}

	/**
	 * Breaking lines into fixed length lines, using t3lib_div::breakLinesForEmail()
	 *
	 * @param        string  $str       : The string to break
	 * @param        string  $implChar  : Line break character
	 * @param        integer $charWidth : Length of lines, default is $this->charWidth
	 *
	 * @return        string                Processed string
	 */
	function breakLines($str, $implChar = LF, $charWidth = 76) {
		return MailUtility::breakLinesForEmail($str, $implChar, $charWidth);
	}
}