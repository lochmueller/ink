<?php
/**
 * @todo    General file information
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @todo General class information
 */
class Templavoila extends AbstractRendering {

	/**
	 * Render the given element
	 *
	 * @return array
	 */
	public function renderInternal() {

		// TV nicht geladen
		if (!ExtensionManagementUtility::isLoaded('templavoila')) {
			$defaultOutput = $this->configuration['defaultOutput'];
			if ($defaultOutput) {
				$defaultOutput = str_replace('###CType###', $this->contentObject->data['CType'], $defaultOutput);
			}
			return $defaultOutput;
		}
		$pi1 = GeneralUtility::makeInstance('tx_templavoila_pi1');
		$pi1->cObj = $this->contentObject;
		$lines[] = $pi1->renderElement($this->contentObject->data, 'tt_content');
		return $lines;
	}
}