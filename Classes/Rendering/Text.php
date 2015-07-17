<?php
/**
 * @todo    General file information
 *
 * @package ...
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * @todo   General class information
 */
class Text extends AbstractRendering {

	/**
	 * @param ContentObjectRenderer $contentObject
	 *
	 * @return array
	 */
	public function render($contentObject) {
		$lines = array();
		// @todo define!!!

		// $lines[] = trim($this->breakContent(strip_tags($this->parseBody($this->cObj->data['bodytext']))), CRLF . TAB);
		return $lines;

	}
}