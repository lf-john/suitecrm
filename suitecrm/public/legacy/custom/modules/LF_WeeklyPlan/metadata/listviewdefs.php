<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$listViewDefs['LF_WeeklyPlan'] = array(
    'NAME' => array(
        'width' => '20%',
        'label' => 'LBL_NAME',
        'link' => true,
        'default' => true,
    ),
    'WEEK_START_DATE' => array(
        'width' => '15%',
        'label' => 'LBL_WEEK_START_DATE',
        'default' => true,
    ),
    'STATUS' => array(
        'width' => '10%',
        'label' => 'LBL_STATUS',
        'default' => true,
    ),
    'ASSIGNED_USER_ID' => array(
        'width' => '15%',
        'label' => 'LBL_ASSIGNED_TO',
        'default' => true,
    ),
    'DATE_ENTERED' => array(
        'width' => '15%',
        'label' => 'LBL_DATE_ENTERED',
        'default' => true,
    ),
    'DATE_MODIFIED' => array(
        'width' => '15%',
        'label' => 'LBL_DATE_MODIFIED',
        'default' => true,
    ),
);
