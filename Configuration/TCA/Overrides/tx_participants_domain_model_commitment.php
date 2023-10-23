<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
   'participants',
   'tx_participants_domain_model_commitment'
);
