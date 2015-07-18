<?php
/**
 * @todo    General file information
 *
 * @package ...
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

/**
 * @todo   General class information
 */
class Text extends AbstractRendering {

	/**
	 * @return array
	 */
	public function renderInternal() {

		$lines[] = trim($this->breakContent(strip_tags($this->parseBody($this->contentObject->data['bodytext']))), CRLF . TAB);
		return $lines;

	}
}