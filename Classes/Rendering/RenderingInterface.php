<?php
/**
 * Rendering interface
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Rendering interface
 */
interface RenderingInterface
{

    /**
     * Set the content object and configuration
     * Call the renderInternal after preparation
     *
     * @param ContentObjectRenderer $contentObject
     * @param array                 $configuration
     *
     * @return array
     */
    public function render($contentObject, $configuration);

    /**
     * Render the given element
     *
     * @return array
     */
    public function renderInternal();
}
