<?php
/**
 * Render the header
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use FRUIT\Ink\Configuration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Render the header
 */
class Header extends AbstractRendering {

	/**
	 * Render the given element
	 *
	 * @param ContentObjectRenderer $contentObject
	 *
	 * @return array
	 */
	public function render($contentObject) {
		$headerWrap = MailUtility::breakLinesForEmail(trim($contentObject->data['header']), LF, Configuration::getPlainTextWith());
		$subHeaderWrap = MailUtility::breakLinesForEmail(trim($contentObject->data['subheader']), LF, Configuration::getPlainTextWith());

		// align
		$header = array_merge(GeneralUtility::trimExplode(LF, $headerWrap, TRUE), GeneralUtility::trimExplode(LF, $subHeaderWrap, TRUE));
		if ($contentObject->data['header_position'] == 'right') {
			foreach ($header as $key => $l) {
				$l = trim($l);
				$header[$key] = str_pad(' ', (Configuration::getPlainTextWith() - strlen($l)), ' ', STR_PAD_LEFT) . $l;
			}
		} elseif ($contentObject->data['header_position'] == 'center') {
			foreach ($header as $key => $l) {
				$l = trim($l);
				$header[$key] = str_pad(' ', floor((Configuration::getPlainTextWith() - strlen($l)) / 2), ' ', STR_PAD_LEFT) . $l;
			}
		}
		$header = implode(LF, $header);
		$lines[] = $header;
		return $lines;
	}
}