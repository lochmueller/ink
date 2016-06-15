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
 */
class TypoScriptFrontend
{

    /**
     * Call the end of rendering hook
     *
     * @hook TYPO3_CONF_VARS|SC_OPTIONS|tslib/class.tslib_fe.php|hook_eofe
     *
     * @param array                        $params
     * @param TypoScriptFrontendController $obj
     */
    public function endOfRendering(array $params, TypoScriptFrontendController $obj)
    {
        if (!isset($obj->config['config']['newsletterPostprocessing.']) || !is_array($obj->config['config']['newsletterPostprocessing.'])) {
            return;
        }

        foreach ($obj->config['config']['newsletterPostprocessing.'] as $postprocessorClass) {
            /** @var PostprocessingInterface $processor */
            $processor = GeneralUtility::makeInstance($postprocessorClass);
            $obj->content = $processor->process($obj->content);
        }
    }
}
