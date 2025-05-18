<?php
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

$GLOBALS['TCA']['tt_content']['columns']['CType']['config']['itemGroups']['participants']
    = 'LLL:EXT:participants/Resources/Private/Language/locallang_be.xlf:plugins.group.participants.name';