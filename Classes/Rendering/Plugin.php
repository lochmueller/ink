<?php
/**
 * Render a plugin
 *
 * @author  Tim Lochmüller
 */

namespace FRUIT\Ink\Rendering;

/**
 * Render a plugin
 */
class Plugin extends AbstractRendering
{

    /**
     * Render the given element
     *
     * @return array
     */
    public function renderInternal()
    {
        $lines = array();
        $lines[] = 'Test';
        $myName = false;
        $listType = $this->contentObject->data['list_type'];
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
        $content = $this->contentObject->cObjGetSingle($theValue, $theConf);

        $myContent = $this->breakContent(strip_tags($content));
        if (strlen($myContent) > 1) {
            if (substr($myContent, 0, 1) == '|' && substr($myContent, -1) == '|') {
                $myContent = substr($myContent, 1, strlen($myContent) - 2);
            }
        }

        if (!is_array($theConf) || strlen($theConf['plainType']) < 2) {
            $defaultOutput = $this->configuration['defaultOutput'];
            if ($defaultOutput && $myName) {
                $lines[] = str_replace('###CType###', $this->contentObject->data['CType'] . ': "' . $this->contentObject->data['list_type'] . '"; plugin: "' . $myName . '"; plainType: "' . (is_array($theConf) ? $theConf['plainType'] : '') . '"', $defaultOutput);
            }
        }

        $lines[] = $myContent;
        return $lines;
    }
}
