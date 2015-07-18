<?php

/**
 * PlainText Service
 */

namespace FRUIT\Ink;

use FRUIT\Ink\Rendering\RenderingInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Reflection\MethodReflection;

/**
 *  PlainText Service
 */
class PlainRenderer {

	/**
	 * The content object
	 *
	 * @var \TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer
	 */
	public $cObj;

	/**
	 * Configuration
	 *
	 * @var
	 */
	protected $conf;

	/**
	 * Registerd cTypes
	 *
	 * @var array
	 */
	protected $registeredCTypes = array();

	/**
	 * Build up the object
	 */
	function __construct() {
		$this->registerCType();
	}

	/**
	 * Register a CType
	 */
	protected function registerCType() {
		/** @var $classReflection \TYPO3\CMS\Extbase\Reflection\ClassReflection */
		$classReflection = GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Reflection\\ClassReflection', get_class($this));
		$aMethods = $classReflection->getMethods();

		foreach ($aMethods as $method) {
			/** @var MethodReflection $method */
			if ($method->isTaggedWith('CType')) {
				$cType = implode('', $method->getTagValues('CType'));
				$methodName = $method->getName();
				$this->registeredCTypes[$cType] = $methodName;
			}
		}
	}

	/**
	 * Check if a cType is registered
	 *
	 * @param string $cType
	 *
	 * @return boolean
	 */
	public function isRegistered($cType) {
		return isset($this->registeredCTypes[$cType]);
	}

	/**
	 * Function used to wrap the bodytext field content (or image caption) into lines of a max length of
	 *
	 * @param        string $str : The content to break
	 *
	 * @return        string                Processed value.
	 * @see main_plaintext(), breakLines()
	 */
	function breakContent($str) {
		$cParts = explode(chr(10), $str);
		$lines = array();
		foreach ($cParts as $substrs) {
			$lines[] = $this->breakLines($substrs, LF);
		}
		return implode(chr(10), $lines);
	}

	/**
	 * Breaking lines into fixed length lines, using GeneralUtility::breakLinesForEmail()
	 *
	 * @param        string  $str       : The string to break
	 * @param        string  $implChar  : Line break character
	 * @param        integer $charWidth : Length of lines, default is $this->charWidth
	 *
	 * @return        string                Processed string
	 */
	function breakLines($str, $implChar = LF, $charWidth = 76) {
		return MailUtility::breakLinesForEmail($str, $implChar, Configuration::getPlainTextWith());
	}

	/**
	 * Render the different elements and collect the single lines.
	 * After the rendering the lines will be imploded. Notice:
	 * All methods after this are CType rendering helper
	 *
	 * @param string $content
	 * @param array  $conf
	 *
	 * @return array
	 */
	public function render($content, $conf) {
		$lines = array();
		$this->conf = $conf;
		$CType = (string)$this->cObj->data['CType'];
		if (isset($this->conf['forceCType']) && trim($this->conf['forceCType']) !== '') {
			$CType = trim($this->conf['forceCType']);
		}

		$renderer = array(
			'html'    => 'FRUIT\\Ink\\Rendering\\Html',
			'header'  => 'FRUIT\\Ink\\Rendering\\Header',
			'table'   => 'FRUIT\\Ink\\Rendering\\Table',
			#'menu'   => 'FRUIT\\Ink\\Rendering\\Menu',
			'text'    => 'FRUIT\\Ink\\Rendering\\Text',
			'image'   => 'FRUIT\\Ink\\Rendering\\Image',
			'textpic' => 'FRUIT\\Ink\\Rendering\\TextPicture',
		);

		if (isset($renderer[$CType])) {
			$className = $renderer[$CType];
			/** @var RenderingInterface $renderObject */
			$renderObject = GeneralUtility::makeInstance($className);
			$lines = $renderObject->render($this->cObj, $this->conf);
		} else {

			if ($this->isRegistered($CType)) {
				$func = $this->registeredCTypes[$CType];
				$lines = $this->$func($lines);
			} else {
				$lines[] = 'CType: ' . $CType . ' have no rendering definitions';
			}
		}
		$content = implode(LF, $lines);
		return trim($content, CRLF . TAB);
	}

	/**
	 * Render TV FCE
	 *
	 * @CType templavoila_pi1
	 *
	 * @param array $lines
	 *
	 * @return array
	 */
	public function renderTemplavoilaPi1($lines = array()) {
		// TV nicht geladen
		if (!ExtensionManagementUtility::isLoaded('templavoila')) {
			$defaultOutput = $this->conf['defaultOutput'];
			if ($defaultOutput) {
				$defaultOutput = str_replace('###CType###', $this->cObj->data['CType'], $defaultOutput);
			}
			return $defaultOutput;
		}
		$pi1 = GeneralUtility::makeInstance('tx_templavoila_pi1');
		$pi1->cObj = $this->cObj;
		$lines[] = $pi1->renderElement($this->cObj->data, 'tt_content');
		return $lines;
	}

	/**
	 * Experimental rendering for plugins:
	 * Based on sg_plaintext from Stefan Geith
	 *
	 * @CType list
	 *
	 * @param array $lines
	 *
	 * @return array
	 */
	public function renderList($lines = array()) {
		$myName = FALSE;
		$listType = $this->cObj->data['list_type'];
		$template = $GLOBALS['TSFE']->tmpl;
		if (isset($template->setup['tt_content.']['list.']['20.'][$listType])) {
			$theValue = $template->setup['tt_content.']['list.']['20.'][$listType];
			$theConf = $template->setup['tt_content.']['list.']['20.'][$listType . '.'];
		} else {
			$tmp = explode('_pi', $listType);
			if (count($tmp) < 2) {
				$myName = 'tx_' . str_replace('_', '', $tmp[0]);
			} else {
				$myName = 'tx_' . str_replace('_', '', $tmp[0]) . '_pi' . $tmp[1];
			}
			$theValue = $template->setup['plugin.'][$myName];
			$theConf = $template->setup['plugin.'][$myName . '.'];
		}
		$content = $this->cObj->cObjGetSingle($theValue, $theConf);

		$myContent = $this->breakContent(strip_tags($content));
		if (strlen($myContent) > 1) {
			if (substr($myContent, 0, 1) == '|' && substr($myContent, -1) == '|') {
				$myContent = substr($myContent, 1, strlen($myContent) - 2);
			}
		}

		if (!is_array($theConf) || strlen($theConf['plainType']) < 2) {
			$defaultOutput = $this->conf['defaultOutput'];
			if ($defaultOutput && $myName) {
				$lines[] = str_replace('###CType###', $this->cObj->data['CType'] . ': "' . $this->cObj->data['list_type'] . '"; plugin: "' . $myName . '"; plainType: "' . (is_array($theConf) ? $theConf['plainType'] : '') . '"', $defaultOutput);
			}
		}

		$lines[] = $myContent;
		return $lines;
	}

	/**
	 * Function used by TypoScript "parseFunc" to process links in the bodytext.
	 * Extracts the link and shows it in plain text in a parathesis next to the link text. If link was relative the site URL was prepended.
	 *
	 * @param    string $content : Empty, ignore.
	 * @param    array  $conf    : TypoScript parameters
	 *
	 * @return    string        Processed output.
	 * @see parseBody()
	 */
	function atag_to_http($content, $conf) {
		$this->conf = $conf;
		$this->siteUrl = $conf['siteUrl'];
		$theLink = trim($this->cObj->parameters['href']);
		if (strtolower(substr($theLink, 0, 7)) == 'mailto:') {
			$theLink = substr($theLink, 7);
		}
		return $this->cObj->getCurrentVal() . ' ( ' . $theLink . ' )';
	}
}
