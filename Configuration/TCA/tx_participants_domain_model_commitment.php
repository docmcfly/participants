<?php
use Cylancer\Participants\Domain\PresentState;

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
        'title' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment',
        'label' => 'present',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'versioningWS' => true,
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'enablecolumns' => [
            'present' => 'present',
        ],
        'searchFields' => '',
        'iconfile' => 'EXT:participants/Resources/Public/Icons/tx_participants_domain_model_commitment.gif'
    ],
    'interface' => [
        'showRecordFieldList' => ' l10n_parent, l10n_diffsource, present, present_default, event, user',
    ],
    'types' => [
        '1' => [
            'showitem' => ' l10n_parent, l10n_diffsource, present, present_default, event, user'
        ],
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
                        -1,
                        'flags-multiple'
                    ]
                ],
                'default' => 0,
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 0,
                'items' => [
                    [ '', 0 ],
                ],
                'foreign_table' => 'tx_participants_domain_model_commitment',
                'foreign_table_where' => 'AND {#tx_participants_domain_model_commitment}.{#pid}=###CURRENT_PID### AND {#tx_participants_domain_model_commitment}.{#sys_language_uid} IN (-1,0)',
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],

        'present' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present',
            
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'readOnly' => false,
                'items' => [
                    [ 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present.option.unknown', PresentState::UNKNOWN ],
                    [ 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present.option.notPresent', PresentState::NOT_PRESENT ],
                    [ 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present.option.present', PresentState::PRESENT ],
                ],
                'default' => PresentState::UNKNOWN,
            ]
        ],
        'present_default' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present_default',
            'config' => [
                'type' => 'check',
                'readOnly' => false,
                'renderType' => 'checkboxToggle',
                'default' => 0,
            ]
        ],
        'event' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.event',
            'config' => [
                'readOnly' => true,
                'type' => 'select',
                'dbType' => 'event',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_participants_domain_model_event',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],
        'user' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.user',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'fe_users',
                'minitems' => 0,
                'maxitems' => 1,
            ],
        ],

    ],
];