<?php
/**
 * Remove HTML comments
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Postprocessing;

/**
 * Remove HTML comments
 */
class RemoveHtmlComments implements PostprocessingInterface
{

    /**
     * Run the replacement of the comments
     *
     * @param string $content
     *
     * @return string
     */
    public function process($content)
    {
        return preg_replace('/<!--(.*?)-->/s', '', $content);
    }
}
