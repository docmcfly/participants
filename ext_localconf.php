<?php
use Cylancer\Participants\Controller\CommitmentSettingsController;
use Cylancer\Participants\Controller\TimeOutManagementController;
use Cylancer\Participants\Controller\TaskForceOverviewController;
use Cylancer\Participants\Controller\DutyRosterController;
use Cylancer\Participants\Controller\PersonalDutyRosterController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') || die('Access denied.');

/**
 * This file is part of the "Participants" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2025 C. Gogolin <service@cylancer.net>
 *
 */


ExtensionUtility::configurePlugin(
    'Participants',
    'CommitmentSettings',
    [
        CommitmentSettingsController::class => 'show, save'
    ],
    // non-cacheable actions
    [
        CommitmentSettingsController::class => 'show,save'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'Participants',
    'TimeOutManagement',
    [
        TimeOutManagementController::class => 'list,delete, create'
    ],
    // non-cacheable actions
    [
        TimeOutManagementController::class => 'list,delete, create'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'Participants',
    'TaskForceOverview',
    [
        TaskForceOverviewController::class => 'show'
    ],
    // non-cacheable actions
    [
        TaskForceOverviewController::class => 'show'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'Participants',
    'DutyRoster',
    [
        DutyRosterController::class => 'show, downloadIcs'
    ],
    // non-cacheable actions
    [
        DutyRosterController::class => 'show, downloadIcs'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

ExtensionUtility::configurePlugin(
    'Participants',
    'PersonalDutyRoster',
    [
        PersonalDutyRosterController::class => 'show, setPresent, setPersonalDutyRosterFilter, downloadAllVisibleCalendarEntries, downloadAllPromisedCalendarEntries, downloadAllPromisedVisibleCalendarEntries, downloadCalendarEntry, getMembers'
    ],
    // non-cacheable actions
    [
        PersonalDutyRosterController::class => 'show, setPresent, setPersonalDutyRosterFilter, downloadAllVisibleCalendarEntries, downloadAllPromisedCalendarEntries, downloadAllPromisedVisibleCalendarEntries, downloadCalendarEntry, getMembers'
    ],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Cylancer\Participants\Task\PersonalDutyRosterPlanningTask::class] = [
    'extension' => 'participants',
    'title' => 'LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.title',
    'description' => 'LLL:EXT:usertools/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.description',
    'additionalFields' => \Cylancer\Participants\Task\PersonalDutyRosterPlanningAdditionalFieldProvider::class
];

// E-Mail-Templates
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths']['participants-UpdatePersonalDutyRosterPlanningMail'] = 'EXT:participants/Resources/Private/Templates/UpdatePersonalDutyRosterPlanningMail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths']['participants-UpdatePersonalDutyRosterPlanningMail'] = 'EXT:participants/Resources/Private/Layouts/UpdatePersonalDutyRosterPlanningMail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths']['participants-UpdatePersonalDutyRosterPlanningMail'] = 'EXT:participants/Resources/Private/Partials/UpdatePersonalDutyRosterPlanningMail/';

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths']['participants-TommorrowsEventsReminderMail'] = 'EXT:participants/Resources/Private/Templates/TommorrowsEventsReminderMail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['layoutRootPaths']['participants-TommorrowsEventsReminderMail'] = 'EXT:participants/Resources/Private/Layouts/TommorrowsEventsReminderMail/';
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['partialRootPaths']['participants-TommorrowsEventsReminderMail'] = 'EXT:participants/Resources/Private/Partials/TommorrowsEventsReminderMail/';

