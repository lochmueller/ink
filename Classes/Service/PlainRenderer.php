<?php

/**
 * PlainText Service
 */

namespace FRUIT\Ink\Service;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 *  PlainText Service
 */
class PlainRenderer extends AbstractRenderer {

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
	 * Breaking lines into fixed length lines, using t3lib_div::breakLinesForEmail()
	 *
	 * @param        string  $str       : The string to break
	 * @param        string  $implChar  : Line break character
	 * @param        integer $charWidth : Length of lines, default is $this->charWidth
	 *
	 * @return        string                Processed string
	 */
	function breakLines($str, $implChar = LF, $charWidth = 76) {
		return MailUtility::breakLinesForEmail($str, $implChar, $charWidth);
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
		$aConf['parseFunc.']['tags.']['a.']['userFunc'] = 'tx_directmail_pi1->atag_to_http';
		$aConf['parseFunc.']['tags.']['a.']['siteUrl'] = 'http://www.google.de';
		$str = $this->cObj->stdWrap($str, $aConf);
		$str = str_replace('&nbsp;', ' ', htmlspecialchars_decode($str));

		if ($this->conf[$altConf . '.']['header']) {
			$str = $this->conf[$altConf . '.']['header'] . LF . $str;
		}

		return chr(10) . $str;
	}

	/**
	 * Breaks content lines into a bullet list
	 *
	 * @param    string $str : Content string to make into a bullet list
	 *
	 * @return    string        Processed value
	 */
	function breakBulletlist($str) {
		$type = $this->cObj->data['layout'];
		$type = MathUtility::forceIntegerInRange($type, 0, 3);

		$tConf = $this->conf['bulletlist.'][$type . '.'];

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

			$lines[] = $bullet . $this->breakLines($substrs, chr(10) . $secondRow, 76 - $bLen);

			$blanks = MathUtility::forceIntegerInRange($tConf['blanks'], 0, 1000);
			if ($blanks) {
				$lines[] = str_pad('', $blanks - 1, chr(10));
			}
		}
		return implode(chr(10), $lines);
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

		if ($this->isRegistered($CType)) {
			$func = $this->registeredCTypes[$CType];
			#$lines[] = $CType;
			$lines = $this->$func($lines);
			/**
			 * Check out direct_mail there are more different types
			 *
			 * case 'uploads':
			 * $lines[] = $this->getHeader();
			 * $lines[] = $this->renderUploads($this->cObj->data['media']);
			 * break;
			 * case 'shortcut':
			 * $lines[] = $this->getShortcut();
			 * break;
			 * case 'bullets':
			 * $lines[] = $this->getHeader();
			 * $lines[] = $this->breakBulletlist(strip_tags($this->parseBody($this->cObj->data['bodytext'])));
			 * break;
			 * case 'table':
			 * $lines[] = $this->getHeader();
			 * $lines[] = $this->breakTable(strip_tags($this->parseBody($this->cObj->data['bodytext'])));
			 * break;
			 */
		} else {
			$lines[] = 'CType: ' . $CType . ' have no rendering definitions';
		}
		$content = implode(LF, $lines);
		return trim($content, CRLF . TAB);
	}

	/**
	 * Creates a menu/sitemap
	 *
	 * @CType menu
	 *
	 * @return        string                $str: Content
	 */
	function getMenuSitemap() {
		$str = $this->cObj->cObjGetSingle($this->conf['menu'], $this->conf['menu.']);
		$str = $this->breakBulletlist(trim(strip_tags(preg_replace('/<br\s*\/?>/i', chr(10), $this->parseBody($str)))));
		return $str;
	}

	/**
	 * Render a table
	 *
	 * @CType table
	 *
	 * @param array $lines
	 *
	 * @return array
	 */
	public function renderTable($lines = array()) {
		$controller = GeneralUtility::makeInstance('TYPO3\\CMS\\CssStyledContent\\Controller\\CssStyledContentController');
		$controller->cObj = $this->cObj;
		$htmlTable = $controller->render_table();
		$tableData = $this->parseHtmlTable($htmlTable);
		$writer = new PlainTableWriter();
		$lines[] = $writer->getTable($tableData);
		return $lines;
	}

	protected function parseHtmlTable($html) {
		$dom = new \DOMDocument();

		//load the html
		$html = $dom->loadHTML(utf8_decode($html));

		//discard white space
		$dom->preserveWhiteSpace = FALSE;

		//the table by its tag name
		$tables = $dom->getElementsByTagName('table');

		//get all rows from the table
		$rows = $tables->item(0)
			->getElementsByTagName('tr');
		// get each column by tag name
		$cols = $rows->item(0)
			->getElementsByTagName('th');
		$row_headers = NULL;
		foreach ($cols as $node) {
			//print $node->nodeValue."\n";
			$row_headers[] = $node->nodeValue;
		}

		$table = array();
		//get all rows from the table
		$rows = $tables->item(0)
			->getElementsByTagName('tr');
		foreach ($rows as $row) {
			// get each column by tag name
			$cols = $row->getElementsByTagName('td');
			$row = array();
			$i = 0;
			foreach ($cols as $node) {
				# code...
				//print $node->nodeValue."\n";
				if ($row_headers == NULL) {
					$row[] = $node->nodeValue;
				} else {
					$row[$row_headers[$i]] = $node->nodeValue;
				}
				$i++;
			}
			$table[] = $row;
		}

		return $table;
	}

	/**
	 * Render a header
	 *
	 * @CType header
	 *
	 * @param array $lines
	 *
	 * @return array
	 * @todo  move to TS
	 */
	public function renderHeader($lines = array()) {
		$headerWrap = MailUtility::breakLinesForEmail(trim($this->cObj->data['header']));
		$subHeaderWrap = MailUtility::breakLinesForEmail(trim($this->cObj->data['subheader']));

		// align
		$header = array_merge(GeneralUtility::trimExplode(LF, $headerWrap, TRUE), GeneralUtility::trimExplode(LF, $subHeaderWrap, TRUE));
		if ($this->cObj->data['header_position'] == 'right') {
			foreach ($header as $key => $l) {
				$l = trim($l);
				$header[$key] = str_pad(' ', (76 - strlen($l)), ' ', STR_PAD_LEFT) . $l;
			}
		} elseif ($this->cObj->data['header_position'] == 'center') {
			foreach ($header as $key => $l) {
				$l = trim($l);
				$header[$key] = str_pad(' ', floor((76 - strlen($l)) / 2), ' ', STR_PAD_LEFT) . $l;
			}
		}
		$header = implode(LF, $header);
		$lines[] = $header;
		return $lines;
	}

	/**
	 * Render text
	 *
	 * @CType text
	 *
	 * @param array $lines
	 *
	 * @return array
	 */
	public function renderText($lines = array()) {
		$lines[] = trim($this->breakContent(strip_tags($this->parseBody($this->cObj->data['bodytext']))), CRLF . TAB);
		return $lines;
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
	 * Render a HTML Content Element
	 *
	 * @CType html
	 *
	 * @param array $lines
	 *
	 * @return array
	 */
	public function renderHtml($lines = array()) {
		$lines[] = $this->breakContent(strip_tags($this->cObj->data['bodytext']));
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
}