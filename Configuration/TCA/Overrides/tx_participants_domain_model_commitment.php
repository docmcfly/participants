<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
   'participants',
   'tx_participants_domain_model_commitment'
);



$GLOBALS['TCA']['tx_participants_domain_model_commitment']['columns']['present']['config']['readOnly'] = 1;
$GLOBALS['TCA']['tx_participants_domain_model_commitment']['columns']['user']['config']['readOnly'] = 1;
$GLOBALS['TCA']['tx_participants_domain_model_commitment']['columns']['event']['config']['readOnly'] = 1;
