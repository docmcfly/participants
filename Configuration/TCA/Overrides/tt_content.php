<?php


$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_timeoutmanagement'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
    'participants_timeoutmanagement',
    // Flexform configuration schema file
    'FILE:EXT:participants/Configuration/FlexForms/TimeOutManagement.xml'
    );
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_taskforceoverview'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
    'participants_taskforceoverview',
    // Flexform configuration schema file
    'FILE:EXT:participants/Configuration/FlexForms/TaskForceOverview.xml'
    );
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_dutyroster'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
    'participants_dutyroster',
    // Flexform configuration schema file
    'FILE:EXT:participants/Configuration/FlexForms/DutyRoster.xml'
    );
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['participants_personaldutyroster'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    // plugin signature: <extension key without underscores> '_' <plugin name in lowercase>
    'participants_personaldutyroster',
    // Flexform configuration schema file
    'FILE:EXT:participants/Configuration/FlexForms/PersonalDutyRoster.xml'
    );

?>
