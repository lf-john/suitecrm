<?php
define('sugarEntry', true);
chdir('/var/www/html/public/legacy');
require_once 'include/entryPoint.php';
require_once 'custom/include/LF_PlanningReporting/OpportunityQuery.php';

try {
    $result = OpportunityQuery::getStaleDeals(14);
    echo "Stale deals count: " . count($result) . "\n";
    foreach (array_slice($result, 0, 5) as $deal) {
        echo "- {$deal['name']} (stage: {$deal['sales_stage']}, last: {$deal['last_activity_date']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
