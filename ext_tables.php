<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Cylancer.Participants',
            'TimeOutManagement',
            'TimeOutManagement'
            );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Cylancer.Participants',
            'TaskForceOverview',
            'TaskForceOverview'
            );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Cylancer.Participants',
            'CommitmentSettings',
            'CommitmentSettings'
            );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Cylancer.Participants',
            'DutyRoster',
            'DutyRoster'
            );
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'Cylancer.Participants',
            'PersonalDutyRoster',
            'PersonalDutyRoster'
            );
        
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('participants', 'Configuration/TypoScript', 'Participants');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_participants_domain_model_commitment', 'EXT:participants/Resources/Private/Language/locallang_csh_tx_participants_domain_model_commitment.xlf');
       //  \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_participants_domain_model_commitment');

    }
);
