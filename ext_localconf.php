<?php
use Cylancer\Participants\Controller\CommitmentSettingsController;
use Cylancer\Participants\Controller\TimeOutManagementController;
use Cylancer\Participants\Controller\TaskForceOverviewController;
use Cylancer\Participants\Controller\DutyRosterController;
use Cylancer\Participants\Controller\PersonalDutyRosterController;

defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {


    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Cylancer.Participants',
        'CommitmentSettings',
        [
            CommitmentSettingsController::class => 'show, save'
        ],
        // non-cacheable actions
        [
            CommitmentSettingsController::class => 'show,save'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Participants',
        'TimeOutManagement',
        [
            TimeOutManagementController::class => 'list,delete, create'
        ],
        // non-cacheable actions
        [
            TimeOutManagementController::class => 'list,delete, create'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Participants',
        'TaskForceOverview',
        [
            TaskForceOverviewController::class => 'show'
        ],
        // non-cacheable actions
        [
            TaskForceOverviewController::class => 'show'
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Participants',
        'DutyRoster',
        [
            DutyRosterController::class => 'show, downloadIcs'
        ],
        // non-cacheable actions
        [
            DutyRosterController::class => 'show, downloadIcs'
        ]
    );
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Participants',
        'PersonalDutyRoster',
        [
            PersonalDutyRosterController::class => 'show, setPresent, setPersonalDutyRosterFilter, downloadAllVisibleCalendarEntries, downloadAllPromisedCalendarEntries, downloadAllPromisedVisibleCalendarEntries, downloadCalendarEntry, getMembers'
        ],
        // non-cacheable actions
        [
            PersonalDutyRosterController::class => 'show, setPresent, setPersonalDutyRosterFilter, downloadAllVisibleCalendarEntries, downloadAllPromisedCalendarEntries, downloadAllPromisedVisibleCalendarEntries, downloadCalendarEntry, getMembers'
        ]
    );

    // wizards
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('mod {
            wizards.newContentElement.wizardItems.plugins {
                elements {
                    commitmentsettings {
                        iconIdentifier = participants-plugin-commitmentsettings
                        title = LLL:EXT:participants/Resources/Private/Language/locallang_be_commitmentSettings.xlf:plugin.name
                        description = LLL:EXT:participants/Resources/Private/Language/locallang_be_commitmentSettings.xlf:plugin.description
                        tt_content_defValues {
                            CType = list
                            list_type = participants_commitmentsettings
                        }
                    }
                    timeoutmanagement {
                        iconIdentifier = participants-plugin-timeoutmanagement
                        title = LLL:EXT:participants/Resources/Private/Language/locallang_be_timeOutManagement.xlf:plugin.name
                        description = LLL:EXT:participants/Resources/Private/Language/locallang_be_timeOutManagement.xlf:plugin.description
                        tt_content_defValues {
                            CType = list
                            list_type = participants_timeoutmanagement
                        }
                    }
                    taskforceoverview {
                        iconIdentifier = participants-plugin-taskforceoverview
                        title = LLL:EXT:participants/Resources/Private/Language/locallang_be_taskForceOverview.xlf:plugin.name
                        description = LLL:EXT:participants/Resources/Private/Language/locallang_be_taskForceOverview.xlf:plugin.description
                        tt_content_defValues {
                            CType = list
                            list_type = participants_taskforceoverview
                        }
                    }
                    dutyroster {
                        iconIdentifier = participants-plugin-dutyroster
                        title = LLL:EXT:participants/Resources/Private/Language/locallang_be_dutyRoster.xlf:plugin.name
                        description = LLL:EXT:participants/Resources/Private/Language/locallang_be_dutyRoster.xlf:plugin.description
                        tt_content_defValues {
                            CType = list
                            list_type = participants_dutyroster
                        }
                    }
                    personaldutyroster {
                        iconIdentifier = participants-plugin-personaldutyroster
                        title = LLL:EXT:participants/Resources/Private/Language/locallang_be_personalDutyRoster.xlf:plugin.name
                        description = LLL:EXT:participants/Resources/Private/Language/locallang_be_personalDutyRoster.xlf:plugin.description
                        tt_content_defValues {
                            CType = list
                            list_type = participants_personaldutyroster
                        }
                    }
                }
                show = *
            }
       }');
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon('participants-plugin-timeoutmanagement', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:participants/Resources/Public/Icons/user_plugin_timeOutManagement.svg'
    ]);
    $iconRegistry->registerIcon('participants-plugin-taskforceoverview', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:participants/Resources/Public/Icons/user_plugin_taskForceOverview.svg'
    ]);
    $iconRegistry->registerIcon('participants-plugin-commitmentsettings', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:participants/Resources/Public/Icons/user_plugin_commitmentSettings.svg'
    ]);
    $iconRegistry->registerIcon('participants-plugin-dutyroster', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:participants/Resources/Public/Icons/user_plugin_dutyRoster.svg'
    ]);
    $iconRegistry->registerIcon('participants-plugin-personaldutyroster', \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class, [
        'source' => 'EXT:participants/Resources/Public/Icons/user_plugin_personalDutyRoster.svg'
    ]);

});

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\Cylancer\Participants\Task\PersonalDutyRosterPlanningTask::class] = [
    'extension' => 'participants',
    'title' => 'LLL:EXT:participants/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.title',
    'description' => 'LLL:EXT:usertools/Resources/Private/Language/locallang.xlf:task.personalDutyRosterPlanning.description',
    'additionalFields' => \Cylancer\Participants\Task\PersonalDutyRosterPlanningAdditionalFieldProvider::class
];