<?php
/**
 * Render text w/ picture element
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

/**
 * Render text w/ picture element
 */
class TextPicture extends AbstractRendering {

	/**
	 * Render the given element
	 *
	 * @return array
	 */
	public function renderInternal() {
		$image = new Image();
		if (!($this->contentObject->data['imageorient'] & 24)) {
			$lines = $image->render($this->contentObject, $this->configuration);
			$lines[] = '';
		}
		$lines[] = $this->breakContent(strip_tags($this->parseBody($this->contentObject->data['bodytext'])));
		if ($this->contentObject->data['imageorient'] & 24) {
			$lines[] = '';
			$lines = array_merge($lines, $image->render($this->contentObject, $this->configuration));
		}
		return $lines;
	}
}