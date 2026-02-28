<?php
if (!defined("sugarEntry") || !sugarEntry) {
    die("Not A Valid Entry Point");
}

// Include the stock listviewdefs first to preserve column definitions
require __DIR__ . "/../../../../modules/Contacts/metadata/listviewdefs.php";

// Add sidebarWidgets for Insights button
$viewdefs["Contacts"]["ListView"]["sidebarWidgets"] = [
    "accounts-new-by-month" => [
        "type" => "chart",
        "labelKey" => "LBL_QUICK_CHARTS",
        "options" => [
            "toggle" => true,
            "headerTitle" => false,
            "charts" => [
                [
                    "chartKey" => "accounts-new-by-month",
                    "chartType" => "line-chart",
                    "statisticsType" => "accounts-new-by-month",
                    "labelKey" => "ACCOUNT_TYPES_PER_MONTH",
                    "chartOptions" => []
                ]
            ]
        ],
        "acls" => [
            "Contacts" => ["view", "list"]
        ]
    ],
];
