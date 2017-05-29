<?php

########################################################################
# Extension Manager/Repository config file for ext "mydashboard".
#
# Auto generated 02-10-2011 14:50
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'myDashboard',
	'description' => 'Get a cool Dashboard for TYPO3 with basic elements and a extension API to add own widgets! Help me with the Dashboard and develop widgets for your extensions. TYPO3 4.2 is recommended to use the dashboard in combination with the "userdefined Startpage".',
	'category' => 'module',
	'shy' => 0,
	'version' => '0.2.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => 'mod1',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'be_users',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Tim Lochmueller',
	'author_email' => 'webmaster@fruit-lab.de',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' =>
		array (
			'depends' =>
				array (
					'typo3' => '4.5.0-6.2.99',
				),
			'conflicts' =>
				array (
				),
			'suggests' =>
				array (
				),
		),
);

?>