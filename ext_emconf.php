<?php

/***************************************************************
 * Extension Manager/Repository config file for ext: "participants"
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'Participants',
    'description' => 'Diese Erweiterung kümmert sich rund um das Thema Teilnahme bei der Ortsfeuerwehr Letter.
Change log:
3.1.8 :: Change: PersonalDutyRoster -> Events from the previous day are still displayed only for "can members see" user
3.1.7 :: Change: Icons
3.1.6 :: Add: PersonalDutyRoster -> Events from the previous day are still displayed
3.1.5 :: Fix: PersonalDutyRoster -> fix the progess bars.
3.1.4 :: Add: Add an backend switch for the auto scroll function.  
3.1.3 :: Add: Auto scroll to the current event in the big duty roster list.  
3.1.2 :: Fix: The personal reminder does not remind canceled event.  
3.1.1 :: Change: The personal reminder setting default is enabled. 
3.1.0 :: Add: The personal roster planner has a new user event reminder function.
3.0.6 :: Fix: PersonalDutyRoster -> change the date output format for old SAMSUNG cell phones
3.0.5 :: jquery 3.4.1 -> 3.6.1
3.0.x :: Migragtion: Port to TYPO3 11.5
2.0.7 :: Functional update: if user commitment is not changable, if the event is started or in the past.  
2.0.6 :: Fix: The little duty roster displays only current and future events. 
2.0.5 :: Fix: Repair the personal duty roster iCal export file (wrong: "VTIMEZONE + SPACE" is not allowed.)
2.0.4 :: Fix: Add missing crdate in the iCal export file
2.0.3 :: Fix: Add missing translation of personal duty roster frontend plugin.
2.0.2 :: Fix: Remove dubug outputs. 
2.0.1 :: Fix: Duty roster planning task form: the front end user groups are save wrong.
2.0.0 :: Vollständiger Umbau der Eventstruktur. Diese Basiert nicht mehr auf der "news"-Erweiterung',
    'category' => 'plugin',
    'author' => 'C. Gogolin',
    'author_email' => 'service@cylancer.net',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '3.1.8',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.5.99',
            'bootstrap_package' => '11.0.2-12.9.99',
            'usertools' => '2.0.0-2.9.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
