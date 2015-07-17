<?php
/**
 * Remove generator
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Postprocessing;

/**
 * Remove generator
 */
class RemoveGenerator implements PostprocessingInterface {

	/**
	 * Remove the generator tag
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function process($content) {
		return preg_replace('/<meta name="?generator"?.+?>/is', '', $content);
	}
}
