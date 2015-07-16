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
 *
 * @author Tim Lochmüller
 */
class Html extends AbstractRendering {

	/**
	 * @param ContentObjectRenderer $contentObject
	 *
	 * @return array
	 */
	public function render($contentObject) {
		return array($this->breakContent(strip_tags($contentObject->data['bodytext'])));
	}
}