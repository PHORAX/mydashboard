<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

if (TYPO3_MODE == 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'user',
        'txmydashboardM1',
        '',
        '',
        [
            'routeTarget' => \PHORAX\Mydashboard\Controller\MydashboardController::class . '::mainAction',
            'access' => 'user,group',
            'name' => 'user_txmydashboardM1',
            'icon' => 'EXT:mydashboard/Resources/Public/Icons/moduleicon.gif',
            'labels' => 'LLL:EXT:mydashboard/Resources/Private/Language/locallang_mod.xml'
        ]
    );
}