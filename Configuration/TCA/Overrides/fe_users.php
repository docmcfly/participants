<?php

defined('TYPO3') || die('Access denied.');

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$translationPath = 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_feUsers';

ExtensionManagementUtility::addTCAcolumns('fe_users', [

    'hidden_personal_duty_roster_groups' => [
        'label' => "$translationPath.hidden_personal_duty_roster_groups",
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'fe_groups',
            'MM' => 'tx_participants_user_hiddenpersonaldutyrostergroup_mm',
            //  'readOnly' => true,
        ],

    ],

    'apply_planning_data' => [
        'label' => "$translationPath.apply_planning_data",
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'readOnly' => true,
            'default' => 0,
        ]
    ],
    'info_mail_when_personal_duty_roster_changed' => [
        'label' => "$translationPath.info_mail_when_personal_duty_roster_changed",
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'readOnly' => true,
            'default' => 1,
        ]
    ],
    'personal_duty_event_reminder' => [
        'label' => "$translationPath.personal_duty_event_reminder",
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'readOnly' => true,
            'default' => 1,
        ]
    ],
    'show_only_scheduled_events' => [
        'label' => "$translationPath.show_only_scheduled_events",
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'readOnly' => true,
            'default' => 1,
        ]
    ],
]);

ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    "--div--;$translationPath.tab_settings,"
    . ' apply_planning_data, info_mail_when_personal_duty_roster_changed,'
    . ' personal_duty_event_reminder, hidden_personal_duty_roster_groups,'
    . ' show_only_scheduled_events',
);
