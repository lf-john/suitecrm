/**
 * SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.
 * Copyright (C) 2021 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Supercharged by SuiteCRM" logo. If the display of the logos is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Supercharged by SuiteCRM".
 */
import { Injectable } from '@angular/core';
import { ValidationManager } from '../validation/validation.manager';
import { DataTypeFormatter } from '../../formatters/data-type.formatter.service';
import { FilterFieldBuilder } from './filter-field.builder';
import isObjectLike from 'lodash-es/isObjectLike';
import { FieldObjectRegistry } from "./field-object-type.registry";
import * as i0 from "@angular/core";
import * as i1 from "../validation/validation.manager";
import * as i2 from "../../formatters/data-type.formatter.service";
import * as i3 from "./field-object-type.registry";
class FilterAttributeBuilder extends FilterFieldBuilder {
    constructor(validationManager, typeFormatter, fieldRegistry) {
        super(validationManager, typeFormatter, fieldRegistry);
        this.validationManager = validationManager;
        this.typeFormatter = typeFormatter;
        this.fieldRegistry = fieldRegistry;
    }
    /**
     * Build filter attribute
     *
     * @param {object} savedFilter SavedFilter
     * @param {object} parentField Field
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @returns {object} FieldAttribute
     */
    buildFilterAttribute(savedFilter, parentField, viewField, language = null) {
        const definition = (viewField && viewField.fieldDefinition) || {};
        if (!definition.valuePath) {
            definition.valuePath = 'criteria.' + (viewField.name || definition.name);
        }
        const { value, valueList, valueObject } = this.parseFilterAttributeValue(viewField, definition, savedFilter, parentField);
        const { validators, asyncValidators } = this.getFilterValidators(savedFilter, viewField);
        const field = this.setupField(savedFilter.searchModule, viewField, value, valueList, valueObject, savedFilter, definition, validators, asyncValidators, language);
        const fieldAttribute = field;
        fieldAttribute.valuePath = definition.valuePath;
        fieldAttribute.source = 'attribute';
        fieldAttribute.parentKey = parentField.definition.name;
        return fieldAttribute;
    }
    /**
     * Add attribute to SavedFilter
     *
     * @param {object} savedFilter SavedFilter
     * @param {object} field Field
     * @param {string} name string
     * @param {object} attribute FieldAttribute
     */
    addAttributeToSavedFilter(savedFilter, field, name, attribute) {
        if (!savedFilter || !name || !field || !attribute) {
            return;
        }
        field.attributes = field.attributes || {};
        field.attributes[name] = attribute;
        if (savedFilter.criteriaFormGroup && attribute.formControl) {
            savedFilter.criteriaFormGroup.addControl(name, attribute.formControl);
        }
    }
    /**
     * Parse filter attribute from field
     *
     * @param {object} viewField ViewFieldDefinition
     * @param {object} definition FieldDefinition
     * @param {object} record Record
     * @param {object} field Field
     * @returns {object} value object
     */
    parseFilterAttributeValue(viewField, definition, record, field) {
        const viewName = viewField.name || '';
        let value;
        if (!viewName) {
            value = '';
        }
        else {
            value = this.getParentValue(record, field, viewName, definition);
        }
        if (Array.isArray(value)) {
            return {
                value: null,
                valueList: value,
                valueObject: null
            };
        }
        if (isObjectLike(value)) {
            return {
                value: null,
                valueList: null,
                valueObject: value
            };
        }
        return { value, valueList: null, valueObject: null };
    }
    static { this.ɵfac = function FilterAttributeBuilder_Factory(t) { return new (t || FilterAttributeBuilder)(i0.ɵɵinject(i1.ValidationManager), i0.ɵɵinject(i2.DataTypeFormatter), i0.ɵɵinject(i3.FieldObjectRegistry)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: FilterAttributeBuilder, factory: FilterAttributeBuilder.ɵfac, providedIn: 'root' }); }
}
export { FilterAttributeBuilder };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(FilterAttributeBuilder, [{
        type: Injectable,
        args: [{
                providedIn: 'root'
            }]
    }], function () { return [{ type: i1.ValidationManager }, { type: i2.DataTypeFormatter }, { type: i3.FieldObjectRegistry }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZmlsdGVyLWF0dHJpYnV0ZS5idWlsZGVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vY29yZS9hcHAvY29yZS9zcmMvbGliL3NlcnZpY2VzL3JlY29yZC9maWVsZC9maWx0ZXItYXR0cmlidXRlLmJ1aWxkZXIudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQXdCRztBQUVILE9BQU8sRUFBQyxVQUFVLEVBQUMsTUFBTSxlQUFlLENBQUM7QUFDekMsT0FBTyxFQUFDLGlCQUFpQixFQUFDLE1BQU0sa0NBQWtDLENBQUM7QUFDbkUsT0FBTyxFQUFDLGlCQUFpQixFQUFDLE1BQU0sOENBQThDLENBQUM7QUFJL0UsT0FBTyxFQUFDLGtCQUFrQixFQUFDLE1BQU0sd0JBQXdCLENBQUM7QUFDMUQsT0FBTyxZQUFZLE1BQU0sd0JBQXdCLENBQUM7QUFDbEQsT0FBTyxFQUFDLG1CQUFtQixFQUFDLE1BQU0sOEJBQThCLENBQUM7Ozs7O0FBRWpFLE1BR2Esc0JBQXVCLFNBQVEsa0JBQWtCO0lBRTFELFlBQ2MsaUJBQW9DLEVBQ3BDLGFBQWdDLEVBQ2hDLGFBQWtDO1FBRTVDLEtBQUssQ0FBQyxpQkFBaUIsRUFBRSxhQUFhLEVBQUUsYUFBYSxDQUFDLENBQUM7UUFKN0Msc0JBQWlCLEdBQWpCLGlCQUFpQixDQUFtQjtRQUNwQyxrQkFBYSxHQUFiLGFBQWEsQ0FBbUI7UUFDaEMsa0JBQWEsR0FBYixhQUFhLENBQXFCO0lBR2hELENBQUM7SUFFRDs7Ozs7Ozs7T0FRRztJQUNJLG9CQUFvQixDQUN2QixXQUF3QixFQUN4QixXQUFrQixFQUNsQixTQUE4QixFQUM5QixXQUEwQixJQUFJO1FBRzlCLE1BQU0sVUFBVSxHQUFHLENBQUMsU0FBUyxJQUFJLFNBQVMsQ0FBQyxlQUFlLENBQUMsSUFBSSxFQUFxQixDQUFDO1FBRXJGLElBQUksQ0FBQyxVQUFVLENBQUMsU0FBUyxFQUFF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VSxFQUFFLE1BQU07YUFDckIiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFN1aXRlQ1JNIGlzIGEgY3VzdG9tZXIgcmVsYXRpb25zaGlwIG1hbmFnZW1lbnQgcHJvZ3JhbSBkZXZlbG9wZWQgYnkgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqIENvcHlyaWdodCAoQykgMjAyMSBTYWxlc0FnaWxpdHkgTHRkLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBmcmVlIHNvZnR3YXJlOyB5b3UgY2FuIHJlZGlzdHJpYnV0ZSBpdCBhbmQvb3IgbW9kaWZ5IGl0IHVuZGVyXG4gKiB0aGUgdGVybXMgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSB2ZXJzaW9uIDMgYXMgcHVibGlzaGVkIGJ5IHRoZVxuICogRnJlZSBTb2Z0d2FyZSBGb3VuZGF0aW9uIHdpdGggdGhlIGFkZGl0aW9uIG9mIHRoZSBmb2xsb3dpbmcgcGVybWlzc2lvbiBhZGRlZFxuICogdG8gU2VjdGlvbiAxNSBhcyBwZXJtaXR0ZWQgaW4gU2VjdGlvbiA3KGEpOiBGT1IgQU5ZIFBBUlQgT0YgVEhFIENPVkVSRUQgV09SS1xuICogSU4gV0hJQ0ggVEhFIENPUFlSSUdIVCBJUyBPV05FRCBCWSBTQUxFU0FHSUxJVFksIFNBTEVTQUdJTElUWSBESVNDTEFJTVMgVEhFXG4gKiBXQVJSQU5UWSBPRiBOT04gSU5GUklOR0VNRU5UIE9GIFRISVJEIFBBUlRZIFJJR0hUUy5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZGlzdHJpYnV0ZWQgaW4gdGhlIGhvcGUgdGhhdCBpdCB3aWxsIGJlIHVzZWZ1bCwgYnV0IFdJVEhPVVRcbiAqIEFOWSBXQVJSQU5UWTsgd2l0aG91dCBldmVuIHRoZSBpbXBsaWVkIHdhcnJhbnR5IG9mIE1FUkNIQU5UQUJJTElUWSBvciBGSVRORVNTXG4gKiBGT1IgQSBQQVJUSUNVTEFSIFBVUlBPU0UuIFNlZSB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIGZvciBtb3JlXG4gKiBkZXRhaWxzLlxuICpcbiAqIFlvdSBzaG91bGQgaGF2ZSByZWNlaXZlZCBhIGNvcHkgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogYWxvbmcgd2l0aCB0aGlzIHByb2dyYW0uICBJZiBub3QsIHNlZSA8aHR0cDovL3d3dy5nbnUub3JnL2xpY2Vuc2VzLz4uXG4gKlxuICogSW4gYWNjb3JkYW5jZSB3aXRoIFNlY3Rpb24gNyhiKSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiB2ZXJzaW9uIDMsIHRoZXNlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCByZXRhaW4gdGhlIGRpc3BsYXkgb2YgdGhlXG4gKiBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiIGxvZ28uIElmIHRoZSBkaXNwbGF5IG9mIHRoZSBsb2dvcyBpcyBub3QgcmVhc29uYWJseVxuICogZmVhc2libGUgZm9yIHRlY2huaWNhbCByZWFzb25zLCB0aGUgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IGRpc3BsYXlcbiAqIHRoZSB3b3JkcyBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiLlxuICovXG5cbmltcG9ydCB7SW5qZWN0YWJsZX0gZnJvbSAnQGFuZ3VsYXIvY29yZSc7XG5pbXBvcnQge1ZhbGlkYXRpb25NYW5hZ2VyfSBmcm9tICcuLi92YWxpZGF0aW9uL3ZhbGlkYXRpb24ubWFuYWdlcic7XG5pbXBvcnQge0RhdGFUeXBlRm9ybWF0dGVyfSBmcm9tICcuLi8uLi9mb3JtYXR0ZXJzL2RhdGEtdHlwZS5mb3JtYXR0ZXIuc2VydmljZSc7XG5pbXBvcnQge1NhdmVkRmlsdGVyfSBmcm9tICcuLi8uLi8uLi9zdG9yZS9zYXZlZC1maWx0ZXJzL3NhdmVkLWZpbHRlci5tb2RlbCc7XG5pbXBvcnQge0ZpZWxkLCBGaWVsZEF0dHJpYnV0ZSwgRmllbGREZWZpbml0aW9uLCBSZWNvcmQsIFZpZXdGaWVsZERlZmluaXRpb259IGZyb20gJ2NvbW1vbic7XG5pbXBvcnQge0xhbmd1YWdlU3RvcmV9IGZyb20gJy4uLy4uLy4uL3N0b3JlL2xhbmd1YWdlL2xhbmd1YWdlLnN0b3JlJztcbmltcG9ydCB7RmlsdGVyRmllbGRCdWlsZGVyfSBmcm9tICcuL2ZpbHRlci1maWVsZC5idWlsZGVyJztcbmltcG9ydCBpc09iamVjdExpa2UgZnJvbSAnbG9kYXNoLWVzL2lzT2JqZWN0TGlrZSc7XG5pbXBvcnQge0ZpZWxkT2JqZWN0UmVnaXN0cnl9IGZyb20gXCIuL2ZpZWxkLW9iamVjdC10eXBlLnJlZ2lzdHJ5XCI7XG5cbkBJbmplY3RhYmxlKHtcbiAgICBwcm92aWRlZEluOiAncm9vdCdcbn0pXG5leHBvcnQgY2xhc3MgRmlsdGVyQXR0cmlidXRlQnVpbGRlciBleHRlbmRzIEZpbHRlckZpZWxkQnVpbGRlciB7XG5cbiAgICBjb25zdHJ1Y3RvcihcbiAgICAgICAgcHJvdGVjdGVkIHZhbGlkYXRpb25NYW5hZ2VyOiBWYWxpZGF0aW9uTWFuYWdlcixcbiAgICAgICAgcHJvdGVjdGVkIHR5cGVGb3JtYXR0ZXI6IERhdGFUeXBlRm9ybWF0dGVyLFxuICAgICAgICBwcm90ZWN0ZWQgZmllbGRSZWdpc3RyeTogRmllbGRPYmplY3RSZWdpc3RyeVxuICAgICkge1xuICAgICAgICBzdXBlcih2YWxpZGF0aW9uTWFuYWdlciwgdHlwZUZvcm1hdHRlciwgZmllbGRSZWdpc3RyeSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQnVpbGQgZmlsdGVyIGF0dHJpYnV0ZVxuICAgICAqXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHNhdmVkRmlsdGVyIFNhdmVkRmlsdGVyXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHBhcmVudEZpZWxkIEZpZWxkXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHZpZXdGaWVsZCBWaWV3RmllbGREZWZpbml0aW9uXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGxhbmd1YWdlIExhbmd1YWdlU3RvcmVcbiAgICAgKiBAcmV0dXJucyB7b2JqZWN0fSBGaWVsZEF0dHJpYnV0ZVxuICAgICAqL1xuICAgIHB1YmxpYyBidWlsZEZpbHRlckF0dHJpYnV0ZShcbiAgICAgICAgc2F2ZWRGaWx0ZXI6IFNhdmVkRmlsdGVyLFxuICAgICAgICBwYXJlbnRGaWVsZDogRmllbGQsXG4gICAgICAgIHZpZXdGaWVsZDogVmlld0ZpZWxkRGVmaW5pdGlvbixcbiAgICAgICAgbGFuZ3VhZ2U6IExhbmd1YWdlU3RvcmUgPSBudWxsXG4gICAgKTogRmllbGRBdHRyaWJ1dGUge1xuXG4gICAgICAgIGNvbnN0IGRlZmluaXRpb24gPSAodmlld0ZpZWxkICYmIHZpZXdGaWVsZC5maWVsZERlZmluaXRpb24pIHx8IHt9IGFzIEZpZWxkRGVmaW5pdGlvbjtcblxuICAgICAgICBpZiAoIWRlZmluaXRpb24udmFsdWVQYXRoKSB7XG4gICAgICAgICAgICBkZWZpbml0aW9uLnZhbHVlUGF0aCA9ICdjcml0ZXJpYS4nICsgKHZpZXdGaWVsZC5uYW1lIHx8IGRlZmluaXRpb24ubmFtZSk7XG4gICAgICAgIH1cblxuICAgICAgICBjb25zdCB7dmFsdWUsIHZhbHVlTGlzdCwgdmFsdWVPYmplY3R9ID0gdGhpcy5wYXJzZUZpbHRlckF0dHJpYnV0ZVZhbHVlKHZpZXdGaWVsZCwgZGVmaW5pdGlvbiwgc2F2ZWRGaWx0ZXIsIHBhcmVudEZpZWxkKTtcbiAgICAgICAgY29uc3Qge3ZhbGlkYXRvcnMsIGFzeW5jVmFsaWRhdG9yc30gPSB0aGlzLmdldEZpbHRlclZhbGlkYXRvcnMoc2F2ZWRGaWx0ZXIsIHZpZXdGaWVsZCk7XG5cbiAgICAgICAgY29uc3QgZmllbGQgPSB0aGlzLnNldHVwRmllbGQoXG4gICAgICAgICAgICBzYXZlZEZpbHRlci5zZWFyY2hNb2R1bGUsXG4gICAgICAgICAgICB2aWV3RmllbGQsXG4gICAgICAgICAgICB2YWx1ZSxcbiAgICAgICAgICAgIHZhbHVlTGlzdCxcbiAgICAgICAgICAgIHZhbHVlT2JqZWN0LFxuICAgICAgICAgICAgc2F2ZWRGaWx0ZXIsXG4gICAgICAgICAgICBkZWZpbml0aW9uLFxuICAgICAgICAgICAgdmFsaWRhdG9ycyxcbiAgICAgICAgICAgIGFzeW5jVmFsaWRhdG9ycyxcbiAgICAgICAgICAgIGxhbmd1YWdlXG4gICAgICAgICk7XG5cbiAgICAgICAgY29uc3QgZmllbGRBdHRyaWJ1dGUgPSBmaWVsZCBhcyBGaWVsZEF0dHJpYnV0ZTtcbiAgICAgICAgZmllbGRBdHRyaWJ1dGUudmFsdWVQYXRoID0gZGVmaW5pdGlvbi52YWx1ZVBhdGg7XG4gICAgICAgIGZpZWxkQXR0cmlidXRlLnNvdXJjZSA9ICdhdHRyaWJ1dGUnO1xuICAgICAgICBmaWVsZEF0dHJpYnV0ZS5wYXJlbnRLZXkgPSBwYXJlbnRGaWVsZC5kZWZpbml0aW9uLm5hbWU7XG5cbiAgICAgICAgcmV0dXJuIGZpZWxkQXR0cmlidXRlO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEFkZCBhdHRyaWJ1dGUgdG8gU2F2ZWRGaWx0ZXJcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBzYXZlZEZpbHRlciBTYXZlZEZpbHRlclxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBmaWVsZCBGaWVsZFxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBuYW1lIHN0cmluZ1xuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBhdHRyaWJ1dGUgRmllbGRBdHRyaWJ1dGVcbiAgICAgKi9cbiAgICBwdWJsaWMgYWRkQXR0cmlidXRlVG9TYXZlZEZpbHRlcihzYXZlZEZpbHRlcjogU2F2ZWRGaWx0ZXIsIGZpZWxkOiBGaWVsZCwgbmFtZTogc3RyaW5nLCBhdHRyaWJ1dGU6IEZpZWxkQXR0cmlidXRlKTogdm9pZCB7XG5cbiAgICAgICAgaWYgKCFzYXZlZEZpbHRlciB8fCAhbmFtZSB8fCAhZmllbGQgfHwgIWF0dHJpYnV0ZSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgZmllbGQuYXR0cmlidXRlcyA9IGZpZWxkLmF0dHJpYnV0ZXMgfHwge307XG5cbiAgICAgICAgZmllbGQuYXR0cmlidXRlc1tuYW1lXSA9IGF0dHJpYnV0ZTtcblxuICAgICAgICBpZiAoc2F2ZWRGaWx0ZXIuY3JpdGVyaWFGb3JtR3JvdXAgJiYgYXR0cmlidXRlLmZvcm1Db250cm9sKSB7XG4gICAgICAgICAgICBzYXZlZEZpbHRlci5jcml0ZXJpYUZvcm1Hcm91cC5hZGRDb250cm9sKG5hbWUsIGF0dHJpYnV0ZS5mb3JtQ29udHJvbCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBQYXJzZSBmaWx0ZXIgYXR0cmlidXRlIGZyb20gZmllbGRcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSB2aWV3RmllbGQgVmlld0ZpZWxkRGVmaW5pdGlvblxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBkZWZpbml0aW9uIEZpZWxkRGVmaW5pdGlvblxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSByZWNvcmQgUmVjb3JkXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGZpZWxkIEZpZWxkXG4gICAgICogQHJldHVybnMge29iamVjdH0gdmFsdWUgb2JqZWN0XG4gICAgICovXG4gICAgcHJvdGVjdGVkIHBhcnNlRmlsdGVyQXR0cmlidXRlVmFsdWUoXG4gICAgICAgIHZpZXdGaWVsZDogVmlld0ZpZWxkRGVmaW5pdGlvbixcbiAgICAgICAgZGVmaW5pdGlvbjogRmllbGREZWZpbml0aW9uLFxuICAgICAgICByZWNvcmQ6IFJlY29yZCxcbiAgICAgICAgZmllbGQ6IEZpZWxkXG4gICAgKTogeyB2YWx1ZTogc3RyaW5nOyB2YWx1ZUxpc3Q6IHN0cmluZ1tdOyB2YWx1ZU9iamVjdD86IGFueSB9IHtcblxuICAgICAgICBjb25zdCB2aWV3TmFtZSA9IHZpZXdGaWVsZC5uYW1lIHx8ICcnO1xuICAgICAgICBsZXQgdmFsdWU6IGFueTtcblxuICAgICAgICBpZiAoIXZpZXdOYW1lKSB7XG4gICAgICAgICAgICB2YWx1ZSA9ICcnO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgdmFsdWUgPSB0aGlzLmdldFBhcmVudFZhbHVlKHJlY29yZCwgZmllbGQsIHZpZXdOYW1lLCBkZWZpbml0aW9uKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChBcnJheS5pc0FycmF5KHZhbHVlKSkge1xuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICB2YWx1ZTogbnVsbCxcbiAgICAgICAgICAgICAgICB2YWx1ZUxpc3Q6IHZhbHVlLFxuICAgICAgICAgICAgICAgIHZhbHVlT2JqZWN0OiBudWxsXG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoaXNPYmplY3RMaWtlKHZhbHVlKSkge1xuICAgICAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgICAgICB2YWx1ZTogbnVsbCxcbiAgICAgICAgICAgICAgICB2YWx1ZUxpc3Q6IG51bGwsXG4gICAgICAgICAgICAgICAgdmFsdWVPYmplY3Q6IHZhbHVlXG4gICAgICAgICAgICB9XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4ge3ZhbHVlLCB2YWx1ZUxpc3Q6IG51bGwsIHZhbHVlT2JqZWN0OiBudWxsfTtcbiAgICB9XG59XG4iXX0=