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
    'types' => [
        '1' => [
            'showitem' => ' l10n_parent, l10n_diffsource, present, present_default, event, user'
        ],
    ],
    'columns' => [
        'present' => [
            'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present',

            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'readOnly' => false,
                'items' => [
                    [
                        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present.option.unknown',
                        'value' => PresentState::UNKNOWN
                    ],
                    [
                        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present.option.notPresent',
                        'value' => PresentState::NOT_PRESENT
                    ],
                    [
                        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_commitment.present.option.present',
                        'value' => PresentState::PRESENT
                    ],
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