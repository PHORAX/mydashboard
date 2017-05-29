<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Load the Widgets from "myDashboard"
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/mydashboard/class.tx_mydashboard_widgetmgm.php']['addWidget']['serverinfo'] = \tx_mydashboard_serverinfo::class;
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/mydashboard/class.tx_mydashboard_widgetmgm.php']['addWidget']['userstats'] = \tx_mydashboard_userstats::class;
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/mydashboard/class.tx_mydashboard_widgetmgm.php']['addWidget']['rssfeed'] = \tx_mydashboard_rssfeed::class;
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/mydashboard/class.tx_mydashboard_widgetmgm.php']['addWidget']['sysnotepad'] = \tx_mydashboard_sysnotepad::class;

// Move to tt_news
$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/mydashboard/class.tx_mydashboard_widgetmgm.php']['addWidget']['latestnews'] = \tx_mydashboard_latestnews::class;

// Backend Home
//if (TYPO3_MODE=='BE')	{
//	$TYPO3_CONF_VARS['typo3/backend.php']['additionalBackendItems'][] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('mydashboard').'class.tx_mydashboard_additionalToolbarIcons.php';
//}
