<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.title',
        'label' => 'title',
        'label_userFunc' => \Cylancer\Participants\Domain\TCA\EventTca::class . '->computeTitle',
        'sortby' => 'date',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
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
    'interface' => [
        'showRecordFieldList' => 'l10n_parent, l10n_diffsource, hidden, canceled, event_type, date, full_day, time, ' . //
            'duration, usergroups, show_public_usergroups, public_usergroups, description, public, public_description, show_public_description '
    ],
    'types' => [
        '1' => [
            'showitem' => ' l10n_parent, l10n_diffsource, hidden, canceled, event_type, public, date, full_day, time, duration,'
                . '--div--;LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.tabGroupSettings, usergroups, show_public_usergroups, public_usergroups, '
                . '--div--;LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.tabDescription, description, show_public_description, public_description '
        ]
    ],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.title',
            'config' => [
                'type' => 'input'
            ]
        ],

        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ]
                ],
                'default' => 0
            ]
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255
            ]
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true
                    ]
                ]
            ]
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ]
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ]
        ],
        'crdate' => [
            'label' => 'crdate',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime'
            ]
        ],
        'tstamp' => [
            'label' => 'tstamp',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime'
            ]
        ],

        'event_type' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.eventType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_participants_domain_model_eventtype',
                'eval' => 'required'
            ]
        ],

        'public' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.public',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.public.option.public',
                        1
                    ],
                    [
                        'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.public.option.internal',
                        0
                    ],
                    [
                        'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event.public.option.inherited',
                        2
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
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'format' => 'date',
                'dbType' => 'date',
                'eval' => 'date',
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
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'dbType' => 'time',
                'eval' => 'time',
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