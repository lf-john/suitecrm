<?php
/**
 * Custom Line Items Detail View
 * Columns: Product/Service, Description, Qty, Unit Price, Profit, Line Total
 * Profit = (Unit Price - Cost Price) × Qty
 *
 * Based on: modules/AOS_Products_Quotes/Line_Items.php
 * Customized for Logical Front theme
 */

if (!function_exists('display_lines')) {
function display_lines($focus, $field, $value, $view)
{
    global $sugar_config, $locale, $app_list_strings, $mod_strings;

    $enable_groups = (int)$sugar_config['aos']['lineItems']['enableGroups'];
    $total_tax = (int)$sugar_config['aos']['lineItems']['totalTax'];

    $html = '';

    if ($view == 'EditView') {
        // EditView uses the original line_items.js — no changes needed here
        $html .= '<script src="modules/AOS_Products_Quotes/line_items.js"></script>';
        if (file_exists('custom/modules/AOS_Products_Quotes/line_items.js')) {
            $html .= '<script src="custom/modules/AOS_Products_Quotes/line_items.js"></script>';
        }
        // Inject language strings inline so they are always available, regardless of how the
        // page is embedded (SuiteCRM 8 Angular shell can break relative jsLanguage URLs).
        $quotes_lang = return_module_language($GLOBALS['current_language'], 'AOS_Quotes');
        $html .= '<script type="text/javascript">SUGAR.language.setLanguage("AOS_Quotes",' . json_encode($quotes_lang) . ');</script>';
        $html .= '<script language="javascript">var sig_digits = '.$locale->getPrecision().';';
        $html .= 'var module_sugar_grp1 = "'.$focus->module_dir.'";';
        $html .= 'var enable_groups = '.$enable_groups.';';
        $html .= 'var total_tax = '.$total_tax.';';
        $html .= '</script>';

        $html .= "<table border='0' cellspacing='4' id='lineItems'></table>";

        if ($enable_groups) {
            $html .= "<div style='padding-top: 10px; padding-bottom:10px;'>";
            $html .= "<input type=\"button\" tabindex=\"116\" class=\"button\" value=\"".$mod_strings['LBL_ADD_GROUP']."\" id=\"addGroup\" onclick=\"insertGroup(0)\" />";
            $html .= "</div>";
        }
        $html .= '<input type="hidden" name="vathidden" id="vathidden" value="'.get_select_options_with_id($app_list_strings['vat_list'], '').'">
                  <input type="hidden" name="discounthidden" id="discounthidden" value="'.get_select_options_with_id($app_list_strings['discount_list'], '').'">';
        if ($focus->id != '') {
            require_once('modules/AOS_Products_Quotes/AOS_Products_Quotes.php');
            require_once('modules/AOS_Line_Item_Groups/AOS_Line_Item_Groups.php');

            $sql = "SELECT pg.id, pg.group_id FROM aos_products_quotes pg LEFT JOIN aos_line_item_groups lig ON pg.group_id = lig.id WHERE pg.parent_type = '" . $focus->object_name . "' AND pg.parent_id = '" . $focus->id . "' AND pg.deleted = 0 ORDER BY lig.number ASC, pg.number ASC";

            $result = $focus->db->query($sql);
            $html .= "<script>
                if(typeof sqs_objects == 'undefined'){var sqs_objects = new Array;}
                </script>";

            while ($row = $focus->db->fetchByAssoc($result)) {
                $line_item = BeanFactory::newBean('AOS_Products_Quotes');
                $line_item->retrieve($row['id'], false);
                $line_item = json_encode($line_item->toArray());

                $group_item = 'null';
                if ($row['group_id'] != null) {
                    $group_item = BeanFactory::newBean('AOS_Line_Item_Groups');
                    $group_item->retrieve($row['group_id'], false);
                    $group_item = json_encode($group_item->toArray());
                }
                $html .= "<script>
                        insertLineItems(" . $line_item . "," . $group_item . ");
                    </script>";
            }
        }
        if (!$enable_groups) {
            $html .= '<script>insertGroup();</script>';
        }
    } elseif ($view == 'DetailView') {
        $params = array('currency_id' => $focus->currency_id);

        $sql = "SELECT pg.id, pg.group_id FROM aos_products_quotes pg LEFT JOIN aos_line_item_groups lig ON pg.group_id = lig.id WHERE pg.parent_type = '".$focus->object_name."' AND pg.parent_id = '".$focus->id."' AND pg.deleted = 0 ORDER BY lig.number ASC, pg.number ASC";

        $result = $focus->db->query($sql);
        $sep = get_number_separators();

        $html .= "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";

        $i = 0;
        $productCount = 0;
        $serviceCount = 0;
        $group_id = '';
        $groupStart = '';
        $groupEnd = '';
        $product = '';
        $service = '';

        while ($row = $focus->db->fetchByAssoc($result)) {
            $line_item = BeanFactory::newBean('AOS_Products_Quotes');
            $line_item->retrieve($row['id']);

            if ($enable_groups && ($group_id != $row['group_id'] || $i == 0)) {
                $html .= $groupStart.$product.$service.$groupEnd;
                if ($i != 0) {
                    $html .= "<tr><td colspan='6' nowrap='nowrap'><br></td></tr>";
                }
                $groupStart = '';
                $groupEnd = '';
                $product = '';
                $service = '';
                $i = 1;
                $productCount = 0;
                $serviceCount = 0;
                $group_id = $row['group_id'];

                $group_item = BeanFactory::newBean('AOS_Line_Item_Groups');
                $group_item->retrieve($row['group_id']);

                $groupStart .= "<tr>";
                $groupStart .= "<td class='tabDetailViewDL' style='text-align: left;padding:2px;' scope='row'>Group:</td>";
                $groupStart .= "<td class='tabDetailViewDL' colspan='5' style='text-align: left;padding:2px;'>".$group_item->name."</td>";
                $groupStart .= "</tr>";

                $groupEnd = "<tr><td colspan='6' nowrap='nowrap'><br></td></tr>";
                $groupEnd .= "<tr>";
                $groupEnd .= "<td class='tabDetailViewDL' colspan='5' style='text-align: right;padding:2px;' scope='row'>".$mod_strings['LBL_SUBTOTAL_AMOUNT'].":&nbsp;&nbsp;</td>";
                $groupEnd .= "<td class='tabDetailViewDL' style='text-align: right;padding:2px;'>".currency_format_number($group_item->subtotal_amount ?? 0, $params)."</td>";
                $groupEnd .= "</tr>";
                $groupEnd .= "<tr>";
                $groupEnd .= "<td class='tabDetailViewDL' colspan='5' style='text-align: right;padding:2px;' scope='row'>".$mod_strings['LBL_GRAND_TOTAL'].":&nbsp;&nbsp;</td>";
                $groupEnd .= "<td class='tabDetailViewDL' style='text-align: right;padding:2px;'>".currency_format_number($group_item->total_amount ?? 0, $params)."</td>";
                $groupEnd .= "</tr>";
            }

            // Both products and services use the same 6-column layout
            if ($line_item->product_id != '0' && $line_item->product_id != null) {
                // Product row
                if ($productCount == 0) {
                    $product .= "<tr>";
                    $product .= "<td width='25%' class='tabDetailViewDL' style='text-align: left;padding:2px;' scope='row'>Product/Service</td>";
                    $product .= "<td width='25%' class='tabDetailViewDL' style='text-align: left;padding:2px;' scope='row'>Description</td>";
                    $product .= "<td width='10%' class='tabDetailViewDL' style='text-align: right;padding:2px;' scope='row'>Qty</td>";
                    $product .= "<td width='15%' class='tabDetailViewDL' style='text-align: right;padding:2px;' scope='row'>Unit Price</td>";
                    $product .= "<td width='12%' class='tabDetailViewDL' style='text-align: right;padding:2px;' scope='row'>Profit</td>";
                    $product .= "<td width='13%' class='tabDetailViewDL' style='text-align: right;padding:2px;' scope='row'>Line Total</td>";
                    $product .= "</tr>";
                }

                // Calculate profit: (Unit Price - Cost Price) × Qty
                $cost = (float)($line_item->product_cost_price ?? 0);
                $unit = (float)($line_item->product_unit_price ?? 0);
                $qty = (float)($line_item->product_qty ?? 0);
                $profit = ($unit - $cost) * $qty;

                $description = htmlspecialchars($line_item->description ?? '');

                $product .= "<tr>";
                $product .= "<td class='tabDetailViewDF' style='padding:2px;'><a href='index.php?module=AOS_Products&action=DetailView&record=".$line_item->product_id."' class='tabDetailViewDFLink'>".$line_item->name."</a></td>";
                $product .= "<td class='tabDetailViewDF' style='padding:2px;'>".$description."</td>";
                $product .= "<td class='tabDetailViewDF' style='text-align: right; padding:2px;'>".stripDecimalPointsAndTrailingZeroes(format_number($line_item->product_qty), $sep[1])."</td>";
                $product .= "<td class='tabDetailViewDF' style='text-align: right; padding:2px;'>".currency_format_number($line_item->product_unit_price, $params)."</td>";
                $product .= "<td class='tabDetailViewDF' style='text-align: right; padding:2px;'>".currency_format_number($profit, $params)."</td>";
                $product .= "<td class='tabDetailViewDF' style='text-align: right; padding:2px;'>".currency_format_number($line_item->product_total_price, $params)."</td>";
                $product .= "</tr>";
                $productCount++;
            } else {
                // Service row (no product_id — uses same column layout)
                if ($serviceCount == 0 && $productCount == 0) {
                    // Only show headers if no product headers were shown
                    $service .= "<tr>";
                    $service .= "<td width='25%' class='tabDetailViewDL' style='text-align: left;padding:2px;' scope='row'>Product/Service</td>";
                    $service .= "<td width='25%' class='tabDetailViewDL' style='text-align: left;padding:2px;' scope='row'>Description</td>";
                    $service .= "<td width='10%' class='tabDetailViewDL' style='text-align: right;padding:2px;' scope='row'>Qty</td>";
                    $service .= "<td width='15%' class='tabDetailViewDL' style='text-align: right;padding:2px;' scope='row'>Unit Price</td>";
                    $service .= "<td width='12%' class='tabDetailViewDL' style='text-align: right;padding:2px;' scope='row'>Profit</td>";
                    $service .= "<td width='13%' class='tabDetailViewDL' style='text-align: right;padding:2px;' scope='row'>Line Total</td>";
                    $service .= "</tr>";
                }

                $cost = (float)($line_item->product_cost_price ?? 0);
                $unit = (float)($line_item->product_unit_price ?? 0);
                $qty = (float)($line_item->product_qty ?? 0);
                $profit = ($unit - $cost) * $qty;

                $description = htmlspecialchars($line_item->description ?? '');

                $service .= "<tr>";
                $service .= "<td class='tabDetailViewDF' style='padding:2px;'>".$line_item->name."</td>";
                $service .= "<td class='tabDetailViewDF' style='padding:2px;'>".$description."</td>";
                $service .= "<td class='tabDetailViewDF' style='text-align: right; padding:2px;'>".stripDecimalPointsAndTrailingZeroes(format_number($line_item->product_qty), $sep[1])."</td>";
                $service .= "<td class='tabDetailViewDF' style='text-align: right; padding:2px;'>".currency_format_number($line_item->product_unit_price, $params)."</td>";
                $service .= "<td class='tabDetailViewDF' style='text-align: right; padding:2px;'>".currency_format_number($profit, $params)."</td>";
                $service .= "<td class='tabDetailViewDF' style='text-align: right; padding:2px;'>".currency_format_number($line_item->product_total_price, $params)."</td>";
                $service .= "</tr>";
                $serviceCount++;
            }
        }
        $html .= $groupStart.$product.$service.$groupEnd;
        $html .= "</table>";
    }
    return $html;
}

} // end if !function_exists('display_lines')

// Preserved from original
if (!function_exists('stripDecimalPointsAndTrailingZeroes')) {
function stripDecimalPointsAndTrailingZeroes($inputString, $decimalSeparator)
{
    return preg_replace('/'.preg_quote((string) $decimalSeparator).'[0]+$/', '', (string) $inputString);
}

} // end if !function_exists('stripDecimalPointsAndTrailingZeroes')

if (!function_exists('get_discount_string')) {
function get_discount_string($type, $amount, $params, $locale, $sep)
{
    if ($amount != '' && $amount != '0.00') {
        if ($type == 'Amount') {
            return currency_format_number($amount, $params)."</td>";
        } elseif ($locale->getPrecision()) {
            return rtrim(rtrim(format_number($amount), '0'), $sep[1])."%";
        }
        return format_number($amount)."%";
    }
    return "-";
}

} // end if !function_exists('get_discount_string')

if (!function_exists('display_shipping_vat')) {
function display_shipping_vat($focus, $field, $value, $view)
{
    if ($view == 'EditView') {
        global $app_list_strings;

        if ($value != '') {
            $value = format_number($value);
        }

        $html = "<input id='shipping_tax_amt' type='text' tabindex='0' title='' value='".$value."' maxlength='26,6' size='22' name='shipping_tax_amt' onblur='calculateTotal(\"lineItems\");'>";
        $html .= "<select name='shipping_tax' id='shipping_tax' onchange='calculateTotal(\"lineItems\");' >".get_select_options_with_id($app_list_strings['vat_list'], (isset($focus->shipping_tax) ? $focus->shipping_tax : ''))."</select>";

        return $html;
    }
    return format_number($value);
}

} // end if !function_exists('display_shipping_vat')

if (!function_exists('display_tax_detail_view')) {
function display_tax_detail_view($locale, $value, $sep): string
{
    global $app_strings;

    if ($locale->getPrecision()) {
        $value = rtrim(rtrim(format_number($value), '0'), $sep[1]) . $app_strings['LBL_PERCENTAGE_SYMBOL'];
    } else {
        $value = format_number($value) . $app_strings['LBL_PERCENTAGE_SYMBOL'];
    }

    return $value;
}
} // end if !function_exists('display_tax_detail_view')
