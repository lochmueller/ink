<?php
/**
 * @todo    General file information
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Service\Postprocessing;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * @todo   General class information
 *
 * @author Tim Lochmüller
 */
class InlineCsss implements PostprocessingInterface {

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	public function process($content) {
		GeneralUtility::requireOnce(ExtensionManagementUtility::extPath('ink', 'Resources/Private/Php/vendor/autoload.php'));
		$pattern = '%<(link|style)(?=[^<>]*?(?:type="(text/css)"|>))(?=[^<>]*?(?:media="([^<>"]*)"|>))(?=[^<>]*?(?:href="(.*?)"|>))(?=[^<>]*(?:rel="([^<>"]*)"|>))(?:.*?</\1>|[^<>]*>)%si';
		$matches = array();
		$css = '';
		preg_match_all($pattern, $content, $matches);
		if (isset($matches[0])) {
			foreach ($matches[0] as $key => $match) {
				if ($matches[1][$key] === 'style') {
					$css .= strip_tags($match);
				} elseif (strpos($match, 'type="text/css"') !== FALSE) {

					$file = preg_replace('/^(.+)\.(\d+)\.css$/', '$1.css', $matches[4][$key]);
					$parts = parse_url($file);
					if (isset($parts['query'])) {
						unset($parts['query']);
					}
					if (!isset($parts['host'])) {
						$parts['path'] = ltrim($parts['path'], '/');
					}

					$css .= GeneralUtility::getUrl(HttpUtility::buildUrl($parts));
				} else {
					continue;
				}
				$content = str_replace($match, '', $content);
			}
		}
		$format = new CssToInlineStyles($content, $css);
		return $format->convert();
	}
}
