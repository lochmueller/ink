<?php

/**
 * PlainText Service
 */

namespace FRUIT\Ink;

use FRUIT\Ink\Rendering\RenderingInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

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
			'html'            => 'FRUIT\\Ink\\Rendering\\Html',
			'header'          => 'FRUIT\\Ink\\Rendering\\Header',
			'table'           => 'FRUIT\\Ink\\Rendering\\Table',
			#'menu'            => 'FRUIT\\Ink\\Rendering\\Menu',
			'text'            => 'FRUIT\\Ink\\Rendering\\Text',
			#'image'           => 'FRUIT\\Ink\\Rendering\\Image',
			'textpic'         => 'FRUIT\\Ink\\Rendering\\TextPicture',
			'templavoila_pi1' => 'FRUIT\\Ink\\Rendering\\Templavoila',
			'list'            => 'FRUIT\\Ink\\Rendering\\Plugin',
		);

		$objectManager = new ObjectManager();
		/** @var Dispatcher $signalSlot */
		$signalSlot = $objectManager->get('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
		$returnData = $signalSlot->dispatch(__CLASS__, 'renderer', array('renderer' => $renderer));
		$renderer = $returnData['renderer'];

		if (isset($renderer[$CType])) {
			$className = $renderer[$CType];
			/** @var RenderingInterface $renderObject */
			$renderObject = GeneralUtility::makeInstance($className);
			$lines = $renderObject->render($this->cObj, $this->conf);
		} else {
			$lines[] = 'CType: ' . $CType . ' have no rendering definitions';
		}
		$content = implode(LF, $lines);
		return trim($content, CRLF . TAB);
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
