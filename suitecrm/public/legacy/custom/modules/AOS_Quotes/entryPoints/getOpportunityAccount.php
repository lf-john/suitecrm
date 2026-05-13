<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

header('Content-Type: application/json');

$opp_id = $_GET['opp_id'] ?? '';
if (empty($opp_id)) {
    echo json_encode(array('error' => 'No opportunity ID provided'));
    exit;
}

$opp = BeanFactory::getBean('Opportunities', $opp_id);
if ($opp && !empty($opp->account_id)) {
    echo json_encode(array(
        'account_id' => $opp->account_id,
        'account_name' => $opp->account_name
    ));
} else {
    echo json_encode(array('error' => 'Opportunity not found or no account'));
}
exit;
