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
import { FieldBuilder } from './field.builder';
import { ValidationManager } from '../validation/validation.manager';
import { DataTypeFormatter } from '../../formatters/data-type.formatter.service';
import { Injectable } from '@angular/core';
import isObjectLike from 'lodash-es/isObjectLike';
import { FieldObjectRegistry } from "./field-object-type.registry";
import * as i0 from "@angular/core";
import * as i1 from "../validation/validation.manager";
import * as i2 from "../../formatters/data-type.formatter.service";
import * as i3 from "./field-object-type.registry";
class AttributeBuilder extends FieldBuilder {
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
     * @param {function} buildAttributeFunction
     * @param {function} addAttributeFunction
     */
    addAttributes(record, fields, viewField, language, buildAttributeFunction, addAttributeFunction) {
        const fieldKeys = Object.keys(fields) || [];
        if (fieldKeys.length < 1) {
            return;
        }
        fieldKeys.forEach(key => {
            const field = fields[key];
            this.addFieldAttributes(record, field, language, buildAttributeFunction, addAttributeFunction);
        });
    }
    /**
     * Create and add attributes fields to field
     *
     * @param {object} record Record
     * @param {object} field Field
     * @param {object} language LanguageStore
     * @param {function} buildAttributeFunction
     * @param {function} addAttributeFunction
     */
    addFieldAttributes(record, field, language, buildAttributeFunction, addAttributeFunction) {
        const definition = (field && field.definition) || {};
        const attributes = definition.attributeFields || {};
        const attributeKeys = Object.keys(attributes);
        attributeKeys.forEach(key => {
            const attributeDefinition = attributes[key];
            if (!!field.attributes[key]) {
                return;
            }
            const attributeViewField = {
                name: attributeDefinition.name,
                label: attributeDefinition.vname,
                type: attributeDefinition.type,
                fieldDefinition: attributeDefinition
            };
            const attributeField = buildAttributeFunction(record, field, attributeViewField, language);
            addAttributeFunction(record, field, attributeDefinition.name, attributeField);
        });
    }
    /**
     * Build field
     *
     * @param {object} record Record
     * @param {object} parentField Field
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @returns {object} FieldAttribute
     */
    buildAttribute(record, parentField, viewField, language = null) {
        const definition = (viewField && viewField.fieldDefinition) || {};
        const { value, valueList, valueObject } = this.parseAttributeValue(viewField, definition, record, parentField);
        const { validators, asyncValidators } = this.getSaveValidators(record, viewField);
        const field = this.setupField(record.module, viewField, value, valueList, valueObject, record, definition, validators, asyncValidators, language);
        const fieldAttribute = field;
        fieldAttribute.valuePath = definition.valuePath;
        fieldAttribute.valueParent = definition.valueParent;
        fieldAttribute.source = 'attribute';
        fieldAttribute.parentKey = parentField.name;
        return fieldAttribute;
    }
    /**
     * Add attribute to record
     *
     * @param {object} record Record
     * @param {object} field Field
     * @param {string} name string
     * @param {object} attribute FieldAttribute
     */
    addAttributeToRecord(record, field, name, attribute) {
        if (!record || !name || !field || !attribute) {
            return;
        }
        field.attributes = field.attributes || {};
        field.attributes[name] = attribute;
        if (record.formGroup && attribute.formControl) {
            record.formGroup.addControl(name, attribute.formControl);
        }
    }
    /**
     * Parse attribute from field
     *
     * @param {object} viewField ViewFieldDefinition
     * @param {object} definition FieldDefinition
     * @param {object} record Record
     * @param {object} field Field
     * @returns {object} value object
     */
    parseAttributeValue(viewField, definition, record, field) {
        const type = (viewField && viewField.type) || '';
        const source = (definition && definition.source) || '';
        const rname = (definition && definition.rname) || 'name';
        const viewName = viewField.name || '';
        let value;
        let valueList = null;
        if (type === 'relate' && source === 'non-db' && rname !== '') {
            value = this.getParentValue(record, field, viewName, definition)[rname];
            const valueObject = this.getParentValue(record, field, viewName, definition);
            return { value, valueList, valueObject };
        }
        if (!viewName) {
            value = '';
        }
        else {
            value = this.getParentValue(record, field, viewName, definition);
        }
        value = this.getParentValue(record, field, viewName, definition);
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
    static { this.ɵfac = function AttributeBuilder_Factory(t) { return new (t || AttributeBuilder)(i0.ɵɵinject(i1.ValidationManager), i0.ɵɵinject(i2.DataTypeFormatter), i0.ɵɵinject(i3.FieldObjectRegistry)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: AttributeBuilder, factory: AttributeBuilder.ɵfac, providedIn: 'root' }); }
}
export { AttributeBuilder };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(AttributeBuilder, [{
        type: Injectable,
        args: [{
                providedIn: 'root'
            }]
    }], function () { return [{ type: i1.ValidationManager }, { type: i2.DataTypeFormatter }, { type: i3.FieldObjectRegistry }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYXR0cmlidXRlLmJ1aWxkZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvc2VydmljZXMvcmVjb3JkL2ZpZWxkL2F0dHJpYnV0ZS5idWlsZGVyLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0F3Qkc7QUFFSCxPQUFPLEVBQUMsWUFBWSxFQUFDLE1BQU0saUJBQWlCLENBQUM7QUFHN0MsT0FBTyxFQUFDLGlCQUFpQixFQUFDLE1BQU0sa0NBQWtDLENBQUM7QUFDbkUsT0FBTyxFQUFDLGlCQUFpQixFQUFDLE1BQU0sOENBQThDLENBQUM7QUFDL0UsT0FBTyxFQUFDLFVBQVUsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUN6QyxPQUFPLFlBQVksTUFBTSx3QkFBd0IsQ0FBQztBQUNsRCxPQUFPLEVBQUMsbUJBQW1CLEVBQUMsTUFBTSw4QkFBOEIsQ0FBQzs7Ozs7QUFFakUsTUFHYSxnQkFBaUIsU0FBUSxZQUFZO0lBRTlDLFlBQ2MsaUJBQW9DLEVBQ3BDLGFBQWdDLEVBQ2hDLGFBQWtDO1FBRTVDLEtBQUssQ0FBQyxpQkFBaUIsRUFBRSxhQUFhLEVBQUUsYUFBYSxDQUFDLENBQUM7UUFKN0Msc0JBQWlCLEdBQWpCLGlCQUFpQixDQUFtQjtRQUNwQyxrQkFBYSxHQUFiLGFBQWEsQ0FBbUI7UUFDaEMsa0JBQWEsR0FBYixhQUFhLENBQXFCO0lBR2hELENBQUM7SUFHRDs7Ozs7Ozs7O09BU0c7SUFDSSxhQUFhLENBQ2hCLE1BQWMsRUFDZCxNQUFnQixFQUNoQixTQUE4QixFQUM5QixRQUF1QixFQUN2QixzQkFBZ0MsRUFDaEMsb0JBQThCO1FBRTlCLE1BQU0sU0FBUyxHQUFHLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLElBQUksRUFBRSxDQUFDO1FBRzVDLElBQUksU0FBUyxDQUFDLE1BQU0sR0FBRyxDQUFDLEVBQUU7WUFDdEIsT0FBTztTQUNWO1FBRUQsU0FBUyxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBRTtZQUNwQixNQUFNLEtBQUssR0FBRyxNQUFNLENBQUMsR0FBRyxDQUFDLENBQUM7WUFDMUIsSUFBSSxDQUFDLGtCQUFrQixDQUNuQixNQUFNLEVBQ04sS0FBSyxFQUNMLFFBQVEsRUFDUixzQkFBc0IsRUFDdEIsb0JBQW9CLENBQ3ZCLENBQUE7UUFDTCxDQUFDLENBQUMsQ0FBQztJQUVQLENBQUM7SUFFRDs7Ozs7Ozs7T0FRRztJQUNJLGtCQUFrQixDQUNyQixNQUFjLEVBQ2QsS0FBWSxFQUNaLFFBQXVCLEVBQ3ZCLHNCQUFnQyxFQUNoQyxvQkFBOEI7UUFHOUIsTUFBTSxVQUFVLEdBQUcsQ0FBQyxLQUFLLElBQUksS0FBSyxDQUFDLFVBQVUsQ0FBQyxJQUFJLEVBQUUsQ0FBQztRQUNyRCxNQUFNLFVBQVUsR0FBRyxVQUFVLENBQUMsZUFBZSxJQUFJLEVBQUUsQ0FBQztRQUNwRCxNQUFNLGFBQWEsR0FBRyxNQUFNLENBQUMsSUFBSSxDQUFDLFVBQVUsQ0FBQyxDQUFDO1FBRTlDLGFBQWEsQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUU7WUFDeEIsTUFBTSxtQkFBbUIsR0FBRyxVQUFVLENBQUMsR0FBRyxDQUFDLENBQUM7WUFFNUMsSUFBSSxDQUFDLENBQUMsS0FBSyxDQUFDLFVBQVUsQ0FBQyxHQUFHLENBQUMsRUFBRTtnQkFDekIsT0FBTzthQUNWO1lBRUQsTUFBTSxrQkFBa0IsR0FBRztnQkFDdkIsSUFBSSxFQUFFLG1CQUFtQixDQUFDLElBQUk7Z0JBQzlCLEtBQUssRUFBRSxtQkFBbUIsQ0FBQyxLQUFLO2dCQUNoQyxJQUFJLEVBQUUsbUJBQW1CLENBQUMsSUFBSTtnQkFDOUIsZUFBZSxFQUFFLG1CQUFtQjthQUN2QyxDQUFDO1lBRUYsTUFBTSxjQUFjLEdBQUcsc0JBQXNCLENBQUMsTUFBTSxFQUFFLEtBQUssRUFBRSxrQkFBa0IsRUFBRSxRQUFRLENBQUMsQ0FBQztZQUMzRixvQkFBb0IsQ0FBQyxNQUFNLEVBQUUsS0FBSyxFQUFFLG1CQUFtQixDQUFDLElBQUksRUFBRSxjQUFjLENBQUMsQ0FBQztRQUNsRixDQUFDLENBQUMsQ0FBQztJQUNQLENBQUM7SUFFRDs7Ozs7Ozs7T0FRRztJQUNJLGNBQWMsQ0FBQyxNQUFjLEVBQUUsV0FBa0IsRUFBRSxTQUE4QixFQUFFLFdBQTBCLElBQUk7UUFFcEgsTUFBTSxVQUFVLEdBQUcsQ0FBQyxTQUFTLElBQUksU0FBUyxDQUFDLGVBQWUsQ0FBQyxJQUFJLEVBQXFCLENBQUM7UUFDckYsTUFBTSxFQUFDLEtBQUssRUFBRSxTQUFTLEVBQUUsV0FBVyxFQUFDLEdBQUcsSUFBSSxDQUFDLG1CQUFtQixDQUFDLFNBQVMsRUFBRSxVQUFVLEVBQUUsTUFBTSxFQUFFLFdBQVcsQ0FBQyxDQUFDO1FBQzdHLE1BQU0sRUFBQyxVQUFVLEVBQUUsZUFBZSxFQUFDLEdBQUcsSUFBSSxDQUFDLGlCQUFpQixDQUFDLE1BQU0sRUFBRSxTQUFTLENBQUMsQ0FBQztRQUVoRixNQUFNLEtBQUssR0FBRyxJQUFJLENBQUMsVUFBVSxDQUN6QixNQUFNLENBQUMsTUFBTSxFQUNiLFNBQVMsRUFDVCxLQUFLLEVBQ0wsU0FBUyxFQUNULFdBQVcsRUFDWCxNQUFNLEVBQ04sVUFBVSxFQUNWLFVBQVUsRUFDVixlQUFlLEVBQ2YsUUFBUSxDQUNYLENBQUM7UUFFRixNQUFNLGNBQWMsR0FBRyxLQUF1QixDQUFDO1FBQy9DLGNBQWMsQ0FBQyxTQUFTLEdBQUcsVUFBVSxDQUFDLFNBQVMsQ0FBQztRQUNoRCxjQUFjLENBQUMsV0FBVyxHQUFHLFVBQVUsQ0FBQyxXQUFXLENBQUM7UUFDcEQsY0FBYyxDQUFDLE1BQU0sR0FBRyxXQUFXLENBQUM7UUFDcEMsY0FBYyxDQUFDLFNBQVMsR0FBRyxXQUFXLENBQUMsSUFBSSxDQUFDO1FBRTVDLE9BQU8sY0FBYyxDQUFDO0lBQzFCLENBQUM7SUFFRDs7Ozs7OztPQU9HO0lBQ0ksb0JBQW9CLENBQUMsTUFBYyxFQUFFLEtBQVksRUFBRSxJQUFZLEVBQUUsU0FBeUI7UUFFN0YsSUFBSSxDQUFDLE1BQU0sSUFBSSxDQUFDLElBQUksSUFBSSxDQUFDLEtBQUssSUFBSSxDQUFDLFNBQVMsRUFBRTtZQUMxQyxPQUFPO1NBQ1Y7UUFFRCxLQUFLLENBQUMsVUFBVSxHQUFHLEtBQUssQ0FBQyxVQUFVLElBQUksRUFBRSxDQUFDO1FBRTFDLEtBQUssQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDLEdBQUcsU0FBUyxDQUFDO1FBRW5DLElBQUksTUFBTSxDQUFDLFNBQVMsSUFBSSxTQUFTLENBQUMsV0FBVyxFQUFFO1lBQzNDLE1BQU0sQ0FBQyxTQUFTLENBQUMsVUFBVSxDQUFDLElBQUksRUFBRSxTQUFTLENBQUMsV0FBVyxDQUFDLENBQUM7U0FDNUQ7SUFFTCxDQUFDO0lBRUQ7Ozs7Ozs7O09BUUc7SUFDTyxtQkFBbUIsQ0FDekIsU0FBOEIsRUFDOUIsVUFBMkIsRUFDM0IsTUFBYyxFQUNkLEtBQVk7UUFHWixNQUFNLElBQUksR0FBRyxDQUFDLFNBQVMsSUFBSSxTQUFTLENBQUMsSUFBSSxDQUFDLElBQUksRUFBRSxDQUFDO1FBQ2pELE1BQU0sTUFBTSxHQUFHLENBQUMsVUFBVSxJQUFJLFVBQVUsQ0FBQyxNQUFNLENBQUMsSUFBSSxFQUFFLENBQUM7UUFDdkQsTUFBTSxLQUFLLEdBQUcsQ0FBQyxVQUFVLElBQUksVUFBVSxDQUFDLEtBQUssQ0FBQyxJQUFJLE1BQU0sQ0FBQztRQUN6RCxNQUFNLFFBQVEsR0FBRyxTQUFTLENBQUMsSUFBSSxJQUFJLEVBQUUsQ0FBQztRQUN0QyxJQUFJLEtBQWEsQ0FBQztRQUNsQixJQUFJLFNBQVMsR0FBYSxJQUFJLENBQUM7UUFFL0IsSUFBSSxJQUFJLEtBQUssUUFBUSxJQUFJLE1BQU0sS0FBSyxRQUFRLElBQUksS0FBSyxLQUFLLEVBQUUsRUFBRTtZQUMxRCxLQUFLLEdBQUcsSUFBSSxDQUFDLGNBQWMsQ0FBQyxNQUFNLEVBQUUsS0FBSyxFQUFFLFFBQVEsRUFBRSxVQUFVLENBQUMsQ0FBQyxLQUFLLENBQUMsQ0FBQztZQUN4RSxNQUFNLFdBQVcsR0FBRyxJQUFJLENBQUMsY0FBYyxDQUFDLE1BQU0sRUFBRSxLQUFLLEVBQUUsUUFBUSxFQUFFLFVBQVUsQ0FBQyxDQUFDO1lBQzdFLE9BQU8sRUFBQyxLQUFLLEVBQUUsU0FBUyxFQUFFLFdBQVcsRUFBQyxDQUFDO1NBQzFDO1FBRUQsSUFBSSxDQUFDLFFBQVEsRUFBRTtZQUNYLEtBQUssR0FBRyxFQUFFLENBQUM7U0FDZDthQUFNO1lBQ0gsS0FBSyxHQUFHLElBQUksQ0FBQyxjQUFjLENBQUMsTUFBTSxFQUFFLEtBQUssRUFBRSxRQUFRLEVBQUUsVUFBVSxDQUFDLENBQUM7U0FDcEU7UUFFRCxLQUFLLEdBQUcsSUFBSSxDQUFDLGNBQWMsQ0FBQyxNQUFNLEVBQUUsS0FBSyxFQUFFLFFBQVEsRUFBRSxVQUFVLENBQUMsQ0FBQztRQUVqRSxJQUFJLEtBQUssQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLEVBQUU7WUFDdEIsT0FBTztnQkFDSCxLQUFLLEVBQUUsSUFBSTtnQkFDWCxTQUFTLEVBQUUsS0FBSztnQkFDaEIsV0FBVyxFQUFFLElBQUk7YUFDcEIsQ0FBQTtTQUNKO1FBRUQsSUFBSSxZQUFZLENBQUMsS0FBSyxDQUFDLEVBQUU7WUFDckIsT0FBTztnQkFDSCxLQUFLLEVBQUUsSUFBSTtnQkFDWCxTQUFTLEVBQUUsSUFBSTtnQkFDZixXQUFXLEVBQUUsS0FBSzthQUNyQixDQUFBO1NBQ0o7UUFFRCxPQUFPLEVBQUMsS0FBSyxFQUFFLFNBQVMsRUFBRSxJQUFJLEVBQUUsV0FBVyxFQUFFLElBQUksRUFBQyxDQUFDO0lBQ3ZELENBQUM7aUZBNU1RLGdCQUFnQjt1RUFBaEIsZ0JBQWdCLFdBQWhCLGdCQUFnQixtQkFGYixNQUFNOztTQUVULGdCQUFnQjt1RkFBaEIsZ0JBQWdCO2NBSDVCLFVBQVU7ZUFBQztnQkFDUixVQUFVLEVBQUUsTUFBTTthQUNyQiIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogU3VpdGVDUk0gaXMgYSBjdXN0b21lciByZWxhdGlvbnNoaXAgbWFuYWdlbWVudCBwcm9ncmFtIGRldmVsb3BlZCBieSBTYWxlc0FnaWxpdHkgTHRkLlxuICogQ29weXJpZ2h0IChDKSAyMDIxIFNhbGVzQWdpbGl0eSBMdGQuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGZyZWUgc29mdHdhcmU7IHlvdSBjYW4gcmVkaXN0cmlidXRlIGl0IGFuZC9vciBtb2RpZnkgaXQgdW5kZXJcbiAqIHRoZSB0ZXJtcyBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIHZlcnNpb24gMyBhcyBwdWJsaXNoZWQgYnkgdGhlXG4gKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4gKiB0byBTZWN0aW9uIDE1IGFzIHBlcm1pdHRlZCBpbiBTZWN0aW9uIDcoYSk6IEZPUiBBTlkgUEFSVCBPRiBUSEUgQ09WRVJFRCBXT1JLXG4gKiBJTiBXSElDSCBUSEUgQ09QWVJJR0hUIElTIE9XTkVEIEJZIFNBTEVTQUdJTElUWSwgU0FMRVNBR0lMSVRZIERJU0NMQUlNUyBUSEVcbiAqIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBkaXN0cmlidXRlZCBpbiB0aGUgaG9wZSB0aGF0IGl0IHdpbGwgYmUgdXNlZnVsLCBidXQgV0lUSE9VVFxuICogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiAqIEZPUiBBIFBBUlRJQ1VMQVIgUFVSUE9TRS4gU2VlIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgZm9yIG1vcmVcbiAqIGRldGFpbHMuXG4gKlxuICogWW91IHNob3VsZCBoYXZlIHJlY2VpdmVkIGEgY29weSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiBhbG9uZyB3aXRoIHRoaXMgcHJvZ3JhbS4gIElmIG5vdCwgc2VlIDxodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvPi5cbiAqXG4gKiBJbiBhY2NvcmRhbmNlIHdpdGggU2VjdGlvbiA3KGIpIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIHZlcnNpb24gMywgdGhlc2UgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IHJldGFpbiB0aGUgZGlzcGxheSBvZiB0aGVcbiAqIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4gKiBmZWFzaWJsZSBmb3IgdGVjaG5pY2FsIHJlYXNvbnMsIHRoZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgZGlzcGxheVxuICogdGhlIHdvcmRzIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIuXG4gKi9cblxuaW1wb3J0IHtGaWVsZEJ1aWxkZXJ9IGZyb20gJy4vZmllbGQuYnVpbGRlcic7XG5pbXBvcnQge0ZpZWxkLCBGaWVsZEF0dHJpYnV0ZSwgRmllbGREZWZpbml0aW9uLCBGaWVsZE1hcCwgUmVjb3JkLCBWaWV3RmllbGREZWZpbml0aW9ufSBmcm9tICdjb21tb24nO1xuaW1wb3J0IHtMYW5ndWFnZVN0b3JlfSBmcm9tICcuLi8uLi8uLi9zdG9yZS9sYW5ndWFnZS9sYW5ndWFnZS5zdG9yZSc7XG5pbXBvcnQge1ZhbGlkYXRpb25NYW5hZ2VyfSBmcm9tICcuLi92YWxpZGF0aW9uL3ZhbGlkYXRpb24ubWFuYWdlcic7XG5pbXBvcnQge0RhdGFUeXBlRm9ybWF0dGVyfSBmcm9tICcuLi8uLi9mb3JtYXR0ZXJzL2RhdGEtdHlwZS5mb3JtYXR0ZXIuc2VydmljZSc7XG5pbXBvcnQge0luamVjdGFibGV9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IGlzT2JqZWN0TGlrZSBmcm9tICdsb2Rhc2gtZXMvaXNPYmplY3RMaWtlJztcbmltcG9ydCB7RmllbGRPYmplY3RSZWdpc3RyeX0gZnJvbSBcIi4vZmllbGQtb2JqZWN0LXR5cGUucmVnaXN0cnlcIjtcblxuQEluamVjdGFibGUoe1xuICAgIHByb3ZpZGVkSW46ICdyb290J1xufSlcbmV4cG9ydCBjbGFzcyBBdHRyaWJ1dGVCdWlsZGVyIGV4dGVuZHMgRmllbGRCdWlsZGVyIHtcblxuICAgIGNvbnN0cnVjdG9yKFxuICAgICAgICBwcm90ZWN0ZWQgdmFsaWRhdGlvbk1hbmFnZXI6IFZhbGlkYXRpb25NYW5hZ2VyLFxuICAgICAgICBwcm90ZWN0ZWQgdHlwZUZvcm1hdHRlcjogRGF0YVR5cGVGb3JtYXR0ZXIsXG4gICAgICAgIHByb3RlY3RlZCBmaWVsZFJlZ2lzdHJ5OiBGaWVsZE9iamVjdFJlZ2lzdHJ5XG4gICAgKSB7XG4gICAgICAgIHN1cGVyKHZhbGlkYXRpb25NYW5hZ2VyLCB0eXBlRm9ybWF0dGVyLCBmaWVsZFJlZ2lzdHJ5KTtcbiAgICB9XG5cblxuICAgIC8qKlxuICAgICAqIENyZWF0ZSBhbmQgYWRkIGF0dHJpYnV0ZXMgZmllbGRzIHRvIGZpZWxkXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gcmVjb3JkIFJlY29yZFxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBmaWVsZHMgRmllbGRNYXBcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gdmlld0ZpZWxkIFZpZXdGaWVsZERlZmluaXRpb25cbiAgICAgKiBAcGFyYW0ge29iamVjdH0gbGFuZ3VhZ2UgTGFuZ3VhZ2VTdG9yZVxuICAgICAqIEBwYXJhbSB7ZnVuY3Rpb259IGJ1aWxkQXR0cmlidXRlRnVuY3Rpb25cbiAgICAgKiBAcGFyYW0ge2Z1bmN0aW9ufSBhZGRBdHRyaWJ1dGVGdW5jdGlvblxuICAgICAqL1xuICAgIHB1YmxpYyBhZGRBdHRyaWJ1dGVzKFxuICAgICAgICByZWNvcmQ6IFJlY29yZCxcbiAgICAgICAgZmllbGRzOiBGaWVsZE1hcCxcbiAgICAgICAgdmlld0ZpZWxkOiBWaWV3RmllbGREZWZpbml0aW9uLFxuICAgICAgICBsYW5ndWFnZTogTGFuZ3VhZ2VTdG9yZSxcbiAgICAgICAgYnVpbGRBdHRyaWJ1dGVGdW5jdGlvbjogRnVuY3Rpb24sXG4gICAgICAgIGFkZEF0dHJpYnV0ZUZ1bmN0aW9uOiBGdW5jdGlvbixcbiAgICApOiB2b2lkIHtcbiAgICAgICAgY29uc3QgZmllbGRLZXlzID0gT2JqZWN0LmtleXMoZmllbGRzKSB8fCBbXTtcblxuXG4gICAgICAgIGlmIChmaWVsZEtleXMubGVuZ3RoIDwgMSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgZmllbGRLZXlzLmZvckVhY2goa2V5ID0+IHtcbiAgICAgICAgICAgIGNvbnN0IGZpZWxkID0gZmllbGRzW2tleV07XG4gICAgICAgICAgICB0aGlzLmFkZEZpZWxkQXR0cmlidXRlcyhcbiAgICAgICAgICAgICAgICByZWNvcmQsXG4gICAgICAgICAgICAgICAgZmllbGQsXG4gICAgICAgICAgICAgICAgbGFuZ3VhZ2UsXG4gICAgICAgICAgICAgICAgYnVpbGRBdHRyaWJ1dGVGdW5jdGlvbixcbiAgICAgICAgICAgICAgICBhZGRBdHRyaWJ1dGVGdW5jdGlvblxuICAgICAgICAgICAgKVxuICAgICAgICB9KTtcblxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENyZWF0ZSBhbmQgYWRkIGF0dHJpYnV0ZXMgZmllbGRzIHRvIGZpZWxkXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gcmVjb3JkIFJlY29yZFxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBmaWVsZCBGaWVsZFxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBsYW5ndWFnZSBMYW5ndWFnZVN0b3JlXG4gICAgICogQHBhcmFtIHtmdW5jdGlvbn0gYnVpbGRBdHRyaWJ1dGVGdW5jdGlvblxuICAgICAqIEBwYXJhbSB7ZnVuY3Rpb259IGFkZEF0dHJpYnV0ZUZ1bmN0aW9uXG4gICAgICovXG4gICAgcHVibGljIGFkZEZpZWxkQXR0cmlidXRlcyhcbiAgICAgICAgcmVjb3JkOiBSZWNvcmQsXG4gICAgICAgIGZpZWxkOiBGaWVsZCxcbiAgICAgICAgbGFuZ3VhZ2U6IExhbmd1YWdlU3RvcmUsXG4gICAgICAgIGJ1aWxkQXR0cmlidXRlRnVuY3Rpb246IEZ1bmN0aW9uLFxuICAgICAgICBhZGRBdHRyaWJ1dGVGdW5jdGlvbjogRnVuY3Rpb25cbiAgICApOiB2b2lkIHtcblxuICAgICAgICBjb25zdCBkZWZpbml0aW9uID0gKGZpZWxkICYmIGZpZWxkLmRlZmluaXRpb24pIHx8IHt9O1xuICAgICAgICBjb25zdCBhdHRyaWJ1dGVzID0gZGVmaW5pdGlvbi5hdHRyaWJ1dGVGaWVsZHMgfHwge307XG4gICAgICAgIGNvbnN0IGF0dHJpYnV0ZUtleXMgPSBPYmplY3Qua2V5cyhhdHRyaWJ1dGVzKTtcblxuICAgICAgICBhdHRyaWJ1dGVLZXlzLmZvckVhY2goa2V5ID0+IHtcbiAgICAgICAgICAgIGNvbnN0IGF0dHJpYnV0ZURlZmluaXRpb24gPSBhdHRyaWJ1dGVzW2tleV07XG5cbiAgICAgICAgICAgIGlmICghIWZpZWxkLmF0dHJpYnV0ZXNba2V5XSkge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgY29uc3QgYXR0cmlidXRlVmlld0ZpZWxkID0ge1xuICAgICAgICAgICAgICAgIG5hbWU6IGF0dHJpYnV0ZURlZmluaXRpb24ubmFtZSxcbiAgICAgICAgICAgICAgICBsYWJlbDogYXR0cmlidXRlRGVmaW5pdGlvbi52bmFtZSxcbiAgICAgICAgICAgICAgICB0eXBlOiBhdHRyaWJ1dGVEZWZpbml0aW9uLnR5cGUsXG4gICAgICAgICAgICAgICAgZmllbGREZWZpbml0aW9uOiBhdHRyaWJ1dGVEZWZpbml0aW9uXG4gICAgICAgICAgICB9O1xuXG4gICAgICAgICAgICBjb25zdCBhdHRyaWJ1dGVGaWVsZCA9IGJ1aWxkQXR0cmlidXRlRnVuY3Rpb24ocmVjb3JkLCBmaWVsZCwgYXR0cmlidXRlVmlld0ZpZWxkLCBsYW5ndWFnZSk7XG4gICAgICAgICAgICBhZGRBdHRyaWJ1dGVGdW5jdGlvbihyZWNvcmQsIGZpZWxkLCBhdHRyaWJ1dGVEZWZpbml0aW9uLm5hbWUsIGF0dHJpYnV0ZUZpZWxkKTtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQnVpbGQgZmllbGRcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSByZWNvcmQgUmVjb3JkXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHBhcmVudEZpZWxkIEZpZWxkXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHZpZXdGaWVsZCBWaWV3RmllbGREZWZpbml0aW9uXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGxhbmd1YWdlIExhbmd1YWdlU3RvcmVcbiAgICAgKiBAcmV0dXJucyB7b2JqZWN0fSBGaWVsZEF0dHJpYnV0ZVxuICAgICAqL1xuICAgIHB1YmxpYyBidWlsZEF0dHJpYnV0ZShyZWNvcmQ6IFJlY29yZCwgcGFyZW50RmllbGQ6IEZpZWxkLCB2aWV3RmllbGQ6IFZpZXdGaWVsZERlZmluaXRpb24sIGxhbmd1YWdlOiBMYW5ndWFnZVN0b3JlID0gbnVsbCk6IEZpZWxkQXR0cmlidXRlIHtcblxuICAgICAgICBjb25zdCBkZWZpbml0aW9uID0gKHZpZXdGaWVsZCAmJiB2aWV3RmllbGQuZmllbGREZWZpbml0aW9uKSB8fCB7fSBhcyBGaWVsZERlZmluaXRpb247XG4gICAgICAgIGNvbnN0IHt2YWx1ZSwgdmFsdWVMaXN0LCB2YWx1ZU9iamVjdH0gPSB0aGlzLnBhcnNlQXR0cmlidXRlVmFsdWUodmlld0ZpZWxkLCBkZWZpbml0aW9uLCByZWNvcmQsIHBhcmVudEZpZWxkKTtcbiAgICAgICAgY29uc3Qge3ZhbGlkYXRvcnMsIGFzeW5jVmFsaWRhdG9yc30gPSB0aGlzLmdldFNhdmVWYWxpZGF0b3JzKHJlY29yZCwgdmlld0ZpZWxkKTtcblxuICAgICAgICBjb25zdCBmaWVsZCA9IHRoaXMuc2V0dXBGaWVsZChcbiAgICAgICAgICAgIHJlY29yZC5tb2R1bGUsXG4gICAgICAgICAgICB2aWV3RmllbGQsXG4gICAgICAgICAgICB2YWx1ZSxcbiAgICAgICAgICAgIHZhbHVlTGlzdCxcbiAgICAgICAgICAgIHZhbHVlT2JqZWN0LFxuICAgICAgICAgICAgcmVjb3JkLFxuICAgICAgICAgICAgZGVmaW5pdGlvbixcbiAgICAgICAgICAgIHZhbGlkYXRvcnMsXG4gICAgICAgICAgICBhc3luY1ZhbGlkYXRvcnMsXG4gICAgICAgICAgICBsYW5ndWFnZVxuICAgICAgICApO1xuXG4gICAgICAgIGNvbnN0IGZpZWxkQXR0cmlidXRlID0gZmllbGQgYXMgRmllbGRBdHRyaWJ1dGU7XG4gICAgICAgIGZpZWxkQXR0cmlidXRlLnZhbHVlUGF0aCA9IGRlZmluaXRpb24udmFsdWVQYXRoO1xuICAgICAgICBmaWVsZEF0dHJpYnV0ZS52YWx1ZVBhcmVudCA9IGRlZmluaXRpb24udmFsdWVQYXJlbnQ7XG4gICAgICAgIGZpZWxkQXR0cmlidXRlLnNvdXJjZSA9ICdhdHRyaWJ1dGUnO1xuICAgICAgICBmaWVsZEF0dHJpYnV0ZS5wYXJlbnRLZXkgPSBwYXJlbnRGaWVsZC5uYW1lO1xuXG4gICAgICAgIHJldHVybiBmaWVsZEF0dHJpYnV0ZTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBBZGQgYXR0cmlidXRlIHRvIHJlY29yZFxuICAgICAqXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHJlY29yZCBSZWNvcmRcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gZmllbGQgRmllbGRcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gbmFtZSBzdHJpbmdcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gYXR0cmlidXRlIEZpZWxkQXR0cmlidXRlXG4gICAgICovXG4gICAgcHVibGljIGFkZEF0dHJpYnV0ZVRvUmVjb3JkKHJlY29yZDogUmVjb3JkLCBmaWVsZDogRmllbGQsIG5hbWU6IHN0cmluZywgYXR0cmlidXRlOiBGaWVsZEF0dHJpYnV0ZSk6IHZvaWQge1xuXG4gICAgICAgIGlmICghcmVjb3JkIHx8ICFuYW1lIHx8ICFmaWVsZCB8fCAhYXR0cmlidXRlKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBmaWVsZC5hdHRyaWJ1dGVzID0gZmllbGQuYXR0cmlidXRlcyB8fCB7fTtcblxuICAgICAgICBmaWVsZC5hdHRyaWJ1dGVzW25hbWVdID0gYXR0cmlidXRlO1xuXG4gICAgICAgIGlmIChyZWNvcmQuZm9ybUdyb3VwICYmIGF0dHJpYnV0ZS5mb3JtQ29udHJvbCkge1xuICAgICAgICAgICAgcmVjb3JkLmZvcm1Hcm91cC5hZGRDb250cm9sKG5hbWUsIGF0dHJpYnV0ZS5mb3JtQ29udHJvbCk7XG4gICAgICAgIH1cblxuICAgIH1cblxuICAgIC8qKlxuICAgICAqIFBhcnNlIGF0dHJpYnV0ZSBmcm9tIGZpZWxkXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gdmlld0ZpZWxkIFZpZXdGaWVsZERlZmluaXRpb25cbiAgICAgKiBAcGFyYW0ge29iamVjdH0gZGVmaW5pdGlvbiBGaWVsZERlZmluaXRpb25cbiAgICAgKiBAcGFyYW0ge29iamVjdH0gcmVjb3JkIFJlY29yZFxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBmaWVsZCBGaWVsZFxuICAgICAqIEByZXR1cm5zIHtvYmplY3R9IHZhbHVlIG9iamVjdFxuICAgICAqL1xuICAgIHByb3RlY3RlZCBwYXJzZUF0dHJpYnV0ZVZhbHVlKFxuICAgICAgICB2aWV3RmllbGQ6IFZpZXdGaWVsZERlZmluaXRpb24sXG4gICAgICAgIGRlZmluaXRpb246IEZpZWxkRGVmaW5pdGlvbixcbiAgICAgICAgcmVjb3JkOiBSZWNvcmQsXG4gICAgICAgIGZpZWxkOiBGaWVsZFxuICAgICk6IHsgdmFsdWU6IHN0cmluZzsgdmFsdWVMaXN0OiBzdHJpbmdbXTsgdmFsdWVPYmplY3Q/OiBhbnkgfSB7XG5cbiAgICAgICAgY29uc3QgdHlwZSA9ICh2aWV3RmllbGQgJiYgdmlld0ZpZWxkLnR5cGUpIHx8ICcnO1xuICAgICAgICBjb25zdCBzb3VyY2UgPSAoZGVmaW5pdGlvbiAmJiBkZWZpbml0aW9uLnNvdXJjZSkgfHwgJyc7XG4gICAgICAgIGNvbnN0IHJuYW1lID0gKGRlZmluaXRpb24gJiYgZGVmaW5pdGlvbi5ybmFtZSkgfHwgJ25hbWUnO1xuICAgICAgICBjb25zdCB2aWV3TmFtZSA9IHZpZXdGaWVsZC5uYW1lIHx8ICcnO1xuICAgICAgICBsZXQgdmFsdWU6IHN0cmluZztcbiAgICAgICAgbGV0IHZhbHVlTGlzdDogc3RyaW5nW10gPSBudWxsO1xuXG4gICAgICAgIGlmICh0eXBlID09PSAncmVsYXRlJyAmJiBzb3VyY2UgPT09ICdub24tZGInICYmIHJuYW1lICE9PSAnJykge1xuICAgICAgICAgICAgdmFsdWUgPSB0aGlzLmdldFBhcmVudFZhbHVlKHJlY29yZCwgZmllbGQsIHZpZXdOYW1lLCBkZWZpbml0aW9uKVtybmFtZV07XG4gICAgICAgICAgICBjb25zdCB2YWx1ZU9iamVjdCA9IHRoaXMuZ2V0UGFyZW50VmFsdWUocmVjb3JkLCBmaWVsZCwgdmlld05hbWUsIGRlZmluaXRpb24pO1xuICAgICAgICAgICAgcmV0dXJuIHt2YWx1ZSwgdmFsdWVMaXN0LCB2YWx1ZU9iamVjdH07XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIXZpZXdOYW1lKSB7XG4gICAgICAgICAgICB2YWx1ZSA9ICcnO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgdmFsdWUgPSB0aGlzLmdldFBhcmVudFZhbHVlKHJlY29yZCwgZmllbGQsIHZpZXdOYW1lLCBkZWZpbml0aW9uKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHZhbHVlID0gdGhpcy5nZXRQYXJlbnRWYWx1ZShyZWNvcmQsIGZpZWxkLCB2aWV3TmFtZSwgZGVmaW5pdGlvbik7XG5cbiAgICAgICAgaWYgKEFycmF5LmlzQXJyYXkodmFsdWUpKSB7XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIHZhbHVlOiBudWxsLFxuICAgICAgICAgICAgICAgIHZhbHVlTGlzdDogdmFsdWUsXG4gICAgICAgICAgICAgICAgdmFsdWVPYmplY3Q6IG51bGxcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChpc09iamVjdExpa2UodmFsdWUpKSB7XG4gICAgICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgICAgIHZhbHVlOiBudWxsLFxuICAgICAgICAgICAgICAgIHZhbHVlTGlzdDogbnVsbCxcbiAgICAgICAgICAgICAgICB2YWx1ZU9iamVjdDogdmFsdWVcbiAgICAgICAgICAgIH1cbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiB7dmFsdWUsIHZhbHVlTGlzdDogbnVsbCwgdmFsdWVPYmplY3Q6IG51bGx9O1xuICAgIH1cblxufVxuIl19