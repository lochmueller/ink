<?php
/**
 * Render the HTML element
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Render the HTML element
 */
class Html extends AbstractRendering {

	/**
	 * Get the liens for the current HTML element
	 *
	 * @param ContentObjectRenderer $contentObject
	 *
	 * @return array
	 */
	public function render($contentObject) {
		return array($this->breakContent(strip_tags($contentObject->data['bodytext'])));
	}
}