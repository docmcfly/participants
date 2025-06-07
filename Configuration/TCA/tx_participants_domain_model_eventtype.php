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
        'title' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event_type.title',
        'label' => 'title',
        'label_userFunc' => \Cylancer\Participants\Domain\TCA\EventTypeTca::class . '->computeTitleDescription',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'title' => 'title',
            'public' => 'public',
            'description' => 'description',
            'usergroups' => 'usergroups',
        ],
        'searchFields' => '',
        'iconfile' => 'EXT:participants/Resources/Public/Icons/tx_participants_domain_model_event_type.gif'
    ],
    'types' => [
        '1' => [
            'showitem' => ' title, public, description, usergroups'
        ]
    ],
    'columns' => [
        'title' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event_type.title',
            'config' => [
                'type' => 'input',
                'dbType' => 'title',
                'required' => true,
                'max' => 255
            ]
        ],
        'description' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event_type.description',
            'config' => [
                'type' => 'input',
                'dbType' => 'description',
                'max' => 255
            ],
        ],
        'public' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event_type.public',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'dbType' => 'public',
                'default' => 1,
            ],
        ],
        'usergroups' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_event_type.usergroups',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'fe_groups',
                'MM' => 'tx_participants_eventtype_usergroup_mm',
            ],
        ],
    ]
];
