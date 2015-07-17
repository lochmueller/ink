<?php
/**
 * $EM_CONF
 *
 * @category Extension
 * @author   Tim Lochmüller
 */


$EM_CONF['ink'] = array(
	'title'            => 'Ink',
	'description'      => 'Build stable and flexible newsltter templates.',
	'category'         => 'fe',
	'version'          => '0.1.0',
	'state'            => 'beta',
	'clearcacheonload' => 1,
	'author'           => 'Tim Lochmüller',
	'author_email'     => 'tim@fruit-lab.de',
	'constraints'      => array(
		'depends' => array(
			'typo3'              => '6.2.0-7.99.99',
			'css_styled_content' => '6.2.0-7.99.99',
			'autoloader'         => '1.6.0-0.0.0',
		),
	),
);
