<?php
/**
 * Custom Sales Stages for Logical Front
 * Follows Customer Centered Selling (CCS) methodology
 * 
 * Stage 2 = Analysis (1%) - Not yet a real opportunity, placeholder
 * Stage 3 = Confirmation (10%) - Should progress to Stage 5 in same meeting
 * Stage 5 = Specifications (30%)
 * Stage 6 = Solution (60%)
 * Stage 7 = Closing (90%)
 * 
 * Stages 1 and 4 are intentionally skipped per CCS methodology.
 */

$app_list_strings["sales_stage_dom"] = array(
    "2-Analysis (1%)" => "2-Analysis (1%)",
    "3-Confirmation (10%)" => "3-Confirmation (10%)",
    "5-Specifications (30%)" => "5-Specifications (30%)",
    "6-Solution (60%)" => "6-Solution (60%)",
    "7-Closing (90%)" => "7-Closing (90%)",
    "closed_won" => "Closed Won",
    "closed_lost" => "Closed Lost",
);

$app_list_strings["sales_probability_dom"] = array(
    "2-Analysis (1%)" => "1",
    "3-Confirmation (10%)" => "10",
    "5-Specifications (30%)" => "30",
    "6-Solution (60%)" => "60",
    "7-Closing (90%)" => "90",
    "closed_won" => "100",
    "closed_lost" => "0",
);

// Set default stage for new opportunities
$app_list_strings["sales_stage_default_key"] = "2-Analysis (1%)";
