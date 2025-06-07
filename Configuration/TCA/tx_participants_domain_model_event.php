<?php

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */

return [
    'ctrl' => [
        'title' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.title',
        'label' => 'title',
        'label_userFunc' => \Cylancer\Participants\Domain\TCA\EventTca::class . '->computeTitle',
        'default_sortby' => 'date ASC, time ASC',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
            'type' => 'type',
            'date' => 'date',
            'full_day' => 'full_day',
            'time' => 'time',
            'duration' => 'duration',
            'usergroups' => 'usergroups',
            'public' => 'public',
            'show_public_usergroups' => 'show_public_usergroups',
            'public_usergroups' => 'public_usergroups',
            'description' => 'description',
            'public_description' => 'public_description',
            'show_public_description' => 'show_public_description',
            'canceled' => 'canceled'
        ],
        'searchFields' => '',
        'iconfile' => 'EXT:participants/Resources/Public/Icons/tx_participants_domain_model_event_type.gif'
    ],
    'types' => [
        '1' => [
            'showitem' => ' canceled, event_type, public, date, full_day, time, duration,'
                . '--div--;LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.tabGroupSettings, usergroups, show_public_usergroups, public_usergroups, '
                . '--div--;LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.tabDescription, description, show_public_description, public_description, '
                . '--div--;LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.tabMiscellaneous, '
        ]
    ],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.title',
            'config' => [
                'type' => 'input'
            ]
        ],

        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
            ]
        ],

        'event_type' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.eventType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_participants_domain_model_eventtype',
                'foreign_table_where' => 'ORDER BY title',
                'required' => true
            ]
        ],

        'public' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.public',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.public.option.public',
                        'value' => 1
                    ],
                    [
                        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.public.option.internal',
                        'value' => 0
                    ],
                    [
                        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.public.option.inherited',
                        'value' => 2
                    ]
                ],
                'default' => 2
            ]
        ],
        'canceled' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.canceled',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'dbType' => 'canceled'
            ]
        ],

        'date' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.date',
            'config' => [
                'type' => 'datetime',
                'format' => 'date',
                'dbType' => 'date',
                'default' => time()
            ]
        ],

        'full_day' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.fullDay',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'default' => true
            ]
        ],

        'time' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.time',
            'config' => [
                'type' => 'datetime',
                'dbType' => 'time',
                'format' => 'time',
                'default' => '19:00:00',
                //  68400 // <=> 19:00h
                //   'mode' => 'useOrOverridePlaceholder'
            ]
        ],

        'duration' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.duration',
            'config' => [
                'type' => 'input',
                'eval' => 'num',
                'default' => 3
            ]
        ],

        'description' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.description',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'eval' => 'trim',
                'max' => 65535
            ]
        ],
        'show_public_description' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.showPublicDescription',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle'
            ]
        ],
        'public_description' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.publicDescription',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'eval' => 'trim',
                'max' => 65535
            ]
        ],

        'usergroups' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.usergroups',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_groups',
                'MM' => 'tx_participants_event_usergroup_mm'
            ]
        ],

        'show_public_usergroups' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.showPublicUsergroups',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'dbType' => 'show_public_usergroups'
            ]
        ],

        'public_usergroups' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.publicUsergroups',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_groups',
                'MM' => 'tx_participants_event_publicusergroup_mm'
            ]
        ]
    ]
];