<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'be_users',
    [
        'tx_mydashboard_config' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:mydashboard/Resources/Private/Language/locallang_db.xml:be_users.tx_mydashboard_config',
            'config' => [ 'type' => 'none' ]
        ]
    ]
);