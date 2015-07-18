<?php
/**
 * Render Ctype menu
 *
 * @author Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use FRUIT\Ink\Configuration;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Render Ctype menu
 */
class Menu extends AbstractRendering {

	/**
	 * Render the given element
	 *
	 * @return array
	 */
	public function renderInternal() {
		$str = $this->contentObject->cObjGetSingle($this->configuration['menu'], $this->configuration['menu.']);
		$str = $this->breakBulletList($this->contentObject, trim(strip_tags(preg_replace('/<br\s*\/?>/i', chr(10), $this->parseBody($str)))));
		return $str;
	}

	/**
	 * Breaks content lines into a bullet list
	 *
	 * @param ContentObjectRenderer $contentObject
	 * @param string                $str Content string to make into a bullet list
	 *
	 * @return string Processed value
	 */
	function breakBulletList($contentObject, $str) {
		$type = $contentObject->data['layout'];
		$type = MathUtility::forceIntegerInRange($type, 0, 3);

		$tConf = $this->configuration['bulletlist.'][$type . '.'];

		$cParts = explode(chr(10), $str);
		$lines = array();
		$c = 0;

		foreach ($cParts as $substrs) {
			if (!strlen($substrs)) {
				continue;
			}
			$c++;
			$bullet = $tConf['bullet'] ? $tConf['bullet'] : ' - ';
			$bLen = strlen($bullet);
			$bullet = substr(str_replace('#', $c, $bullet), 0, $bLen);
			$secondRow = substr($tConf['secondRow'] ? $tConf['secondRow'] : str_pad('', strlen($bullet), ' '), 0, $bLen);

			$lines[] = $bullet . $this->breakLines($substrs, chr(10) . $secondRow, Configuration::getPlainTextWith() - $bLen);

			$blanks = MathUtility::forceIntegerInRange($tConf['blanks'], 0, 1000);
			if ($blanks) {
				$lines[] = str_pad('', $blanks - 1, chr(10));
			}
		}
		return implode(chr(10), $lines);
	}
}