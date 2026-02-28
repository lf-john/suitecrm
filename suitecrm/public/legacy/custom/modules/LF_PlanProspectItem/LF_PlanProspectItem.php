<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once 'custom/modules/LF_PRConfig/LF_PRConfig.php';

/**
 * LF_PlanProspectItem Bean Class
 *
 * Represents a prospecting item within a weekly plan. Each item tracks
 * a planned prospecting activity (cold call, referral, event, etc.) and
 * can be converted into an Opportunity once the prospect materialises.
 *
 * Conversion workflow:
 *   1. Rep creates a prospect item in their weekly plan
 *   2. Rep works the prospect during the week
 *   3. When the prospect becomes viable, rep calls convertToOpportunity()
 *   4. An Account is found or created, an Opportunity is created,
 *      and this item's status is set to 'converted'
 */
#[\AllowDynamicProperties]
class LF_PlanProspectItem extends SugarBean
{
    public $table_name = 'lf_plan_prospect_items';
    public $object_name = 'LF_PlanProspectItem';
    public $module_name = 'LF_PlanProspectItem';
    public $module_dir = 'LF_PlanProspectItem';

    // Enable ACL support for this module
    public $acl_display_only = false;

    public function bean_implements($interface)
    {
        return ($interface === 'ACL');
    }

    /**
     * Convert this prospect item to an Opportunity.
     *
     * Finds or creates an Account, creates a new Opportunity linked to it,
     * and marks this prospect item as converted. The Opportunity is assigned
     * to the same rep as the parent weekly plan.
     *
     * @param string $accountName Name for the Account record (must not be empty)
     * @param string $oppName     Name for the Opportunity record (must not be empty)
     * @param float  $amount      Opportunity amount (must be numeric and > 0)
     * @return SugarBean The created Opportunity bean
     * @throws InvalidArgumentException If any parameter fails validation
     */
    public function convertToOpportunity($accountName, $oppName, $amount)
    {
        $this->validateConversionInputs($accountName, $oppName, $amount);

        $defaultStage = LF_PRConfig::getConfig('prospecting', 'default_conversion_stage');
        $repId = $this->getRepIdFromPlan();

        $account = self::findOrCreateAccount($accountName, $repId);

        // Create Opportunity
        $opportunity = BeanFactory::newBean('Opportunities');
        $opportunity->name = $oppName;
        $opportunity->amount = $amount;
        $opportunity->account_id = $account->id;
        $opportunity->sales_stage = $defaultStage;
        $opportunity->date_closed = date('Y-m-d', strtotime('+90 days'));
        if ($repId !== null) {
            $opportunity->assigned_user_id = $repId;
        }
        $opportunity->save();

        // Mark this prospect item as converted
        $this->converted_opportunity_id = $opportunity->id;
        $this->status = 'converted';
        $this->save();

        return $opportunity;
    }

    /**
     * Validate inputs for convertToOpportunity().
     *
     * @param string $accountName Account name
     * @param string $oppName     Opportunity name
     * @param mixed  $amount      Opportunity amount
     * @throws InvalidArgumentException If validation fails
     */
    private function validateConversionInputs($accountName, $oppName, $amount)
    {
        if (empty(trim($accountName))) {
            throw new InvalidArgumentException('Account name must not be empty');
        }
        if (empty(trim($oppName))) {
            throw new InvalidArgumentException('Opportunity name must not be empty');
        }
        if (!is_numeric($amount) || $amount <= 0) {
            throw new InvalidArgumentException('Amount must be a positive number');
        }
        if (empty($this->lf_weekly_plan_id)) {
            throw new InvalidArgumentException('Prospect item must belong to a weekly plan before conversion');
        }
    }

    /**
     * Look up the assigned rep's user ID from the parent weekly plan.
     *
     * @return string|null The assigned_user_id, or null if not found
     */
    private function getRepIdFromPlan()
    {
        $db = DBManagerFactory::getInstance();

        $repId = $db->getOne(sprintf(
            "SELECT assigned_user_id
             FROM lf_weekly_plan
             WHERE id = %s AND deleted = 0",
            $db->quoted($this->lf_weekly_plan_id)
        ));

        return ($repId !== false) ? $repId : null;
    }

    /**
     * Find an existing Account by name or create a new one.
     *
     * @param string      $accountName The account name to search for
     * @param string|null $repId       The user ID to assign to a new account
     * @return SugarBean The found or newly created Account bean
     */
    private static function findOrCreateAccount($accountName, $repId)
    {
        $account = BeanFactory::newBean('Accounts');
        $account->retrieve_by_string_fields(['name' => $accountName, 'deleted' => '0']);

        if (!empty($account->id)) {
            return $account;
        }

        $account = BeanFactory::newBean('Accounts');
        $account->name = $accountName;
        if ($repId !== null) {
            $account->assigned_user_id = $repId;
        }
        $account->save();

        return $account;
    }
}
