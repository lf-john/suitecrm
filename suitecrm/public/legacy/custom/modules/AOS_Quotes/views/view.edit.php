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
        
        // Add JavaScript to populate Account field when Opportunity is selected
        echo <<<'JAVASCRIPT'
<script>
(function() {
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupAccountPopulation);
    } else {
        setupAccountPopulation();
    }
    
    function setupAccountPopulation() {
        var oppField = document.getElementById('opportunity_name');
        if (oppField) {
            oppField.addEventListener('change', function() {
                var oppId = document.getElementById('opportunity_id');
                if (oppId && oppId.value) {
                    // Fetch opportunity details and populate account
                    fetch('index.php?entryPoint=getOpportunityAccount&opp_id=' + oppId.value)
                        .then(function(response) { return response.json(); })
                        .then(function(data) {
                            if (data.account_id && data.account_name) {
                                var accountId = document.getElementById('billing_account_id');
                                var accountName = document.getElementById('billing_account_name');
                                if (accountId) accountId.value = data.account_id;
                                if (accountName) accountName.value = data.account_name;
                            }
                        });
                }
            });
        }
    }
})();
</script>
JAVASCRIPT;
    }
}
