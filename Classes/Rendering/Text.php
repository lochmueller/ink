<?php
/**
 * Render text elements
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

/**
 * Render text elements
 */
class Text extends AbstractRendering
{

    /**
     * Render the current content
     *
     * @return array
     */
    public function renderInternal()
    {

        $lines[] = trim($this->breakContent(strip_tags($this->parseBody($this->contentObject->data['bodytext']))), CRLF . TAB);
        return $lines;

    }
}
