<?php
/**
 * Module name mappings for custom LF modules.
 * Maps legacy module names to frontend (Angular) and core identifiers.
 * Eliminates "ModuleNameMapper | not mapped" warnings in suitecrm.log.
 */

$module_name_map['LF_WeeklyPlan'] = [
    'frontend' => 'lf-weekly-plan',
    'core' => 'LFWeeklyPlan'
];

$module_name_map['LF_WeeklyReport'] = [
    'frontend' => 'lf-weekly-report',
    'core' => 'LFWeeklyReport'
];

$module_name_map['LF_PlanOpItem'] = [
    'frontend' => 'lf-plan-op-item',
    'core' => 'LFPlanOpItem'
];

$module_name_map['LF_PlanProspectItem'] = [
    'frontend' => 'lf-plan-prospect-item',
    'core' => 'LFPlanProspectItem'
];

$module_name_map['LF_PRConfig'] = [
    'frontend' => 'lf-pr-config',
    'core' => 'LFPRConfig'
];

$module_name_map['LF_RepTargets'] = [
    'frontend' => 'lf-rep-targets',
    'core' => 'LFRepTargets'
];

$module_name_map['LF_ReportSnapshot'] = [
    'frontend' => 'lf-report-snapshot',
    'core' => 'LFReportSnapshot'
];
