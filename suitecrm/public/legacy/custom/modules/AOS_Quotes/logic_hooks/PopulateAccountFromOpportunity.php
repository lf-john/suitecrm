<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class PopulateAccountFromOpportunity
{
    public function populateAccount($bean, $event, $arguments)
    {
        // Only run on new records or when opportunity changes
        if (empty($bean->billing_account_id) && !empty($bean->opportunity_id)) {
            $opp = BeanFactory::getBean('Opportunities', $bean->opportunity_id);
            
            if ($opp && !empty($opp->account_id)) {
                $bean->billing_account_id = $opp->account_id;
                $bean->billing_account_name = $opp->account_name;
            }
        }
    }
}
