<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "participants"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Participants',
    'description' => 'This extension takes care of all aspects of participation.',
    'category' => 'plugin',
    'author' => 'C. Gogolin',
    'author_email' => 'service@cylancer.net',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '4.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'bootstrap_package' => '14.0.0-14.0.99',
            'usertools' => '3.0.0-3.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

/**
 * Change log: 
 * 
4.0.1   :: FIX : Fix colors of next event in the duty roster and update the bootstrap package to 14.0.0
4.0.0   :: UPD : Update to TYPO3 12.4.0
3.8.0   :: Add : The personal roster contains new links for downloading the iCal files of individual events.
3.7.11  :: Fix : Repair the commitment description text output.
3.7.10  :: Fix : Repair the (personal) duty roster iCal export file: simple blank lines are not allowed. iCal needs a special coding. 
3.7.9   :: Fix : Fix the default user setting for automatic apply commitments.
3.7.8   :: Fix : Fix the return value type in the frontend user service.
3.7.7   :: Fix : Adapts the personal duty roster folter column counts. 
3.7.6   :: Fix : Link from TaskForceOverview to the user list. 
3.7.5   :: Fix : Frontend user service: Better handling of simulated users.
3.7.4   :: Fix : Timeout management: You can only delete a period only without exceptions if the user is logged in. 
3.7.3   :: Fix : Duty roster excepts events with a null time. 
3.7.2   :: Fix : Event description is not displayed in the list view if the event is hidden (disabled).
3.7.1   :: Fix : Add missing lanugage setting in the event and event type record. 
3.7.0   :: Chg : Switch to the new reason for prevention service api.
3.6.6   :: Add : Personal duty roster -> disabled buttons are grey.
3.6.5   :: Fix : Optimize the commitment description text output. 
3.6.4   :: Fix : Correct the commitments calculation when the scheduling is changed.
3.6.3   :: Fix : The list of members also contains appointment commitments from persons who are not registered.
3.6.2   :: Update : Adds "only scheduled events" filter for personal duty roster. 
3.6.1   :: Update : Displays the undecideds in the members list. 
3.6.0   :: Update : Add a unknwon state in your personal duty roster.
3.5.1   :: Fix : Fix the event sorting.
3.5.0   :: Undo : Remove the trying of time as integer in the database. (don't use 3.4.* !)
3.4.1   :: Fix: Fix the timzone handling in the converter wizzard.
3.4.0   :: Add: Add an reasons for prevention service (interface for the lending extension connection).
3.3.2   :: Fix: Event begin time and begin date stored as integer.
3.3.1   :: Fix: Update the usertools dependency.
3.3.0   :: Fix: Repair the plugin configuration / registry.
3.2.10  :: Fix: Fix the exception if no events exists in the futrue.  
3.2.9   :: Fix: Make the plannig status visible.   
3.2.8   :: Fix: Move the horizonal line in the personal duty roster.  
3.2.7   :: Add: Reminder mail has a target link now. (and small fixes) 
3.2.6   :: Add: Personal commitment settings : Visiblity of the "Automatic apply commitments" 
3.2.5   :: Fix: Timeout management -> fix the jQuery file name
3.2.4   :: Fix: DutyRoster large -> fix the jQuery file name
3.2.3   :: Fix: DutyRoster small -> remove line breaks
3.2.2   :: Fix: PersonalDutyRoster collapsing members list
3.2.1   :: Fix: PersonalDutyRoster broken jquery link
3.2.0   :: Update: Bootstrap dependencies to version 13.0.* / Update jQuery 
3.1.8   :: Change: PersonalDutyRoster -> Events from the previous day are still displayed only for "can members see" user
3.1.7   :: Change: Icons
3.1.6   :: Add: PersonalDutyRoster -> Events from the previous day are still displayed
3.1.5   :: Fix: PersonalDutyRoster -> fix the progess bars.
3.1.4   :: Add: Add an backend switch for the auto scroll function.  
3.1.3   :: Add: Auto scroll to the current event in the big duty roster list.  
3.1.2   :: Fix: The personal reminder does not remind canceled event.  
3.1.1   :: Change: The personal reminder setting default is enabled. 
3.1.0   :: Add: The personal roster planner has a new user event reminder function.
3.0.6   :: Fix: PersonalDutyRoster -> change the date output format for old SAMSUNG cell phones
3.0.5   :: jquery 3.4.1 -> 3.6.1
3.0.x   :: Migragtion: Port to TYPO3 11.5
2.0.7   :: Functional update: if user commitment is not changable, if the event is started or in the past.  
2.0.6   :: Fix: The little duty roster displays only current and future events. 
2.0.5   :: Fix: Repair the personal duty roster iCal export file (wrong: "VTIMEZONE + SPACE" is not allowed.)
2.0.4   :: Fix: Add missing crdate in the iCal export file
2.0.3   :: Fix: Add missing translation of personal duty roster frontend plugin.
2.0.2   :: Fix: Remove dubug outputs. 
2.0.1   :: Fix: Duty roster planning task form: the front end user groups are save wrong.
2.0.0   :: Vollständiger Umbau der Eventstruktur. Diese Basiert nicht mehr auf der "news"-Erweiterung

**/