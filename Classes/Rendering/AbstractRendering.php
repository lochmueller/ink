<?php
/**
 * Abstract rendering helper
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

use FRUIT\Ink\Configuration;
use TYPO3\CMS\Core\Utility\MailUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Abstract rendering helper
 */
abstract class AbstractRendering implements RenderingInterface
{

    /**
     * Content object
     *
     * @var ContentObjectRenderer
     */
    protected $contentObject;

    /**
     * Configuration
     *
     * @var array
     */
    protected $configuration;

    /**
     * Set the content object and configuration
     * Call the renderInternal after preparation
     *
     * @param ContentObjectRenderer $contentObject
     * @param array                 $configuration
     *
     * @return array
     */
    public function render($contentObject, $configuration)
    {
        $this->contentObject = $contentObject;
        $this->configuration = $configuration;
        return $this->renderInternal();
    }

    /**
     * Parsing the bodytext field content, removing typical entities and <br /> tags.
     *
     * @param    string $str     : Field content from "bodytext" or other text field
     * @param    string $altConf : Altername conf name (especially when bodyext field in other table then tt_content)
     *
     * @return    string        Processed content
     */
    function parseBody($str, $altConf = 'bodytext')
    {
        if ($this->configuration[$altConf . '.']['doubleLF']) {
            $str = preg_replace("/\n/", "\n\n", $str);
        }
        // Regular parsing:
        $str = preg_replace('/<br\s*\/?>/i', chr(10), $str);
        $str = $this->contentObject->stdWrap($str, $this->configuration[$altConf . '.']['stdWrap.']);

        // Then all a-tags:
        $aConf = array();
        $aConf['parseFunc.']['tags.']['a'] = 'USER';
        // check direct mail usage @todo
        $aConf['parseFunc.']['tags.']['a.']['userFunc'] = 'FRUIT\\Ink\\PlainRenderer->atag_to_http';
        $aConf['parseFunc.']['tags.']['a.']['siteUrl'] = 'http://www.google.de';
        $str = $this->contentObject->stdWrap($str, $aConf);
        $str = str_replace('&nbsp;', ' ', htmlspecialchars_decode($str));

        if ($this->configuration[$altConf . '.']['header']) {
            $str = $this->configuration[$altConf . '.']['header'] . LF . $str;
        }

        return chr(10) . $str;
    }

    /**
     * Function used to wrap the bodytext field content (or image caption) into lines of a max length of
     *
     * @param        string $str : The content to break
     *
     * @return        string                Processed value.
     * @see main_plaintext(), breakLines()
     */
    function breakContent($str)
    {
        $cParts = explode(chr(10), $str);
        $lines = array();
        foreach ($cParts as $substrs) {
            $lines[] = $this->breakLines($substrs, LF);
        }
        return implode(chr(10), $lines);
    }

    /**
     * Returns a typolink URL based on input.
     *
     * @param    string $ll : Parameter to typolink
     *
     * @return    string        The URL returned from $this->cObj->getTypoLink_URL(); - possibly it prefixed with the URL of the site if not present already
     */
    function getLink($ll)
    {
        return $this->contentObject->getTypoLink_URL($ll);
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
    function breakLines($str, $implChar = LF, $charWidth = false)
    {
        $charWidth = $charWidth === false ? Configuration::getPlainTextWith() : (int)$charWidth;
        return MailUtility::breakLinesForEmail($str, $implChar, $charWidth);
    }
}
