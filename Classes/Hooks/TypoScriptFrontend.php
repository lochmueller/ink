<?php
/**
 * @todo    General file information
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Hooks;

use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @todo   General class information
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
		if (!isset($obj->config['config']['newsletterPreparation'])) {
			return;
		}
		$obj->content = $this->removeGenerator($obj->content);
		$obj->content = $this->removeComments($obj->content);
		$obj->content = $this->removeJavaScript($obj->content);
		$obj->content = $this->inlineCss($obj->content);

	}

	/**
	 * Remove generator meta tag
	 * <meta name="generator" content="TYPO3 6.2 CMS">
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function removeGenerator($content) {
		return preg_replace('/<meta name="?generator"?.+?>/is', '', $content);
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function removeJavaScript($content) {
		return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $content);
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function removeComments($content) {
		return preg_replace('/<!--(.*?)-->/s', '', $content);
	}

	/**
	 * @param $content
	 *
	 * @return string
	 * @throws \TijsVerkoyen\CssToInlineStyles\Exception
	 */
	protected function inlineCss($content) {
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
