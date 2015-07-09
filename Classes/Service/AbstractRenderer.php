<?php

/**
 * Abstract renderer Service
 */

namespace FRUIT\Ink\Service;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\MethodReflection;

/**
 *  Abstract renderer Service
 */
abstract class AbstractRenderer {

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
	 * Render the different elements and collect the information
	 *
	 * @param string $content
	 * @param array  $conf
	 *
	 * @return mixed
	 */
	abstract public function render($content, $conf);
}