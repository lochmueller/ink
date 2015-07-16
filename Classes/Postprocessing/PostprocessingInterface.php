<?php

/**
 *
 */

namespace FRUIT\Ink\Postprocessing;

/**
 * Interface PostprocessingInterface
 */
interface PostprocessingInterface {

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	public function process($content);

}