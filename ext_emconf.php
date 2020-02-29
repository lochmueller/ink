<?php
/**
 * $EM_CONF
 *
 * @category Extension
 * @author   Tim Lochmüller
 */


$EM_CONF[$_EXTKEY] = array(
    'title'            => 'Ink',
    'description'      => 'Build stable and flexible newsletter templates. Incl. also the plain text rendering for plain text e-Mails. Please contribute at https://github.com/lochmueller/ink',
    'category'         => 'fe',
    'version'          => '0.2.0',
    'state'            => 'beta',
    'clearcacheonload' => 1,
    'author'           => 'Tim Lochmüller',
    'author_email'     => 'tim@fruit-lab.de',
    'constraints'      => array(
        'depends' => array(
            'typo3'              => '7.6.0-8.5.99',
            'css_styled_content' => '7.6.0-8.5.99',
            'autoloader'         => '3.0.0-0.0.0',
        ),
    ),
);
