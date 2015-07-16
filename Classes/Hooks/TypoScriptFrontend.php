<?php
/**
 * Hook the TSFE output
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Hooks;

use FRUIT\Ink\Postprocessing\PostprocessingInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Hook the TSFE output
 *
 * @author Tim Lochmüller
 */
class TypoScriptFrontend {

	/**
	 * @hook TYPO3_CONF_VARS|SC_OPTIONS|tslib/class.tslib_fe.php|hook_eofe
	 *
	 * @param array                        $params
	 * @param TypoScriptFrontendController $obj
	 */
	public function endOfRendering(array $params, TypoScriptFrontendController $obj) {
		$postprocessor = array();
		if (isset($obj->config['config']['newsletterHtmlPreparation'])) {
			// HTML
			$postprocessor[] = 'FRUIT\Ink\\Postprocessing\\RemoveGenerator';
			$postprocessor[] = 'FRUIT\Ink\\Postprocessing\\RemoveHtmlComments';
			$postprocessor[] = 'FRUIT\Ink\\Postprocessing\\RemoveJavaScript';
			$postprocessor[] = 'FRUIT\Ink\\Postprocessing\\InlineCss';
		} elseif (isset($obj->config['config']['newsletterPlainPreparation'])) {
			// TXT
			$obj->content = rtrim($this->trimAllLines($obj->content));
			$postprocessor[] = 'FRUIT\Ink\\Postprocessing\\RemoveMultipleEmptyLines';
		}

		foreach ($postprocessor as $pp) {
			/** @var PostprocessingInterface $processor */
			$processor = GeneralUtility::makeInstance($pp);
			$obj->content = $processor->process($obj->content);
		}
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 * @todo move to separate class
	 */
	protected function trimAllLines($content) {
		return implode("\n", GeneralUtility::trimExplode("\n", $content));
	}

}
