<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('modules/AOS_Quotes/views/view.edit.php');

class CustomAOS_QuotesViewEdit extends AOS_QuotesViewEdit
{
    public function display()
    {
        // When creating a Quote from an Opportunity, auto-populate the Account field
        if (!empty($_REQUEST['return_relationship']) && $_REQUEST['return_relationship'] == 'opportunities' 
            && !empty($_REQUEST['return_id']) && empty($_REQUEST['record'])) {
            
            $opp_id = $_REQUEST['return_id'];
            $opp = BeanFactory::getBean('Opportunities', $opp_id);
            
            if ($opp && !empty($opp->account_id)) {
                // Set the billing account from the opportunity's account
                $_REQUEST['billing_account_id'] = $opp->account_id;
                $_REQUEST['billing_account_name'] = $opp->account_name;
                
                // Also set it in the bean so it's available in the view
                $this->bean->billing_account_id = $opp->account_id;
                $this->bean->billing_account_name = $opp->account_name;
            }
        }
        
        parent::display();
    }
}
