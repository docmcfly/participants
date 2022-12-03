<?php
defined('TYPO3_MODE') || die();

if (!isset($GLOBALS['TCA']['fe_users']['ctrl']['type'])) {
    // no type field defined, so we define it here. This will only happen the first time the extension is installed!!
    $GLOBALS['TCA']['fe_users']['ctrl']['type'] = 'tx_extbase_type';
    $tempColumnstx_participants_fe_users = [];
    $tempColumnstx_participants_fe_users[$GLOBALS['TCA']['fe_users']['ctrl']['type']] = [
        'exclude' => true,
        'label'   => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants.tx_extbase_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['',''],
                ['User','Tx_Participants_User']
            ],
            'default' => 'Tx_Participants_User',
            'size' => 1,
            'maxitems' => 1,
        ]
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $tempColumnstx_participants_fe_users);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    $GLOBALS['TCA']['fe_users']['ctrl']['type'],
    '',
    'after:' . $GLOBALS['TCA']['fe_users']['ctrl']['label']
);

$tmp_participants_columns = [
    
   
    'hidden_personal_duty_roster_groups' => [
        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_user.hidden_personal_duty_roster_groups',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectMultipleSideBySide',
            'foreign_table' => 'fe_groups',
            'MM' => 'tx_participants_user_hiddenpersonaldutyrostergroup_mm',
          //  'readOnly' => true,
        ],
        
    ],
    
    'apply_planning_data' => [
        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_user.apply_planning_data',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'readOnly' => true,
            'default' => 1,
        ]
    ],
    'info_mail_when_personal_duty_roster_changed' => [
        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_user.info_mail_when_personal_duty_roster_changed',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'readOnly' => true,
            'default' => 1,
        ]
    ],
    'personal_duty_event_reminder' => [
        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_user.personal_duty_event_reminder',
        'config' => [
            'type' => 'check',
            'renderType' => 'checkboxToggle',
            'items' => [
                [
                    0 => '',
                    1 => '',
                ]
            ],
            'readOnly' => true,
            'default' => 1,
        ]
    ],
    
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users',$tmp_participants_columns);

/* inherit and extend the show items from the parent class */

if (isset($GLOBALS['TCA']['fe_users']['types']['0']['showitem'])) {
    $GLOBALS['TCA']['fe_users']['types']['Tx_Participants_User']['showitem'] = $GLOBALS['TCA']['fe_users']['types']['0']['showitem'];
} elseif(is_array($GLOBALS['TCA']['fe_users']['types'])) {
    // use first entry in types array
    $fe_users_type_definition = reset($GLOBALS['TCA']['fe_users']['types']);
    $GLOBALS['TCA']['fe_users']['types']['Tx_Participants_User']['showitem'] = $fe_users_type_definition['showitem'];
} else {
    $GLOBALS['TCA']['fe_users']['types']['Tx_Participants_User']['showitem'] = '';
}
// $GLOBALS['TCA']['fe_users']['types']['Tx_Participants_User']['showitem'] .= ',--div--;LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_user,';
// $GLOBALS['TCA']['fe_users']['types']['Tx_Participants_User']['showitem'] .= 'hidden_target_groups, apply_planning_data';

$GLOBALS['TCA']['fe_users']['columns'][$GLOBALS['TCA']['fe_users']['ctrl']['type']]['config']['items'][] = ['LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_user.tx_extbase_type','Tx_Participants_User'];

$tmp_types = array_keys($GLOBALS['TCA']['fe_users']['types']);
foreach($tmp_types as $type){
    $GLOBALS['TCA']['fe_users']['types'][$type]['showitem'] .= ', --div--;LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_user.tab_settings, apply_planning_data, info_mail_when_personal_duty_roster_changed, personal_duty_event_reminder, hidden_personal_duty_roster_groups ';
}




