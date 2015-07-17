<?php

/**
 * Interface for processing
 */

namespace FRUIT\Ink\Postprocessing;

/**
 * Interface for processing
 */
interface PostprocessingInterface {

	/**
	 * Run the process
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function process($content);

}