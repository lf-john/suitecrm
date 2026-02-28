<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// LF_PlanReport Module Group Registration
// Registers all 7 custom modules for the Plan and Report functionality

$beanList['LF_WeeklyPlan'] = 'LF_WeeklyPlan';
$beanList['LF_PlanOpItem'] = 'LF_PlanOpItem';
$beanList['LF_PlanProspectItem'] = 'LF_PlanProspectItem';
$beanList['LF_WeeklyReport'] = 'LF_WeeklyReport';
$beanList['LF_ReportSnapshot'] = 'LF_ReportSnapshot';
$beanList['LF_PRConfig'] = 'LF_PRConfig';
$beanList['LF_RepTargets'] = 'LF_RepTargets';

$beanFiles['LF_WeeklyPlan'] = 'custom/modules/LF_WeeklyPlan/LF_WeeklyPlan.php';
$beanFiles['LF_PlanOpItem'] = 'custom/modules/LF_PlanOpItem/LF_PlanOpItem.php';
$beanFiles['LF_PlanProspectItem'] = 'custom/modules/LF_PlanProspectItem/LF_PlanProspectItem.php';
$beanFiles['LF_WeeklyReport'] = 'custom/modules/LF_WeeklyReport/LF_WeeklyReport.php';
$beanFiles['LF_ReportSnapshot'] = 'custom/modules/LF_ReportSnapshot/LF_ReportSnapshot.php';
$beanFiles['LF_PRConfig'] = 'custom/modules/LF_PRConfig/LF_PRConfig.php';
$beanFiles['LF_RepTargets'] = 'custom/modules/LF_RepTargets/LF_RepTargets.php';

$moduleList['LF_WeeklyPlan'] = 'LF_WeeklyPlan';
$moduleList['LF_PlanOpItem'] = 'LF_PlanOpItem';
$moduleList['LF_PlanProspectItem'] = 'LF_PlanProspectItem';
$moduleList['LF_WeeklyReport'] = 'LF_WeeklyReport';
$moduleList['LF_ReportSnapshot'] = 'LF_ReportSnapshot';
$moduleList['LF_PRConfig'] = 'LF_PRConfig';
$moduleList['LF_RepTargets'] = 'LF_RepTargets';
