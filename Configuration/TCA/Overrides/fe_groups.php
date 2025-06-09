<?php
defined('TYPO3') || die('Access denied.');

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$translationPath = 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_feGroups';

ExtensionManagementUtility::addTCAcolumns(
    'fe_groups',
    [
        'accronym' => [
            'exclude' => 0,
            'label' => "$translationPath.acronym",
            'config' => [
                'type' => 'input',
                'max' => 30,
            ],
        ],
    ]
);

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_groups',
    "--div--;$translationPath.tab_settings, accronym",
);
