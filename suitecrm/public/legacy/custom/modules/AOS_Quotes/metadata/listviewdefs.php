<?php
if (!defined("sugarEntry") || !sugarEntry) {
    die("Not A Valid Entry Point");
}

// Include the stock listviewdefs first to preserve column definitions
require __DIR__ . "/../../../../modules/AOS_Quotes/metadata/listviewdefs.php";

// Add sidebarWidgets for Insights button
$viewdefs["AOS_Quotes"]["ListView"]["sidebarWidgets"] = [
    "opportunities-by-sales-stage-price" => [
        "type" => "chart",
        "labelKey" => "LBL_QUICK_CHARTS",
        "options" => [
            "toggle" => true,
            "headerTitle" => false,
            "charts" => [
                [
                    "chartKey" => "opportunities-by-sales-stage-price",
                    "chartType" => "vertical-bar",
                    "statisticsType" => "opportunities-by-sales-stage-price",
                    "labelKey" => "PIPELINE_BY_SALES_STAGE",
                    "chartOptions" => []
                ]
            ]
        ],
        "acls" => [
            "AOS_Quotes" => ["view", "list"]
        ]
    ],
];
