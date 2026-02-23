<?php 
 //WARNING: The contents of this file are auto-generated


if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Weekly Snapshot Job
 *
 * This job runs weekly to create snapshots of all open opportunities for all active reps.
 * It ensures that each rep has an LF_WeeklyReport record for the current week and
 * creates LF_ReportSnapshot records for each open opportunity assigned to them.
 *
 * @return bool True on success
 */
function LF_WeeklySnapshotJob()
{
    if (!class_exists('LF_RepTargets')):
        require_once 'custom/modules/LF_RepTargets/LF_RepTargets.php';
    endif;
    if (!class_exists('LF_WeeklyReport')):
        require_once 'custom/modules/LF_WeeklyReport/LF_WeeklyReport.php';
    endif;
    if (!class_exists('LF_ReportSnapshot')):
        require_once 'custom/modules/LF_ReportSnapshot/LF_ReportSnapshot.php';
    endif;
    if (!class_exists('WeekHelper')):
        require_once 'custom/include/LF_PlanningReporting/WeekHelper.php';
    endif;

    // Get the current week start date
    // Note: Use default parameters to be compatible with both real and mock WeekHelper
    $weekStart = WeekHelper::getCurrentWeekStart();

    // Get all active reps
    $activeReps = LF_RepTargets::getActiveReps();

    foreach ($activeReps as $rep):
        $repUserId = $rep['assigned_user_id'];

        // Find or create the weekly report record for this rep and week
        $reportBean = LF_WeeklyReport::getOrCreateForWeek($repUserId, $weekStart);

        if ($reportBean && !empty($reportBean->id)):
            // Create snapshots for all open opportunities for this rep
            LF_ReportSnapshot::createSnapshotsForWeek($repUserId, $weekStart, $reportBean->id);
        endif;
    endforeach;

    return true;
}

$job_strings[] = 'LF_WeeklySnapshotJob';
?>