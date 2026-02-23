<table width="100%" cellpadding="0" cellspacing="0" border="0" class="edit view">
    <tr>
        <td valign="top" class="dataLabel">{$titleLbl}</td>
        <td valign="top" class="dataField">
            <input type="text" name="title" id="title_{$id}" size="30" maxlength="50" value="{$title}">
        </td>
    </tr>
    <tr>
        <td valign="top" class="dataLabel">{$heightLbl}</td>
        <td valign="top" class="dataField">
            <select name="height" id="height_{$id}">
                <option value="200" {if $height == '200'}selected{/if}>200</option>
                <option value="300" {if $height == '300'}selected{/if}>300</option>
                <option value="400" {if $height == '400'}selected{/if}>400</option>
                <option value="500" {if $height == '500'}selected{/if}>500</option>
                <option value="600" {if $height == '600'}selected{/if}>600</option>
            </select>
        </td>
    </tr>
</table>
