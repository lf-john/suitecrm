<?php
/**
 * Custom Line Items Detail View — Logical Front
 * Columns: Product, Description, Qty, Cost, Markup, Price, Line Total
 *
 * Cost       = product_list_price
 * Markup     = product_discount (percentage)
 * Price      = product_unit_price
 * Line Total = product_total_price
 *
 * Totals (rendered inline, originals hidden via CSS):
 *   Cost        = Sum(Qty * List Price)
 *   Profit      = Sum((Unit Price - List Price) * Qty)
 *   Grand Total = Cost + Profit
 *
 * Based on: modules/AOS_Products_Quotes/Line_Items.php
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

        $html .= "<table border='0' width='100%' cellpadding='0' cellspacing='0' class='lf-line-items'>";

        $i = 0;
        $group_id = '';
        $headerShown = false;
        $totalCostSum = 0;
        $totalProfitSum = 0;

        while ($row = $focus->db->fetchByAssoc($result)) {
            $line_item = BeanFactory::newBean('AOS_Products_Quotes');
            $line_item->retrieve($row['id']);

            // Group handling
            if ($enable_groups && ($group_id != $row['group_id'] || $i == 0)) {
                if ($i != 0) {
                    $html .= "<tr><td colspan='7'><br></td></tr>";
                }
                $i = 1;
                $headerShown = false;
                $group_id = $row['group_id'];

                $group_item = BeanFactory::newBean('AOS_Line_Item_Groups');
                $group_item->retrieve($row['group_id']);

                $html .= "<tr>";
                $html .= "<td class='tabDetailViewDL' colspan='7' style='text-align:left;padding:2px;'>Group: ".$group_item->name."</td>";
                $html .= "</tr>";
            }

            // Show header row once (or once per group)
            if (!$headerShown) {
                $html .= "<tr>";
                $html .= "<td class='tabDetailViewDL' style='text-align:left;padding:2px;width:12%;'>Product</td>";
                $html .= "<td class='tabDetailViewDL' style='text-align:left;padding:2px;'>Description</td>";
                $html .= "<td class='tabDetailViewDL' style='text-align:right;padding:2px;width:6%;'>Qty</td>";
                $html .= "<td class='tabDetailViewDL' style='text-align:right;padding:2px;width:10%;'>Cost</td>";
                $html .= "<td class='tabDetailViewDL' style='text-align:right;padding:2px;width:8%;'>Markup</td>";
                $html .= "<td class='tabDetailViewDL' style='text-align:right;padding:2px;width:10%;'>Price</td>";
                $html .= "<td class='tabDetailViewDL' style='text-align:right;padding:2px;width:11%;'>Line Total</td>";
                $html .= "</tr>";
                $headerShown = true;
            }

            // Fetch product bean once for description
            $prod_bean = null;
            if ($line_item->product_id != '0' && $line_item->product_id != null && !empty($line_item->product_id)) {
                $prod_bean = BeanFactory::getBean('AOS_Products', $line_item->product_id);
            }

            // Field mapping:
            //   Cost   = product_list_price
            //   Markup = product_discount (stored as percentage, negative = markup)
            //   Price  = product_unit_price
            //   Line Total = product_total_price
            $cost = (float)($line_item->product_list_price ?? 0);
            $discount = (float)($line_item->product_discount ?? 0);
            $price = ceil((float)($line_item->product_unit_price ?? 0) * 100) / 100;
            $qty = (float)($line_item->product_qty ?? 0);
            $line_total = (float)($line_item->product_total_price ?? 0);

            // Markup display: discount is stored as negative for markups
            if ($discount != 0) {
                $markup_display = number_format($discount, 1) . '%';
            } else {
                $markup_display = '—';
            }

            // Running totals
            //   Cost total  = Sum(Qty * List Price)
            //   Profit      = Sum((Unit Price - List Price) * Qty)
            $totalCostSum += $qty * $cost;
            $totalProfitSum += ($price - $cost) * $qty;

            // Product vs Service display
            if ($prod_bean) {
                // Product: linked name, description from product record
                $nameCell = "<a href='index.php?module=AOS_Products&action=DetailView&record=".$line_item->product_id."' class='tabDetailViewDFLink'>".$line_item->name."</a>";
                $description = '';
                if (!empty($prod_bean->description)) {
                    $description = htmlspecialchars($prod_bean->description);
                }
            } else {
                // Service: "Service" in Product column, name in Description
                $nameCell = 'Service';
                $description = htmlspecialchars($line_item->name ?? '');
            }

            $html .= "<tr>";
            $html .= "<td class='tabDetailViewDF' style='padding:2px;'>".$nameCell."</td>";
            $html .= "<td class='tabDetailViewDF' style='padding:2px;'>".$description."</td>";
            $html .= "<td class='tabDetailViewDF' style='text-align:right;padding:2px;'>".stripDecimalPointsAndTrailingZeroes(format_number($qty), $sep[1])."</td>";
            $html .= "<td class='tabDetailViewDF' style='text-align:right;padding:2px;'>".currency_format_number($cost, $params)."</td>";
            $html .= "<td class='tabDetailViewDF' style='text-align:right;padding:2px;'>".$markup_display."</td>";
            $html .= "<td class='tabDetailViewDF' style='text-align:right;padding:2px;'>".currency_format_number($price, $params)."</td>";
            $html .= "<td class='tabDetailViewDF' style='text-align:right;padding:2px;'>".currency_format_number($line_total, $params)."</td>";
            $html .= "</tr>";
        }

        // Custom totals section
        $grandTotal = $totalCostSum + $totalProfitSum;

        $html .= "<tr><td colspan='7' style='height:16px;'></td></tr>";

        // Cost row
        $html .= "<tr class='lf-totals-row'>";
        $html .= "<td colspan='5'></td>";
        $html .= "<td class='tabDetailViewDL' style='text-align:right;padding:6px 8px;white-space:nowrap;'>Cost:</td>";
        $html .= "<td class='tabDetailViewDF' style='text-align:right;padding:6px 8px;'>".currency_format_number($totalCostSum, $params)."</td>";
        $html .= "</tr>";

        // Profit row
        $html .= "<tr class='lf-totals-row'>";
        $html .= "<td colspan='5'></td>";
        $html .= "<td class='tabDetailViewDL' style='text-align:right;padding:6px 8px;white-space:nowrap;'>Profit:</td>";
        $html .= "<td class='tabDetailViewDF' style='text-align:right;padding:6px 8px;'>".currency_format_number($totalProfitSum, $params)."</td>";
        $html .= "</tr>";

        // Grand Total row (blue, bold)
        $html .= "<tr class='lf-totals-row lf-grand-total'>";
        $html .= "<td colspan='5'></td>";
        $html .= "<td class='tabDetailViewDL' style='text-align:right;padding:6px 8px;white-space:nowrap;color:#125EAD;font-weight:700;'>Grand Total:</td>";
        $html .= "<td class='tabDetailViewDF' style='text-align:right;padding:6px 8px;color:#125EAD;font-weight:700;font-size:16px;'>".currency_format_number($grandTotal, $params)."</td>";
        $html .= "</tr>";

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
