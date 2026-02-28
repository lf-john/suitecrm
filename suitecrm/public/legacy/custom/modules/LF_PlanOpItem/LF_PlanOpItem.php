<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * LF_PlanOpItem Bean Class
 *
 * Represents a planned opportunity action item within a weekly plan.
 * Each item links to an existing Opportunity and tracks the rep's
 * planned activity (item_type), projected stage, and planned day.
 */
#[\AllowDynamicProperties]
class LF_PlanOpItem extends SugarBean
{
    public $table_name = 'lf_plan_op_items';
    public $object_name = 'LF_PlanOpItem';
    public $module_name = 'LF_PlanOpItem';
    public $module_dir = 'LF_PlanOpItem';

    // Enable ACL support for this module
    public $acl_display_only = false;

    public function bean_implements($interface)
    {
        return ($interface === 'ACL');
    }

    /**
     * Retrieve live opportunity data from the related Opportunity record.
     *
     * @return array Opportunity data: ['name' => string, 'account_name' => string, 'amount' => string, 'sales_stage' => string, 'probability' => string]
     */
    public function getOpportunityData()
    {
        $opportunity = BeanFactory::getBean('Opportunities', $this->opportunity_id);

        if (empty($opportunity) || empty($opportunity->id)) {
            return [
                'name' => '',
                'account_name' => '',
                'amount' => '',
                'sales_stage' => '',
                'probability' => '',
            ];
        }

        return [
            'name' => $opportunity->name,
            'account_name' => $opportunity->account_name,
            'amount' => $opportunity->amount,
            'sales_stage' => $opportunity->sales_stage,
            'probability' => $opportunity->probability,
        ];
    }
}
