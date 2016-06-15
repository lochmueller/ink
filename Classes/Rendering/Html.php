<?php
/**
 * Render the HTML element
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

/**
 * Render the HTML element
 */
class Html extends AbstractRendering
{

    /**
     * Get the liens for the current HTML element
     *
     * @return array
     */
    public function renderInternal()
    {
        return array($this->breakContent(strip_tags($this->contentObject->data['bodytext'])));
    }
}
