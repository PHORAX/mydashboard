<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE == 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('user', 'txmydashboardM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/');
}

$tempColumns = [
    'tx_mydashboard_config' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:mydashboard/locallang_db.xml:be_users.tx_mydashboard_config',
        'config' => ['type' => 'none']
    ],
    'tx_mydashboard_selfadmin' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:mydashboard/locallang_db.xml:be_users.tx_mydashboard_selfadmin',
        'config' => ['type' => 'check']
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'tx_mydashboard_order;;;;1-1-1, tx_mydashboard_selfadmin');
