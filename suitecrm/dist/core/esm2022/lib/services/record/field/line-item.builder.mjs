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
import { deepClone } from 'common';
import { ValidationManager } from '../validation/validation.manager';
import { DataTypeFormatter } from '../../formatters/data-type.formatter.service';
import { Injectable } from '@angular/core';
import { AttributeBuilder } from './attribute.builder';
import { UntypedFormGroup } from '@angular/forms';
import { FieldObjectRegistry } from "./field-object-type.registry";
import * as i0 from "@angular/core";
import * as i1 from "../validation/validation.manager";
import * as i2 from "../../formatters/data-type.formatter.service";
import * as i3 from "./field-object-type.registry";
class LineItemBuilder extends AttributeBuilder {
    constructor(validationManager, typeFormatter, fieldRegistry) {
        super(validationManager, typeFormatter, fieldRegistry);
        this.validationManager = validationManager;
        this.typeFormatter = typeFormatter;
        this.fieldRegistry = fieldRegistry;
    }
    /**
     * Create and add attributes fields to field
     *
     * @param {object} record Record
     * @param {object} fields FieldMap
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @param {function} buildLineItemFunction
     */
    addLineItems(record, fields, viewField, language, buildLineItemFunction) {
        const fieldKeys = Object.keys(fields) || [];
        if (fieldKeys.length < 1) {
            return;
        }
        fieldKeys.forEach(key => {
            const field = fields[key];
            this.addFieldLineItems(record, field, language, buildLineItemFunction);
        });
    }
    /**
     * Create and add attributes fields to field
     *
     * @param {object} record Record
     * @param {object} field Field
     * @param {object} language LanguageStore
     * @param {function} buildLineItemFunction
     */
    addFieldLineItems(record, field, language, buildLineItemFunction) {
        const definition = (field && field.definition) || {};
        const type = (field && field.type) || '';
        const items = (field.valueObjectArray && field.valueObjectArray) || [];
        if (type !== 'line-items' || !items.length) {
            return;
        }
        const itemDefinition = definition.lineItems?.definition || {};
        field.items = [];
        items.forEach(item => {
            this.addLineItem(itemDefinition, item, buildLineItemFunction, language, record, field);
        });
    }
    /**
     * Build line item and and to record
     * @param {object} itemDefinition
     * @param {object }item
     * @param {object} buildLineItemFunction
     * @param {object} language
     * @param {object} parentRecord
     * @param {object} parentField
     */
    addLineItem(itemDefinition, item, buildLineItemFunction, language, parentRecord, parentField) {
        const itemViewField = {
            name: itemDefinition.name,
            label: itemDefinition.vname,
            type: itemDefinition.type,
            fieldDefinition: deepClone(itemDefinition)
        };
        const itemRecord = {
            id: item.id || '',
            type: item.type || '',
            module: item.module || '',
            attributes: item.attributes || {},
            fields: {},
            formGroup: new UntypedFormGroup({})
        };
        buildLineItemFunction(itemRecord, itemViewField, language);
        parentField.itemFormArray.push(itemRecord.formGroup);
        parentField.items.push(itemRecord);
    }
    static { this.ɵfac = function LineItemBuilder_Factory(t) { return new (t || LineItemBuilder)(i0.ɵɵinject(i1.ValidationManager), i0.ɵɵinject(i2.DataTypeFormatter), i0.ɵɵinject(i3.FieldObjectRegistry)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: LineItemBuilder, factory: LineItemBuilder.ɵfac, providedIn: 'root' }); }
}
export { LineItemBuilder };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(LineItemBuilder, [{
        type: Injectable,
        args: [{
                providedIn: 'root'
            }]
    }], function () { return [{ type: i1.ValidationManager }, { type: i2.DataTypeFormatter }, { type: i3.FieldObjectRegistry }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibGluZS1pdGVtLmJ1aWxkZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvc2VydmljZXMvcmVjb3JkL2ZpZWxkL2xpbmUtaXRlbS5idWlsZGVyLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0F3Qkc7QUFFSCxPQUFPLEVBQUMsU0FBUyxFQUFnRSxNQUFNLFFBQVEsQ0FBQztBQUVoRyxPQUFPLEVBQUMsaUJBQWlCLEVBQUMsTUFBTSxrQ0FBa0MsQ0FBQztBQUNuRSxPQUFPLEVBQUMsaUJBQWlCLEVBQUMsTUFBTSw4Q0FBOEMsQ0FBQztBQUMvRSxPQUFPLEVBQUMsVUFBVSxFQUFDLE1BQU0sZUFBZSxDQUFDO0FBQ3pDLE9BQU8sRUFBQyxnQkFBZ0IsRUFBQyxNQUFNLHFCQUFxQixDQUFDO0FBQ3JELE9BQU8sRUFBQyxnQkFBZ0IsRUFBQyxNQUFNLGdCQUFnQixDQUFDO0FBQ2hELE9BQU8sRUFBQyxtQkFBbUIsRUFBQyxNQUFNLDhCQUE4QixDQUFDOzs7OztBQUVqRSxNQUdhLGVBQWdCLFNBQVEsZ0JBQWdCO0lBRWpELFlBQ2MsaUJBQW9DLEVBQ3BDLGFBQWdDLEVBQ2hDLGFBQWtDO1FBRTVDLEtBQUssQ0FBQyxpQkFBaUIsRUFBRSxhQUFhLEVBQUUsYUFBYSxDQUFDLENBQUM7UUFKN0Msc0JBQWlCLEdBQWpCLGlCQUFpQixDQUFtQjtRQUNwQyxrQkFBYSxHQUFiLGFBQWEsQ0FBbUI7UUFDaEMsa0JBQWEsR0FBYixhQUFhLENBQXFCO0lBR2hELENBQUM7SUFHRDs7Ozs7Ozs7T0FRRztJQUNJLFlBQVksQ0FDZixNQUFjLEVBQ2QsTUFBZ0IsRUFDaEIsU0FBOEIsRUFDOUIsUUFBdUIsRUFDdkIscUJBQStCO1FBRS9CLE1BQU0sU0FBUyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLElBQUksRUFBRSxDQUFDO1FBRzVDLElBQUksU0FBUyxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUU7WUFDdEIsT0FBTztTQUNWO1FBRUQsU0FBUyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBRTtZQUNwQixNQUFNLEtBQUssR0FBRyxNQUFNLENBQUMsR0FBRyxDQUFDLENBQUM7WUFDMUIsSUFBSSxDQUFDLGlCQUFpQixDQUNsQixNQUFNLEVBQ04sS0FBSyxFQUNMLFFBQVEsRUFDUixxQkFBcUIsQ0FDeEIsQ0FBQTtRQUNMLENBQUMsQ0FBQyxDQUFDO0lBRVAsQ0FBQztJQUVEOzs7Ozs7O09BT0c7SUFDSSxpQkFBaUIsQ0FDcEIsTUFBYyxFQUNkLEtBQVksRUFDWixRQUF1QixFQUN2QixxQkFBK0I7UUFHL0IsTUFBTSxVQUFVLEdBQUcsQ0FBQyxLQUFLLElBQUksS0FBSyxDQUFDLFVBQVUsQ0FBQyxJQUFJLEVBQUUsQ0FBQztRQUNyRCxNQUFNLElBQUksR0FBRyxDQUFDLEtBQUssSUFBSSxLQUFLLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDO1FBQ3pDLE1BQU0sS0FBSyxHQUFHLENBQUMsS0FBSyxDQUFDLGdCQUFnQixJQUFJLEtBQUssQ0FBQyxnQkFBZ0IsQ0FBQyxJQUFJLEVBQUUsQ0FBQztRQUV2RSxJQUFJLElBQUksS0FBSyxZQUFZLElBQUksQ0FBQyxLQUFLLENBQUMsTUFBTSxFQUFFO1lBQ3hDLE9BQU87U0FDVjtRQUVELE1BQU0sY0FBYyxHQUFvQixVQUFVLENBQUMsU0FBUyxFQUFFLFVBQVUsSUFBSSxFQUFFLENBQUM7UUFDL0UsS0FBSyxDQUFDLEtBQUssR0FBRyxFQUFFLENBQUM7UUFFakIsS0FBSyxDQUFDLE9BQU8sQ0FBQyxJQUFJLENBQUMsRUFBRTtZQUNqQixJQUFJLENBQUMsV0FBVyxDQUFDLGNBQWMsRUFBRSxJQUFjLEVBQUUscUJBQXFCLEVBQUUsUUFBUSxFQUFFLE1BQU0sRUFBRSxLQUFLLENBQUMsQ0FBQztRQUNyRyxDQUFDLENBQUMsQ0FBQztJQUNQLENBQUM7SUFFRDs7Ozs7Ozs7T0FRRztJQUNJLFdBQVcsQ0FDZCxjQUErQixFQUMvQixJQUFZLEVBQ1oscUJBQStCLEVBQy9CLFFBQXVCLEVBQ3ZCLFlBQW9CLEVBQ3BCLFdBQWtCO1FBR2xCLE1BQU0sYUFBYSxHQUFHO1lBQ2xCLElBQUksRUFBRSxjQUFjLENBQUMsSUFBSTtZQUN6QixLQUFLLEVBQUUsY0FBYyxDQUFDLEtBQUs7WUFDM0IsSUFBSSxFQUFFLGNBQWMsQ0FBQyxJQUFJO1lBQ3pCLGVBQWUsRUFBRSxTQUFTLENBQUMsY0FBYyxDQUFDO1NBQzdDLENBQUM7UUFFRixNQUFNLFVBQVUsR0FBRztZQUNmLEVBQUUsRUFBRSxJQUFJLENBQUMsRUFBRSxJQUFJLEVBQUU7WUFDakIsSUFBSSxFQUFFLElBQUksQ0FBQyxJQUFJLElBQUksRUFBRTtZQUNyQixNQUFNLEVBQUUsSUFBSSxDQUFDLE1BQU0sSUFBSSxFQUFFO1lBQ3pCLFVBQVUsRUFBRSxJQUFJLENBQUMsVUFBVSxJQUFJLEVBQUU7WUFDakMsTUFBTSxFQUFFLEVBQUU7WUFDVixTQUFTLEVBQUUsSUFBSSxnQkFBZ0IsQ0FBQyxFQUFFLENBQUM7U0FDNUIsQ0FBQztRQUVaLHFCQUFxQixDQUFDLFVBQVUsRUFBRSxhQUFhLEVBQUUsUUFBUSxDQUFDLENBQUM7UUFFM0QsV0FBVyxDQUFDLGFBQWEsQ0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLFNBQVMsQ0FBQyxDQUFDO1FBRXJELFdBQVcsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDO0lBQ3ZDLENBQUM7Z0ZBcEhRLGVBQWU7dUVBQWYsZUFBZSxXQUFmLGVBQWUsbUJBRlosTUFBTTs7U0FFVCxlQUFlO3VGQUFmLGVBQWU7Y0FIM0IsVUFBVTtlQUFDO2dCQUNSLFVBQVUsRUFBRSxNQUFNO2FBQ3JCIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuXG5pbXBvcnQge2RlZXBDbG9uZSwgRmllbGQsIEZpZWxkRGVmaW5pdGlvbiwgRmllbGRNYXAsIFJlY29yZCwgVmlld0ZpZWxkRGVmaW5pdGlvbn0gZnJvbSAnY29tbW9uJztcbmltcG9ydCB7TGFuZ3VhZ2VTdG9yZX0gZnJvbSAnLi4vLi4vLi4vc3RvcmUvbGFuZ3VhZ2UvbGFuZ3VhZ2Uuc3RvcmUnO1xuaW1wb3J0IHtWYWxpZGF0aW9uTWFuYWdlcn0gZnJvbSAnLi4vdmFsaWRhdGlvbi92YWxpZGF0aW9uLm1hbmFnZXInO1xuaW1wb3J0IHtEYXRhVHlwZUZvcm1hdHRlcn0gZnJvbSAnLi4vLi4vZm9ybWF0dGVycy9kYXRhLXR5cGUuZm9ybWF0dGVyLnNlcnZpY2UnO1xuaW1wb3J0IHtJbmplY3RhYmxlfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7QXR0cmlidXRlQnVpbGRlcn0gZnJvbSAnLi9hdHRyaWJ1dGUuYnVpbGRlcic7XG5pbXBvcnQge1VudHlwZWRGb3JtR3JvdXB9IGZyb20gJ0Bhbmd1bGFyL2Zvcm1zJztcbmltcG9ydCB7RmllbGRPYmplY3RSZWdpc3RyeX0gZnJvbSBcIi4vZmllbGQtb2JqZWN0LXR5cGUucmVnaXN0cnlcIjtcblxuQEluamVjdGFibGUoe1xuICAgIHByb3ZpZGVkSW46ICdyb290J1xufSlcbmV4cG9ydCBjbGFzcyBMaW5lSXRlbUJ1aWxkZXIgZXh0ZW5kcyBBdHRyaWJ1dGVCdWlsZGVyIHtcblxuICAgIGNvbnN0cnVjdG9yKFxuICAgICAgICBwcm90ZWN0ZWQgdmFsaWRhdGlvbk1hbmFnZXI6IFZhbGlkYXRpb25NYW5hZ2VyLFxuICAgICAgICBwcm90ZWN0ZWQgdHlwZUZvcm1hdHRlcjogRGF0YVR5cGVGb3JtYXR0ZXIsXG4gICAgICAgIHByb3RlY3RlZCBmaWVsZFJlZ2lzdHJ5OiBGaWVsZE9iamVjdFJlZ2lzdHJ5XG4gICAgKSB7XG4gICAgICAgIHN1cGVyKHZhbGlkYXRpb25NYW5hZ2VyLCB0eXBlRm9ybWF0dGVyLCBmaWVsZFJlZ2lzdHJ5KTtcbiAgICB9XG5cblxuICAgIC8qKlxuICAgICAqIENyZWF0ZSBhbmQgYWRkIGF0dHJpYnV0ZXMgZmllbGRzIHRvIGZpZWxkXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gcmVjb3JkIFJlY29yZFxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBmaWVsZHMgRmllbGRNYXBcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gdmlld0ZpZWxkIFZpZXdGaWVsZERlZmluaXRpb25cbiAgICAgKiBAcGFyYW0ge29iamVjdH0gbGFuZ3VhZ2UgTGFuZ3VhZ2VTdG9yZVxuICAgICAqIEBwYXJhbSB7ZnVuY3Rpb259IGJ1aWxkTGluZUl0ZW1GdW5jdGlvblxuICAgICAqL1xuICAgIHB1YmxpYyBhZGRMaW5lSXRlbXMoXG4gICAgICAgIHJlY29yZDogUmVjb3JkLFxuICAgICAgICBmaWVsZHM6IEZpZWxkTWFwLFxuICAgICAgICB2aWV3RmllbGQ6IFZpZXdGaWVsZERlZmluaXRpb24sXG4gICAgICAgIGxhbmd1YWdlOiBMYW5ndWFnZVN0b3JlLFxuICAgICAgICBidWlsZExpbmVJdGVtRnVuY3Rpb246IEZ1bmN0aW9uLFxuICAgICk6IHZvaWQge1xuICAgICAgICBjb25zdCBmaWVsZEtleXMgPSBPYmplY3Qua2V5cyhmaWVsZHMpIHx8IFtdO1xuXG5cbiAgICAgICAgaWYgKGZpZWxkS2V5cy5sZW5ndGggPCAxKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBmaWVsZEtleXMuZm9yRWFjaChrZXkgPT4ge1xuICAgICAgICAgICAgY29uc3QgZmllbGQgPSBmaWVsZHNba2V5XTtcbiAgICAgICAgICAgIHRoaXMuYWRkRmllbGRMaW5lSXRlbXMoXG4gICAgICAgICAgICAgICAgcmVjb3JkLFxuICAgICAgICAgICAgICAgIGZpZWxkLFxuICAgICAgICAgICAgICAgIGxhbmd1YWdlLFxuICAgICAgICAgICAgICAgIGJ1aWxkTGluZUl0ZW1GdW5jdGlvbixcbiAgICAgICAgICAgIClcbiAgICAgICAgfSk7XG5cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDcmVhdGUgYW5kIGFkZCBhdHRyaWJ1dGVzIGZpZWxkcyB0byBmaWVsZFxuICAgICAqXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHJlY29yZCBSZWNvcmRcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gZmllbGQgRmllbGRcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gbGFuZ3VhZ2UgTGFuZ3VhZ2VTdG9yZVxuICAgICAqIEBwYXJhbSB7ZnVuY3Rpb259IGJ1aWxkTGluZUl0ZW1GdW5jdGlvblxuICAgICAqL1xuICAgIHB1YmxpYyBhZGRGaWVsZExpbmVJdGVtcyhcbiAgICAgICAgcmVjb3JkOiBSZWNvcmQsXG4gICAgICAgIGZpZWxkOiBGaWVsZCxcbiAgICAgICAgbGFuZ3VhZ2U6IExhbmd1YWdlU3RvcmUsXG4gICAgICAgIGJ1aWxkTGluZUl0ZW1GdW5jdGlvbjogRnVuY3Rpb24sXG4gICAgKTogdm9pZCB7XG5cbiAgICAgICAgY29uc3QgZGVmaW5pdGlvbiA9IChmaWVsZCAmJiBmaWVsZC5kZWZpbml0aW9uKSB8fCB7fTtcbiAgICAgICAgY29uc3QgdHlwZSA9IChmaWVsZCAmJiBmaWVsZC50eXBlKSB8fCAnJztcbiAgICAgICAgY29uc3QgaXRlbXMgPSAoZmllbGQudmFsdWVPYmplY3RBcnJheSAmJiBmaWVsZC52YWx1ZU9iamVjdEFycmF5KSB8fCBbXTtcblxuICAgICAgICBpZiAodHlwZSAhPT0gJ2xpbmUtaXRlbXMnIHx8ICFpdGVtcy5sZW5ndGgpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IGl0ZW1EZWZpbml0aW9uOiBGaWVsZERlZmluaXRpb24gPSBkZWZpbml0aW9uLmxpbmVJdGVtcz8uZGVmaW5pdGlvbiB8fCB7fTtcbiAgICAgICAgZmllbGQuaXRlbXMgPSBbXTtcblxuICAgICAgICBpdGVtcy5mb3JFYWNoKGl0ZW0gPT4ge1xuICAgICAgICAgICAgdGhpcy5hZGRMaW5lSXRlbShpdGVtRGVmaW5pdGlvbiwgaXRlbSBhcyBSZWNvcmQsIGJ1aWxkTGluZUl0ZW1GdW5jdGlvbiwgbGFuZ3VhZ2UsIHJlY29yZCwgZmllbGQpO1xuICAgICAgICB9KTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBCdWlsZCBsaW5lIGl0ZW0gYW5kIGFuZCB0byByZWNvcmRcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gaXRlbURlZmluaXRpb25cbiAgICAgKiBAcGFyYW0ge29iamVjdCB9aXRlbVxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBidWlsZExpbmVJdGVtRnVuY3Rpb25cbiAgICAgKiBAcGFyYW0ge29iamVjdH0gbGFuZ3VhZ2VcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gcGFyZW50UmVjb3JkXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHBhcmVudEZpZWxkXG4gICAgICovXG4gICAgcHVibGljIGFkZExpbmVJdGVtKFxuICAgICAgICBpdGVtRGVmaW5pdGlvbjogRmllbGREZWZpbml0aW9uLFxuICAgICAgICBpdGVtOiBSZWNvcmQsXG4gICAgICAgIGJ1aWxkTGluZUl0ZW1GdW5jdGlvbjogRnVuY3Rpb24sXG4gICAgICAgIGxhbmd1YWdlOiBMYW5ndWFnZVN0b3JlLFxuICAgICAgICBwYXJlbnRSZWNvcmQ6IFJlY29yZCxcbiAgICAgICAgcGFyZW50RmllbGQ6IEZpZWxkXG4gICAgKSB7XG5cbiAgICAgICAgY29uc3QgaXRlbVZpZXdGaWVsZCA9IHtcbiAgICAgICAgICAgIG5hbWU6IGl0ZW1EZWZpbml0aW9uLm5hbWUsXG4gICAgICAgICAgICBsYWJlbDogaXRlbURlZmluaXRpb24udm5hbWUsXG4gICAgICAgICAgICB0eXBlOiBpdGVtRGVmaW5pdGlvbi50eXBlLFxuICAgICAgICAgICAgZmllbGREZWZpbml0aW9uOiBkZWVwQ2xvbmUoaXRlbURlZmluaXRpb24pXG4gICAgICAgIH07XG5cbiAgICAgICAgY29uc3QgaXRlbVJlY29yZCA9IHtcbiAgICAgICAgICAgIGlkOiBpdGVtLmlkIHx8ICcnLFxuICAgICAgICAgICAgdHlwZTogaXRlbS50eXBlIHx8ICcnLFxuICAgICAgICAgICAgbW9kdWxlOiBpdGVtLm1vZHVsZSB8fCAnJyxcbiAgICAgICAgICAgIGF0dHJpYnV0ZXM6IGl0ZW0uYXR0cmlidXRlcyB8fCB7fSxcbiAgICAgICAgICAgIGZpZWxkczoge30sXG4gICAgICAgICAgICBmb3JtR3JvdXA6IG5ldyBVbnR5cGVkRm9ybUdyb3VwKHt9KVxuICAgICAgICB9IGFzIFJlY29yZDtcblxuICAgICAgICBidWlsZExpbmVJdGVtRnVuY3Rpb24oaXRlbVJlY29yZCwgaXRlbVZpZXdGaWVsZCwgbGFuZ3VhZ2UpO1xuXG4gICAgICAgIHBhcmVudEZpZWxkLml0ZW1Gb3JtQXJyYXkucHVzaChpdGVtUmVjb3JkLmZvcm1Hcm91cCk7XG5cbiAgICAgICAgcGFyZW50RmllbGQuaXRlbXMucHVzaChpdGVtUmVjb3JkKTtcbiAgICB9XG59XG4iXX0=