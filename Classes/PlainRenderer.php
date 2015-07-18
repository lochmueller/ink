<?php

/**
 * PlainText Service
 */

namespace FRUIT\Ink;

use FRUIT\Ink\Rendering\RenderingInterface;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
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
	 * Returns a typolink URL based on input.
	 *
	 * @param    string $ll : Parameter to typolink
	 *
	 * @return    string        The URL returned from $this->cObj->getTypoLink_URL(); - possibly it prefixed with the URL of the site if not present already
	 */
	function getLink($ll) {
		return $this->cObj->getTypoLink_URL($ll);
	}

	/**
	 * Render block of images - which means creating lines with links to the images.
	 *
	 * @param     array $images_arr : the image array
	 * @param    string $links      : Link value from the "image_link" field in tt_content records
	 * @param    string $caption    : Caption text
	 *
	 * @return    string        Content
	 * @see getImages()
	 */
	function renderImagesHelper($images_arr, $links, $caption) {
		$linksArr = explode(',', $links);
		$lines = array();
		$imageExists = FALSE;

		foreach ($images_arr as $k => $file) {
			if (strlen(trim($file)) > 0) {
				$lines[] = $file;
				if ($links && count($linksArr) > 1) {
					if (isset($linksArr[$k])) {
						$ll = $linksArr[$k];
					} else {
						$ll = $linksArr[0];
					}

					$theLink = $this->getLink($ll);
					if ($theLink) {
						$lines[] = $this->conf['images.']['linkPrefix'] . $theLink;
					}
				}
				$imageExists = TRUE;
			}
		}
		if ($this->conf['images.']['header'] && $imageExists) {
			array_unshift($lines, $this->conf['images.']['header']);
		}
		if ($links && count($linksArr) == 1) {
			$theLink = $this->getLink($links);
			if ($theLink) {
				$lines[] = $this->conf['images.']['linkPrefix'] . $theLink;
			}
		}
		if ($caption) {
			$lines[] = '';
			$cHeader = trim($this->conf['images.']['captionHeader']);
			if ($cHeader) {
				$lines[] = $cHeader;
			}
			$lines[] = $this->breakContent($caption);
		}

		return chr(10) . implode(chr(10), $lines);
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
			'html'   => 'FRUIT\\Ink\\Rendering\\Html',
			'header' => 'FRUIT\\Ink\\Rendering\\Header',
			'table'  => 'FRUIT\\Ink\\Rendering\\Table',
			#'menu'   => 'FRUIT\\Ink\\Rendering\\Menu',
			'text'   => 'FRUIT\\Ink\\Rendering\\Text',
		);

		if (isset($renderer[$CType])) {
			$className = $renderer[$CType];
			/** @var RenderingInterface $renderObject */
			$renderObject = GeneralUtility::makeInstance($className);
			$lines = $renderObject->render($this->cObj);
		} else {

			if ($this->isRegistered($CType)) {
				$func = $this->registeredCTypes[$CType];
				#$lines[] = $CType;
				$lines = $this->$func($lines);
			} else {
				$lines[] = 'CType: ' . $CType . ' have no rendering definitions';
			}
		}
		$content = implode(LF, $lines);
		return trim($content, CRLF . TAB);
	}

	/**
	 * Render text with pic
	 *
	 * @CType textpic
	 *
	 * @param array $lines
	 *
	 * @return array
	 */
	public function renderTextpic($lines = array()) {
		if (!($this->cObj->data['imageorient'] & 24)) {
			$lines = $this->renderImage($lines);
			$lines[] = '';
		}
		$lines[] = $this->breakContent(strip_tags($this->parseBody($this->cObj->data['bodytext'])));
		if ($this->cObj->data['imageorient'] & 24) {
			$lines[] = '';
			$lines = $this->renderImage($lines);
		}

		return $lines;
	}

	/**
	 * Render image
	 *
	 * @CType image
	 *
	 * @param array $lines
	 *
	 * @return array
	 */
	function renderImage($lines = array()) {
		$lines[] = 'Todo: Images';

		$objectManager = new ObjectManager();
		/** @var FileRepository $fileRepository */
		$fileRepository = $objectManager->get('TYPO3\\CMS\\Core\\Resource\\FileRepository');
		$files = $fileRepository->findByRelation('tt_content', 'image', $this->cObj->data['uid']);

		$images_arr = array();
		foreach ($files as $file) {
			/** @var $file File */
			$images_arr[] = $file->getPublicUrl();
		}

		$lines[] = $this->renderImagesHelper($images_arr, !$this->cObj->data['image_zoom'] ? $this->cObj->data['image_link'] : '', $this->cObj->data['imagecaption']);

		return $lines;
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
	 * Parsing the bodytext field content, removing typical entities and <br /> tags.
	 *
	 * @param    string $str     : Field content from "bodytext" or other text field
	 * @param    string $altConf : Altername conf name (especially when bodyext field in other table then tt_content)
	 *
	 * @return    string        Processed content
	 */
	function parseBody($str, $altConf = 'bodytext') {
		if ($this->conf[$altConf . '.']['doubleLF']) {
			$str = preg_replace("/\n/", "\n\n", $str);
		}
		// Regular parsing:
		$str = preg_replace('/<br\s*\/?>/i', chr(10), $str);
		$str = $this->cObj->stdWrap($str, $this->conf[$altConf . '.']['stdWrap.']);

		// Then all a-tags:
		$aConf = array();
		$aConf['parseFunc.']['tags.']['a'] = 'USER';
		// check direct mail usage @todo
		$aConf['parseFunc.']['tags.']['a.']['userFunc'] = 'FRUIT\\Ink\\PlainRenderer->atag_to_http';
		$aConf['parseFunc.']['tags.']['a.']['siteUrl'] = 'http://www.google.de';
		$str = $this->cObj->stdWrap($str, $aConf);
		$str = str_replace('&nbsp;', ' ', htmlspecialchars_decode($str));

		if ($this->conf[$altConf . '.']['header']) {
			$str = $this->conf[$altConf . '.']['header'] . LF . $str;
		}

		return chr(10) . $str;
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
