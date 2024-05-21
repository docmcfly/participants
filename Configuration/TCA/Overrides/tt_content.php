<?php
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') || die('Access denied.');

(static function (): void{


    ExtensionUtility::registerPlugin(
        'Participants',
        'TimeOutManagement',
        'TimeOutManagement'
    );
    ExtensionUtility::registerPlugin(
        'Participants',
        'TaskForceOverview',
        'TaskForceOverview'
    );
    ExtensionUtility::registerPlugin(
        'Participants',
        'CommitmentSettings',
        'CommitmentSettings'
    );
    ExtensionUtility::registerPlugin(
        'Participants',
        'DutyRoster',
        'DutyRoster'
    );
    ExtensionUtility::registerPlugin(
        'Participants',
        'PersonalDutyRoster',
        'PersonalDutyRoster'
    );


    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_timeoutmanagement'] = 'pi_flexform';
    ExtensionManagementUtility::addPiFlexFormValue(
        // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
        'participants_timeoutmanagement',
        // Flexform configuration schema file
        'FILE:EXT:participants/Configuration/FlexForms/TimeOutManagement.xml'
    );
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_taskforceoverview'] = 'pi_flexform';
    ExtensionManagementUtility::addPiFlexFormValue(
        // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
        'participants_taskforceoverview',
        // Flexform configuration schema file
        'FILE:EXT:participants/Configuration/FlexForms/TaskForceOverview.xml'
    );
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_dutyroster'] = 'pi_flexform';
    ExtensionManagementUtility::addPiFlexFormValue(
        // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
        'participants_dutyroster',
        // Flexform configuration schema file
        'FILE:EXT:participants/Configuration/FlexForms/DutyRoster.xml'
    );
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_personaldutyroster'] = 'pi_flexform';
    ExtensionManagementUtility::addPiFlexFormValue(
        // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
        'participants_personaldutyroster',
        // Flexform configuration schema file
        'FILE:EXT:participants/Configuration/FlexForms/PersonalDutyRoster.xml'
    );
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_commitmentsettings'] = 'pi_flexform';
    ExtensionManagementUtility::addPiFlexFormValue(
        // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
        'participants_commitmentsettings',
        // Flexform configuration schema file
        'FILE:EXT:participants/Configuration/FlexForms/CommitmentSettings.xml'
    );

})();