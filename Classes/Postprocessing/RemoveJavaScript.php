<?php
/**
 * Remove all JS code
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Postprocessing;

/**
 * Remove all JS code
 */
class RemoveJavaScript implements PostprocessingInterface {

	/**
	 * Run the replacement
	 *
	 * @param string $content
	 *
	 * @return string
	 * @todo remove also onchange etc.
	 */
	public function process($content) {
		return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
	}
}
