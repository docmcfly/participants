<?php
defined('TYPO3_MODE') || die();

if (!isset($GLOBALS['TCA']['fe_groups']['ctrl']['type'])) {
    // no type field defined, so we define it here. This will only happen the first time the extension is installed!!
    $GLOBALS['TCA']['fe_groups']['ctrl']['type'] = 'tx_extbase_type';
    $tempColumnstx_participants_fe_groups = [];
    $tempColumnstx_participants_fe_groups[$GLOBALS['TCA']['fe_groups']['ctrl']['type']] = [
        'exclude' => true,
        'label'   => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants.tx_extbase_type',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                ['',''],
                ['Usergroups','Tx_Participants_Usergroups']
            ],
            'default' => 'Tx_Participants_Usergroups',
            'size' => 1,
            'maxitems' => 1,
        ]
    ];
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups', $tempColumnstx_participants_fe_groups);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_groups',
    $GLOBALS['TCA']['fe_groups']['ctrl']['type'],
    '',
    'after:' . $GLOBALS['TCA']['fe_groups']['ctrl']['label']
);

$tmp_participants_columns = [
    'accronym' => [
        'label' => 'LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_feGroups.acronym',
        'config' => [
            'type' => 'input',
            'max' => 30,
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_groups',$tmp_participants_columns);

/* inherit and extend the show items from the parent class */

if (isset($GLOBALS['TCA']['fe_groups']['types']['0']['showitem'])) {
    $GLOBALS['TCA']['fe_groups']['types']['Tx_Participants_Usergroups']['showitem'] = $GLOBALS['TCA']['fe_groups']['types']['0']['showitem'];
} elseif(is_array($GLOBALS['TCA']['fe_groups']['types'])) {
    // use first entry in types array
    $fe_groups_type_definition = reset($GLOBALS['TCA']['fe_groups']['types']);
    $GLOBALS['TCA']['fe_groups']['types']['Tx_Participants_Usergroups']['showitem'] = $fe_groups_type_definition['showitem'];
} else {
    $GLOBALS['TCA']['fe_groups']['types']['Tx_Participants_Usergroups']['showitem'] = '';
}
// $GLOBALS['TCA']['fe_groups']['types']['Tx_Participants_User']['showitem'] .= ',--div--;LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:tx_participants_domain_model_user,';
// $GLOBALS['TCA']['fe_groups']['types']['Tx_Participants_User']['showitem'] .= 'hidden_target_groups, apply_planning_data';

$GLOBALS['TCA']['fe_groups']['columns'][$GLOBALS['TCA']['fe_groups']['ctrl']['type']]['config']['items'][] = ['LLL:EXT:participants/Resources/Private/Language/locallang_db.xlf:fe_groups.tx_extbase_type.Tx_Participants_Usergroups','Tx_Participants_Usergroups'];
$GLOBALS['TCA']['fe_groups']['types']['Tx_Participants_Usergroups']['showitem'] .= ', accronym';
$GLOBALS['TCA']['fe_groups']['types']['Tx_Extbase_Domain_Model_FrontendUserGroup']['showitem'] .= ', accronym';
$GLOBALS['TCA']['fe_groups']['types']['0']['showitem'] .= ', accronym';


