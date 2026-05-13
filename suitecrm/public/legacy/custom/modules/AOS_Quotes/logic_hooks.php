<?php
$hook_array['before_save'][] = Array(
    1,
    'Populate Account from Opportunity',
    'custom/modules/AOS_Quotes/logic_hooks/PopulateAccountFromOpportunity.php',
    'PopulateAccountFromOpportunity',
    'populateAccount'
);
