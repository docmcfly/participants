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
        'title' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_time_out.title',
        'label' => 'user',
        'label_alt' => 'from, until, reason',
        'label_alt_force' => true, 
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
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
    'types' => [
        '1' => [
            'showitem' => 'user, from, until, reason'
        ]
    ],
    'columns' => [
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
                'type' => 'datetime',
                'readOnly' => true,
                'dbType' => 'date',
                'format' => 'date',
                'default' => time(),
            ]
        ],
        'until' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_time_out.until',
            'config' => [
                'readOnly' => true,
                'type' => 'datetime',
                'format' => 'date',
                'dbType' => 'date',
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
