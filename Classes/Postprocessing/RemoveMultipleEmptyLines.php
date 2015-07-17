<?php
/**
 * Remove multiple empty lines
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Postprocessing;

/**
 * Remove multiple empty lines
 */
class RemoveMultipleEmptyLines implements PostprocessingInterface {

	/**
	 * Run the cleanup of the empty lines
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function process($content) {
		return preg_replace('/\n{4,}/', "\n\n\n", $content);
	}
}
