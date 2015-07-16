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
interface RenderingInterface {

	/**
	 * Render the given element
	 *
	 * @param ContentObjectRenderer $contentObject
	 *
	 * @return array
	 */
	public function render($contentObject);

}