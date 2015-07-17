<?php
/**
 * Rendering interface
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Rendering interface
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