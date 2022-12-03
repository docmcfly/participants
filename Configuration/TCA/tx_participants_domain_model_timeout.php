<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_time_out.title',
        'label' => 'user',
        'label_alt' => 'from, until, reason',
        'label_alt_force' => true, 
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'user' => 'user',
            'from' => 'from',
            'until' => 'until',
            'reason' => 'reason'
        ],
        'searchFields' => '',
        'iconfile' => 'EXT:participants/Resources/Public/Icons/tx_participants_domain_model_timeout.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'user, from, until, reason'
    ],
    'types' => [
        '1' => [
            'showitem' => 'user, from, until, reason'
        ]
    ],
    'columns' => [
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
                        - 1,
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
        'user' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_time_out.user',
            'config' => [
                'readOnly' => true,
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'from' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_time_out.from',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
                'dbType' => 'date',
                'renderType' => 'inputDateTime',
                'eval' => 'date,int',
                'default' => 0,
            ]
        ],
        'until' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_time_out.until',
            'config' => [
                'readOnly' => true,
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'dbType' => 'date',
                'eval' => 'date,int',
                'default' => 0,
            ]
        ],
        'reason' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_time_out.reason',
            'config' => [
                'readOnly' => true, 
                'type' => 'input',
                'dbType' => 'reason',
                'max' => 255
            ]
        ],
    ]
];
