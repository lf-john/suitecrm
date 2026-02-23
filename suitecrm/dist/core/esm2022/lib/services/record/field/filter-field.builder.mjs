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
import { Injectable } from '@angular/core';
import { ValidationManager } from '../validation/validation.manager';
import { DataTypeFormatter } from '../../formatters/data-type.formatter.service';
import { deepClone } from 'common';
import { FieldObjectRegistry } from "./field-object-type.registry";
import * as i0 from "@angular/core";
import * as i1 from "../validation/validation.manager";
import * as i2 from "../../formatters/data-type.formatter.service";
import * as i3 from "./field-object-type.registry";
class FilterFieldBuilder extends FieldBuilder {
    constructor(validationManager, typeFormatter, fieldRegistry) {
        super(validationManager, typeFormatter, fieldRegistry);
        this.validationManager = validationManager;
        this.typeFormatter = typeFormatter;
        this.fieldRegistry = fieldRegistry;
    }
    /**
     * Build filter field
     *
     * @param {object} savedFilter SavedFilter
     * @param {object} viewField ViewFieldDefinition
     * @param {object} language LanguageStore
     * @returns {object} Field
     */
    buildFilterField(savedFilter, viewField, language = null) {
        const definition = (viewField && viewField.fieldDefinition) || {};
        const { validators, asyncValidators } = this.getFilterValidators(savedFilter, viewField);
        const field = this.setupField(savedFilter.searchModule, viewField, null, null, null, savedFilter, definition, validators, asyncValidators, language);
        field.criteria = this.initFieldFilter(savedFilter.criteria, viewField, field);
        return field;
    }
    /**
     * Get Filter Validators for given field definition
     *
     * @param {object} record  Record
     * @param {object} viewField ViewFieldDefinition
     * @returns {object} validator map
     */
    getFilterValidators(record, viewField) {
        const validators = this.validationManager.getFilterValidations(record.searchModule, viewField, record);
        const asyncValidators = [];
        return { validators, asyncValidators };
    }
    /**
     * Init filter fields
     *
     * @param {object} searchCriteria to use
     * @param {object} viewField to init
     * @param {object} field to init
     * @returns {object} SearchCriteriaFieldFilter
     */
    initFieldFilter(searchCriteria, viewField, field) {
        let fieldCriteria;
        const fieldName = viewField.name;
        let fieldType = viewField.type;
        if (fieldType === 'composite') {
            fieldType = field.definition.type;
        }
        if (searchCriteria.filters[fieldName] && searchCriteria.filters[fieldName].fieldType) {
            fieldCriteria = deepClone(searchCriteria.filters[fieldName]);
        }
        else {
            fieldCriteria = {
                field: fieldName,
                fieldType,
                operator: '',
                values: []
            };
        }
        this.mapEnumEmptyOption(fieldCriteria, field);
        this.mapRelateFields(fieldCriteria, field, searchCriteria);
        return fieldCriteria;
    }
    mapEnumEmptyOption(fieldCriteria, field) {
        if (!['multienum', 'enum'].includes(fieldCriteria.fieldType)) {
            return;
        }
        let emptyOption = this.getEmptyOption(field);
        if (!emptyOption) {
            return;
        }
        if (!fieldCriteria.values || !fieldCriteria.values.length) {
            return;
        }
        fieldCriteria.values = fieldCriteria.values.map(value => {
            if (value !== '') {
                return value;
            }
            return '__SuiteCRMEmptyString__';
        });
    }
    mapRelateFields(fieldCriteria, field, searchCriteria) {
        if (!['relate'].includes(fieldCriteria.fieldType)) {
            return;
        }
        if (!fieldCriteria.values || !fieldCriteria.values.length) {
            return;
        }
        const idFieldName = (field && field.definition && field.definition.id_name) || '';
        const relateFieldName = (field && field.definition && field.definition.rname) || 'name';
        const idValues = searchCriteria?.filters[idFieldName]?.values ?? [];
        fieldCriteria.valueObjectArray = fieldCriteria.valueObjectArray ?? [];
        const relateFieldMap = {};
        if (fieldCriteria.valueObjectArray.length) {
            fieldCriteria.valueObjectArray.forEach(value => {
                relateFieldMap[value.id] = value;
            });
        }
        for (let i = 0; i < fieldCriteria.values.length; i++) {
            let value = fieldCriteria.values[i];
            const relateValue = {};
            relateValue[relateFieldName] = value;
            relateValue['id'] = idValues[i] ?? '';
            if (!relateFieldMap[relateValue['id']]) {
                relateFieldMap[relateValue['id']] = relateValue;
                fieldCriteria.valueObjectArray.push(relateValue);
            }
        }
    }
    getEmptyOption(field) {
        let emptyOption = null;
        const extraOptions = field?.definition?.metadata?.extraOptions ?? [];
        extraOptions.forEach(option => {
            if (option.value === '__SuiteCRMEmptyString__') {
                emptyOption = option;
            }
        });
        return emptyOption;
    }
    /**
     * Is criteria field initialized in record
     *
     * @param {object} record Record
     * @param {string} fieldName field
     * @returns {boolean} isInitialized
     */
    isCriteriaFieldInitialized(record, fieldName) {
        const criteriaField = record.criteriaFields[fieldName];
        return !!criteriaField && !criteriaField.vardefBased;
    }
    /**
     * Add field to SavedFilter
     *
     * @param {object} savedFilter SavedFilter
     * @param {string} name string
     * @param {object} field Field
     */
    addToSavedFilter(savedFilter, name, field) {
        if (!savedFilter || !name || !field) {
            return;
        }
        if (!savedFilter.criteriaFields) {
            savedFilter.criteriaFields = {};
        }
        savedFilter.criteriaFields[name] = field;
        if (!savedFilter.criteria.filters) {
            savedFilter.criteria.filters = {};
        }
        savedFilter.criteria.filters[name] = field.criteria;
        if (savedFilter.criteriaFormGroup && field.formControl) {
            savedFilter.criteriaFormGroup.addControl(name, field.formControl);
        }
    }
    static { this.ɵfac = function FilterFieldBuilder_Factory(t) { return new (t || FilterFieldBuilder)(i0.ɵɵinject(i1.ValidationManager), i0.ɵɵinject(i2.DataTypeFormatter), i0.ɵɵinject(i3.FieldObjectRegistry)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: FilterFieldBuilder, factory: FilterFieldBuilder.ɵfac, providedIn: 'root' }); }
}
export { FilterFieldBuilder };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(FilterFieldBuilder, [{
        type: Injectable,
        args: [{
                providedIn: 'root'
            }]
    }], function () { return [{ type: i1.ValidationManager }, { type: i2.DataTypeFormatter }, { type: i3.FieldObjectRegistry }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZmlsdGVyLWZpZWxkLmJ1aWxkZXIuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvc2VydmljZXMvcmVjb3JkL2ZpZWxkL2ZpbHRlci1maWVsZC5idWlsZGVyLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0F3Qkc7QUFFSCxPQUFPLEVBQUMsWUFBWSxFQUFDLE1BQU0saUJBQWlCLENBQUM7QUFDN0MsT0FBTyxFQUFDLFVBQVUsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUN6QyxPQUFPLEVBQUMsaUJBQWlCLEVBQUMsTUFBTSxrQ0FBa0MsQ0FBQztBQUNuRSxPQUFPLEVBQUMsaUJBQWlCLEVBQUMsTUFBTSw4Q0FBOEMsQ0FBQztBQUUvRSxPQUFPLEVBQ0gsU0FBUyxFQU9aLE1BQU0sUUFBUSxDQUFDO0FBR2hCLE9BQU8sRUFBQyxtQkFBbUIsRUFBQyxNQUFNLDhCQUE4QixDQUFDOzs7OztBQUVqRSxNQUdhLGtCQUFtQixTQUFRLFlBQVk7SUFFaEQsWUFDYyxpQkFBb0MsRUFDcEMsYUFBZ0MsRUFDaEMsYUFBa0M7UUFFNUMsS0FBSyxDQUFDLGlCQUFpQixFQUFFLGFBQWEsRUFBRSxhQUFhLENBQUMsQ0FBQztRQUo3QyxzQkFBaUIsR0FBakIsaUJBQWlCLENBQW1CO1FBQ3BDLGtCQUFhLEdBQWIsYUFBYSxDQUFtQjtRQUNoQyxrQkFBYSxHQUFiLGFBQWEsQ0FBcUI7SUFHaEQsQ0FBQztJQUVEOzs7Ozs7O09BT0c7SUFDSSxnQkFBZ0IsQ0FBQyxXQUF3QixFQUFFLFNBQThCLEVBQUUsV0FBMEIsSUFBSTtRQUU1RyxNQUFNLFVBQVUsR0FBRyxDQUFDLFNBQVMsSUFBSSxTQUFTLENBQUMsZUFBZSxDQUFDLElBQUksRUFBcUIsQ0FBQztRQUNyRixNQUFNLEVBQUMsVUFBVSxFQUFFLGVBQWUsRUFBQyxHQUFHLElBQUksQ0FBQyxtQkFBbUIsQ0FBQyxXQUFXLEVBQUUsU0FBUyxDQUFDLENBQUM7UUFFdkYsTUFBTSxLQUFLLEdBQUcsSUFBSSxDQUFDLFVBQVUsQ0FDekIsV0FBVyxDQUFDLFlBQVksRUFDeEIsU0FBUyxFQUNULElBQUksRUFDSixJQUFJLEVBQ0osSUFBSSxFQUNKLFdBQVcsRUFDWCxVQUFVLEVBQ1YsVUFBVSxFQUNWLGVBQWUsRUFDZixRQUFRLENBQ1gsQ0FBQztRQUNGLEtBQUssQ0FBQyxRQUFRLEdBQUcsSUFBSSxDQUFDLGVBQWUsQ0FBQyxXQUFXLENBQUMsUUFBUSxFQUFFLFNBQVMsRUFBRSxLQUFLLENBQUMsQ0FBQztRQUM5RSxPQUFPLEtBQUssQ0FBQztJQUNqQixDQUFDO0lBRUQ7Ozs7OztPQU1HO0lBQ0ksbUJBQW1CLENBQ3RCLE1BQW1CLEVBQ25CLFNBQThCO1FBRzlCLE1BQU0sVUFBVSxHQUFHLElBQUksQ0FBQyxpQkFBaUIsQ0FBQyxvQkFBb0IsQ0FBQyxNQUFNLENBQUMsWUFBWSxFQUFFLFNBQVMsRUFBRSxNQUFNLENBQUMsQ0FBQztRQUN2RyxNQUFNLGVBQWUsR0FBdUIsRUFBRSxDQUFDO1FBRS9DLE9BQU8sRUFBQyxVQUFVLEVBQUUsZUFBZSxFQUFDLENBQUM7SUFDekMsQ0FBQztJQUVEOzs7Ozs7O09BT0c7SUFDSSxlQUFlLENBQUMsY0FBOEIsRUFBRSxTQUE4QixFQUFFLEtBQVk7UUFDL0YsSUFBSSxhQUF3QyxDQUFDO1FBRTdDLE1BQU0sU0FBUyxHQUFHLFNBQVMsQ0FBQyxJQUFJLENBQUM7UUFDakMsSUFBSSxTQUFTLEdBQUcsU0FBUyxDQUFDLElBQUksQ0FBQztRQUUvQixJQUFJLFNBQVMsS0FBSyxXQUFXLEVBQUU7WUFDM0IsU0FBUyxHQUFHLEtBQUssQ0FBQyxVQUFVLENBQUMsSUFBSSxDQUFDO1NBQ3JDO1FBRUQsSUFBSSxjQUFjLENBQUMsT0FBTyxDQUFDLFNBQVMsQ0FBQyxJQUFJLGNBQWMsQ0FBQyxPQUFPLENBQUMsU0FBUyxDQUFDLENBQUMsU0FBUyxFQUFFO1lBQ2xGLGFBQWEsR0FBRyxTQUFTLENBQUMsY0FBYyxDQUFDLE9BQU8sQ0FBQyxTQUFTLENBQUMsQ0FBQyxDQUFDO1NBQ2hFO2FBQU07WUFDSCxhQUFhLEdBQUc7Z0JBQ1osS0FBSyxFQUFFLFNBQVM7Z0JBQ2hCLFNBQVM7Z0JBQ1QsUUFBUSxFQUFFLEVBQUU7Z0JBQ1osTUFBTSxFQUFFLEVBQUU7YUFDYixDQUFDO1NBQ0w7UUFFRCxJQUFJLENBQUMsa0JBQWtCLENBQUMsYUFBYSxFQUFFLEtBQUssQ0FBQyxDQUFDO1FBRTlDLElBQUksQ0FBQyxlQUFlLENBQUMsYUFBYSxFQUFFLEtBQUssRUFBRSxjQUFjLENBQUMsQ0FBQztRQUUzRCxPQUFPLGFBQWEsQ0FBQztJQUN6QixDQUFDO0lBRVMsa0JBQWtCLENBQUMsYUFBd0MsRUFBRSxLQUFZO1FBQy9FLElBQUksQ0FBQyxDQUFDLFdBQVcsRUFBRSxNQUFNLENBQUMsQ0FBQyxRQUFRLENBQUMsYUFBYSxDQUFDLFNBQVMsQ0FBQyxFQUFFO1lBQzFELE9BQU87U0FDVjtRQUVELElBQUksV0FBVyxHQUFHLElBQUksQ0FBQyxjQUFjLENBQUMsS0FBSyxDQUFDLENBQUM7UUFFN0MsSUFBSSxDQUFDLFdBQVcsRUFBRTtZQUNkLE9BQU87U0FDVjtRQUVELElBQUksQ0FBQyxhQUFhLENBQUMsTUFBTSxJQUFJLENBQUMsYUFBYSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUU7WUFDdkQsT0FBTztTQUNWO1FBRUQsYUFBYSxDQUFDLE1BQU0sR0FBRyxhQUFhLENBQUMsTUFBTSxDQUFDLEdBQUcsQ0FBQyxLQUFLLENBQUMsRUFBRTtZQUNwRCxJQUFJLEtBQUssS0FBSyxFQUFFLEVBQUU7Z0JBQ2QsT0FBTyxLQUFLLENBQUM7YUFDaEI7WUFFRCxPQUFPLHlCQUF5QixDQUFDO1FBQ3JDLENBQUMsQ0FBQyxDQUFDO0lBQ1AsQ0FBQztJQUVTLGVBQWUsQ0FBQyxhQUF3QyxFQUFFLEtBQVksRUFBRSxjQUE4QjtRQUM1RyxJQUFJLENBQUMsQ0FBQyxRQUFRLENBQUMsQ0FBQyxRQUFRLENBQUMsYUFBYSxDQUFDLFNBQVMsQ0FBQyxFQUFFO1lBQy9DLE9BQU87U0FDVjtRQUVELElBQUksQ0FBQyxhQUFhLENBQUMsTUFBTSxJQUFJLENBQUMsYUFBYSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUU7WUFDdkQsT0FBTztTQUNWO1FBRUQsTUFBTSxXQUFXLEdBQUcsQ0FBQyxLQUFLLElBQUksS0FBSyxDQUFDLFVBQVUsSUFBSSxLQUFLLENBQUMsVUFBVSxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUUsQ0FBQztRQUNsRixNQUFNLGVBQWUsR0FBRyxDQUFDLEtBQUssSUFBSSxLQUFLLENBQUMsVUFBVSxJQUFJLEtBQUssQ0FBQyxVQUFVLENBQUMsS0FBSyxDQUFDLElBQUksTUFBTSxDQUFDO1FBQ3hGLE1BQU0sUUFBUSxHQUFHLGNBQWMsRUFBRSxPQUFPLENBQUMsV0FBVyxDQUFDLEVBQUUsTUFBTSxJQUFJLEVBQUUsQ0FBQztRQUVwRSxhQUFhLENBQUMsZ0JBQWdCLEdBQUcsYUFBYSxDQUFDLGdCQUFnQixJQUFJLEVBQUUsQ0FBQztRQUN0RSxNQUFNLGNBQWMsR0FBRyxFQUFFLENBQUM7UUFDMUIsSUFBSSxhQUFhLENBQUMsZ0JBQWdCLENBQUMsTUFBTSxFQUFFO1lBQ3ZDLGFBQWEsQ0FBQyxnQkFBZ0IsQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLEVBQUU7Z0JBQzNDLGNBQWMsQ0FBQyxLQUFLLENBQUMsRUFBRSxDQUFDLEdBQUcsS0FBSyxDQUFDO1lBQ3JDLENBQUMsQ0FBQyxDQUFBO1NBQ0w7UUFFRCxLQUFLLElBQUksQ0FBQyxHQUFHLENBQUMsRUFBRSxDQUFDLEdBQUcsYUFBYSxDQUFDLE1BQU0sQ0FBQyxNQUFNLEVBQUUsQ0FBQyxFQUFFLEVBQUU7WUFDbEQsSUFBSSxLQUFLLEdBQUcsYUFBYSxDQUFDLE1BQU0sQ0FBQyxDQUFDLENBQUMsQ0FBQztZQUVwQyxNQUFNLFdBQVcsR0FBRyxFQUFFLENBQUM7WUFDdkIsV0FBVyxDQUFDLGVBQWUsQ0FBQyxHQUFHLEtBQUssQ0FBQztZQUNyQyxXQUFXLENBQUMsSUFBSSxDQUFDLEdBQUcsUUFBUSxDQUFDLENBQUMsQ0FBQyxJQUFJLEVBQUUsQ0FBQztZQUV0QyxJQUFJLENBQUMsY0FBYyxDQUFDLFdBQVcsQ0FBQyxJQUFJLENBQUMsQ0FBQyxFQUFFO2dCQUNwQyxjQUFjLENBQUMsV0FBVyxDQUFDLElBQUksQ0FBQyxDQUFDLEdBQUcsV0FBVyxDQUFDO2dCQUNoRCxhQUFhLENBQUMsZ0JBQWdCLENBQUMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxDQUFDO2FBQ3BEO1NBQ0o7SUFDTCxDQUFDO0lBRVMsY0FBYyxDQUFDLEtBQVk7UUFDakMsSUFBSSxXQUFXLEdBQUcsSUFBSSxDQUFDO1FBQ3ZCLE1BQU0sWUFBWSxHQUFHLEtBQUssRUFBRSxVQUFVLEVBQUUsUUFBUSxFQUFFLFlBQVksSUFBSSxFQUFFLENBQUM7UUFFckUsWUFBWSxDQUFDLE9BQU8sQ0FBQyxNQUFNLENBQUMsRUFBRTtZQUMxQixJQUFJLE1BQU0sQ0FBQyxLQUFLLEtBQUsseUJBQXlCLEVBQUU7Z0JBQzVDLFdBQVcsR0FBRyxNQUFNLENBQUM7YUFDeEI7UUFDTCxDQUFDLENBQUMsQ0FBQztRQUVILE9BQU8sV0FBVyxDQUFDO0lBQ3ZCLENBQUM7SUFFRDs7Ozs7O09BTUc7SUFDSSwwQkFBMEIsQ0FBQyxNQUFtQixFQUFFLFNBQWlCO1FBQ3BFLE1BQU0sYUFBYSxHQUFHLE1BQU0sQ0FBQyxjQUFjLENBQUMsU0FBUyxDQUFDLENBQUM7UUFDdkQsT0FBTyxDQUFDLENBQUMsYUFBYSxJQUFJLENBQUMsYUFBYSxDQUFDLFdBQVcsQ0FBQztJQUN6RCxDQUFDO0lBRUQ7Ozs7OztPQU1HO0lBQ0ksZ0JBQWdCLENBQUMsV0FBd0IsRUFBRSxJQUFZLEVBQUUsS0FBWTtRQUV4RSxJQUFJLENBQUMsV0FBVyxJQUFJLENBQUMsSUFBSSxJQUFJLENBQUMsS0FBSyxFQUFFO1lBQ2pDLE9BQU87U0FDVjtRQUVELElBQUksQ0FBQyxXQUFXLENBQUMsY0FBYyxFQUFFO1lBQzdCLFdBQVcsQ0FBQyxjQUFjLEdBQUcsRUFBRSxDQUFDO1NBQ25DO1FBRUQsV0FBVyxDQUFDLGNBQWMsQ0FBQyxJQUFJLENBQUMsR0FBRyxLQUFLLENBQUM7UUFFekMsSUFBSSxDQUFDLFdBQVcsQ0FBQyxRQUFRLENBQUMsT0FBTyxFQUFFO1lBQy9CLFdBQVcsQ0FBQyxRQUFRLENBQUMsT0FBTyxHQUFHLEVBQUUsQ0FBQztTQUNyQztRQUVELFdBQVcsQ0FBQyxRQUFRLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxHQUFHLEtBQUssQ0FBQyxRQUFRLENBQUM7UUFFcEQsSUFBSSxXQUFXLENBQUMsaUJBQWlCLElBQUksS0FBSyxDQUFDLFdBQVcsRUFBRTtZQUNwRCxXQUFXLENBQUMsaUJBQWlCLENBQUMsVUFBVSxDQUFDLElBQUksRUFBRSxLQUFLLENBQUMsV0FBVyxDQUFDLENBQUM7U0FDckU7SUFDTCxDQUFDO21GQTdNUSxrQkFBa0I7dUVBQWxCLGtCQUFrQixXQUFsQixrQkFBa0IsbUJBRmYsTUFBTTs7U0FFVCxrQkFBa0I7dUZBQWxCLGtCQUFrQjtjQUg5QixVQUFVO2VBQUM7Z0JBQ1IsVUFBVSxFQUFFLE1BQU07YUFDckIiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFN1aXRlQ1JNIGlzIGEgY3VzdG9tZXIgcmVsYXRpb25zaGlwIG1hbmFnZW1lbnQgcHJvZ3JhbSBkZXZlbG9wZWQgYnkgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqIENvcHlyaWdodCAoQykgMjAyMSBTYWxlc0FnaWxpdHkgTHRkLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBmcmVlIHNvZnR3YXJlOyB5b3UgY2FuIHJlZGlzdHJpYnV0ZSBpdCBhbmQvb3IgbW9kaWZ5IGl0IHVuZGVyXG4gKiB0aGUgdGVybXMgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSB2ZXJzaW9uIDMgYXMgcHVibGlzaGVkIGJ5IHRoZVxuICogRnJlZSBTb2Z0d2FyZSBGb3VuZGF0aW9uIHdpdGggdGhlIGFkZGl0aW9uIG9mIHRoZSBmb2xsb3dpbmcgcGVybWlzc2lvbiBhZGRlZFxuICogdG8gU2VjdGlvbiAxNSBhcyBwZXJtaXR0ZWQgaW4gU2VjdGlvbiA3KGEpOiBGT1IgQU5ZIFBBUlQgT0YgVEhFIENPVkVSRUQgV09SS1xuICogSU4gV0hJQ0ggVEhFIENPUFlSSUdIVCBJUyBPV05FRCBCWSBTQUxFU0FHSUxJVFksIFNBTEVTQUdJTElUWSBESVNDTEFJTVMgVEhFXG4gKiBXQVJSQU5UWSBPRiBOT04gSU5GUklOR0VNRU5UIE9GIFRISVJEIFBBUlRZIFJJR0hUUy5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZGlzdHJpYnV0ZWQgaW4gdGhlIGhvcGUgdGhhdCBpdCB3aWxsIGJlIHVzZWZ1bCwgYnV0IFdJVEhPVVRcbiAqIEFOWSBXQVJSQU5UWTsgd2l0aG91dCBldmVuIHRoZSBpbXBsaWVkIHdhcnJhbnR5IG9mIE1FUkNIQU5UQUJJTElUWSBvciBGSVRORVNTXG4gKiBGT1IgQSBQQVJUSUNVTEFSIFBVUlBPU0UuIFNlZSB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIGZvciBtb3JlXG4gKiBkZXRhaWxzLlxuICpcbiAqIFlvdSBzaG91bGQgaGF2ZSByZWNlaXZlZCBhIGNvcHkgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogYWxvbmcgd2l0aCB0aGlzIHByb2dyYW0uICBJZiBub3QsIHNlZSA8aHR0cDovL3d3dy5nbnUub3JnL2xpY2Vuc2VzLz4uXG4gKlxuICogSW4gYWNjb3JkYW5jZSB3aXRoIFNlY3Rpb24gNyhiKSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiB2ZXJzaW9uIDMsIHRoZXNlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCByZXRhaW4gdGhlIGRpc3BsYXkgb2YgdGhlXG4gKiBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiIGxvZ28uIElmIHRoZSBkaXNwbGF5IG9mIHRoZSBsb2dvcyBpcyBub3QgcmVhc29uYWJseVxuICogZmVhc2libGUgZm9yIHRlY2huaWNhbCByZWFzb25zLCB0aGUgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IGRpc3BsYXlcbiAqIHRoZSB3b3JkcyBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiLlxuICovXG5cbmltcG9ydCB7RmllbGRCdWlsZGVyfSBmcm9tICcuL2ZpZWxkLmJ1aWxkZXInO1xuaW1wb3J0IHtJbmplY3RhYmxlfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7VmFsaWRhdGlvbk1hbmFnZXJ9IGZyb20gJy4uL3ZhbGlkYXRpb24vdmFsaWRhdGlvbi5tYW5hZ2VyJztcbmltcG9ydCB7RGF0YVR5cGVGb3JtYXR0ZXJ9IGZyb20gJy4uLy4uL2Zvcm1hdHRlcnMvZGF0YS10eXBlLmZvcm1hdHRlci5zZXJ2aWNlJztcbmltcG9ydCB7U2F2ZWRGaWx0ZXJ9IGZyb20gJy4uLy4uLy4uL3N0b3JlL3NhdmVkLWZpbHRlcnMvc2F2ZWQtZmlsdGVyLm1vZGVsJztcbmltcG9ydCB7XG4gICAgZGVlcENsb25lLFxuICAgIEZpZWxkLFxuICAgIEZpZWxkRGVmaW5pdGlvbixcbiAgICBPcHRpb24sXG4gICAgU2VhcmNoQ3JpdGVyaWEsXG4gICAgU2VhcmNoQ3JpdGVyaWFGaWVsZEZpbHRlcixcbiAgICBWaWV3RmllbGREZWZpbml0aW9uXG59IGZyb20gJ2NvbW1vbic7XG5pbXBvcnQge0xhbmd1YWdlU3RvcmV9IGZyb20gJy4uLy4uLy4uL3N0b3JlL2xhbmd1YWdlL2xhbmd1YWdlLnN0b3JlJztcbmltcG9ydCB7QXN5bmNWYWxpZGF0b3JGbiwgVmFsaWRhdG9yRm59IGZyb20gJ0Bhbmd1bGFyL2Zvcm1zJztcbmltcG9ydCB7RmllbGRPYmplY3RSZWdpc3RyeX0gZnJvbSBcIi4vZmllbGQtb2JqZWN0LXR5cGUucmVnaXN0cnlcIjtcblxuQEluamVjdGFibGUoe1xuICAgIHByb3ZpZGVkSW46ICdyb290J1xufSlcbmV4cG9ydCBjbGFzcyBGaWx0ZXJGaWVsZEJ1aWxkZXIgZXh0ZW5kcyBGaWVsZEJ1aWxkZXIge1xuXG4gICAgY29uc3RydWN0b3IoXG4gICAgICAgIHByb3RlY3RlZCB2YWxpZGF0aW9uTWFuYWdlcjogVmFsaWRhdGlvbk1hbmFnZXIsXG4gICAgICAgIHByb3RlY3RlZCB0eXBlRm9ybWF0dGVyOiBEYXRhVHlwZUZvcm1hdHRlcixcbiAgICAgICAgcHJvdGVjdGVkIGZpZWxkUmVnaXN0cnk6IEZpZWxkT2JqZWN0UmVnaXN0cnlcbiAgICApIHtcbiAgICAgICAgc3VwZXIodmFsaWRhdGlvbk1hbmFnZXIsIHR5cGVGb3JtYXR0ZXIsIGZpZWxkUmVnaXN0cnkpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEJ1aWxkIGZpbHRlciBmaWVsZFxuICAgICAqXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHNhdmVkRmlsdGVyIFNhdmVkRmlsdGVyXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHZpZXdGaWVsZCBWaWV3RmllbGREZWZpbml0aW9uXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGxhbmd1YWdlIExhbmd1YWdlU3RvcmVcbiAgICAgKiBAcmV0dXJucyB7b2JqZWN0fSBGaWVsZFxuICAgICAqL1xuICAgIHB1YmxpYyBidWlsZEZpbHRlckZpZWxkKHNhdmVkRmlsdGVyOiBTYXZlZEZpbHRlciwgdmlld0ZpZWxkOiBWaWV3RmllbGREZWZpbml0aW9uLCBsYW5ndWFnZTogTGFuZ3VhZ2VTdG9yZSA9IG51bGwpOiBGaWVsZCB7XG5cbiAgICAgICAgY29uc3QgZGVmaW5pdGlvbiA9ICh2aWV3RmllbGQgJiYgdmlld0ZpZWxkLmZpZWxkRGVmaW5pdGlvbikgfHwge30gYXMgRmllbGREZWZpbml0aW9uO1xuICAgICAgICBjb25zdCB7dmFsaWRhdG9ycywgYXN5bmNWYWxpZGF0b3JzfSA9IHRoaXMuZ2V0RmlsdGVyVmFsaWRhdG9ycyhzYXZlZEZpbHRlciwgdmlld0ZpZWxkKTtcblxuICAgICAgICBjb25zdCBmaWVsZCA9IHRoaXMuc2V0dXBGaWVsZChcbiAgICAgICAgICAgIHNhdmVkRmlsdGVyLnNlYXJjaE1vZHVsZSxcbiAgICAgICAgICAgIHZpZXdGaWVsZCxcbiAgICAgICAgICAgIG51bGwsXG4gICAgICAgICAgICBudWxsLFxuICAgICAgICAgICAgbnVsbCxcbiAgICAgICAgICAgIHNhdmVkRmlsdGVyLFxuICAgICAgICAgICAgZGVmaW5pdGlvbixcbiAgICAgICAgICAgIHZhbGlkYXRvcnMsXG4gICAgICAgICAgICBhc3luY1ZhbGlkYXRvcnMsXG4gICAgICAgICAgICBsYW5ndWFnZVxuICAgICAgICApO1xuICAgICAgICBmaWVsZC5jcml0ZXJpYSA9IHRoaXMuaW5pdEZpZWxkRmlsdGVyKHNhdmVkRmlsdGVyLmNyaXRlcmlhLCB2aWV3RmllbGQsIGZpZWxkKTtcbiAgICAgICAgcmV0dXJuIGZpZWxkO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldCBGaWx0ZXIgVmFsaWRhdG9ycyBmb3IgZ2l2ZW4gZmllbGQgZGVmaW5pdGlvblxuICAgICAqXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHJlY29yZCAgUmVjb3JkXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHZpZXdGaWVsZCBWaWV3RmllbGREZWZpbml0aW9uXG4gICAgICogQHJldHVybnMge29iamVjdH0gdmFsaWRhdG9yIG1hcFxuICAgICAqL1xuICAgIHB1YmxpYyBnZXRGaWx0ZXJWYWxpZGF0b3JzKFxuICAgICAgICByZWNvcmQ6IFNhdmVkRmlsdGVyLFxuICAgICAgICB2aWV3RmllbGQ6IFZpZXdGaWVsZERlZmluaXRpb25cbiAgICApOiB7IHZhbGlkYXRvcnM6IFZhbGlkYXRvckZuW107IGFzeW5jVmFsaWRhdG9yczogQXN5bmNWYWxpZGF0b3JGbltdIH0ge1xuXG4gICAgICAgIGNvbnN0IHZhbGlkYXRvcnMgPSB0aGlzLnZhbGlkYXRpb25NYW5hZ2VyLmdldEZpbHRlclZhbGlkYXRpb25zKHJlY29yZC5zZWFyY2hNb2R1bGUsIHZpZXdGaWVsZCwgcmVjb3JkKTtcbiAgICAgICAgY29uc3QgYXN5bmNWYWxpZGF0b3JzOiBBc3luY1ZhbGlkYXRvckZuW10gPSBbXTtcblxuICAgICAgICByZXR1cm4ge3ZhbGlkYXRvcnMsIGFzeW5jVmFsaWRhdG9yc307XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogSW5pdCBmaWx0ZXIgZmllbGRzXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gc2VhcmNoQ3JpdGVyaWEgdG8gdXNlXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHZpZXdGaWVsZCB0byBpbml0XG4gICAgICogQHBhcmFtIHtvYmplY3R9IGZpZWxkIHRvIGluaXRcbiAgICAgKiBAcmV0dXJucyB7b2JqZWN0fSBTZWFyY2hDcml0ZXJpYUZpZWxkRmlsdGVyXG4gICAgICovXG4gICAgcHVibGljIGluaXRGaWVsZEZpbHRlcihzZWFyY2hDcml0ZXJpYTogU2VhcmNoQ3JpdGVyaWEsIHZpZXdGaWVsZDogVmlld0ZpZWxkRGVmaW5pdGlvbiwgZmllbGQ6IEZpZWxkKTogU2VhcmNoQ3JpdGVyaWFGaWVsZEZpbHRlciB7XG4gICAgICAgIGxldCBmaWVsZENyaXRlcmlhOiBTZWFyY2hDcml0ZXJpYUZpZWxkRmlsdGVyO1xuXG4gICAgICAgIGNvbnN0IGZpZWxkTmFtZSA9IHZpZXdGaWVsZC5uYW1lO1xuICAgICAgICBsZXQgZmllbGRUeXBlID0gdmlld0ZpZWxkLnR5cGU7XG5cbiAgICAgICAgaWYgKGZpZWxkVHlwZSA9PT0gJ2NvbXBvc2l0ZScpIHtcbiAgICAgICAgICAgIGZpZWxkVHlwZSA9IGZpZWxkLmRlZmluaXRpb24udHlwZTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChzZWFyY2hDcml0ZXJpYS5maWx0ZXJzW2ZpZWxkTmFtZV0gJiYgc2VhcmNoQ3JpdGVyaWEuZmlsdGVyc1tmaWVsZE5hbWVdLmZpZWxkVHlwZSkge1xuICAgICAgICAgICAgZmllbGRDcml0ZXJpYSA9IGRlZXBDbG9uZShzZWFyY2hDcml0ZXJpYS5maWx0ZXJzW2ZpZWxkTmFtZV0pO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgZmllbGRDcml0ZXJpYSA9IHtcbiAgICAgICAgICAgICAgICBmaWVsZDogZmllbGROYW1lLFxuICAgICAgICAgICAgICAgIGZpZWxkVHlwZSxcbiAgICAgICAgICAgICAgICBvcGVyYXRvcjogJycsXG4gICAgICAgICAgICAgICAgdmFsdWVzOiBbXVxuICAgICAgICAgICAgfTtcbiAgICAgICAgfVxuXG4gICAgICAgIHRoaXMubWFwRW51bUVtcHR5T3B0aW9uKGZpZWxkQ3JpdGVyaWEsIGZpZWxkKTtcblxuICAgICAgICB0aGlzLm1hcFJlbGF0ZUZpZWxkcyhmaWVsZENyaXRlcmlhLCBmaWVsZCwgc2VhcmNoQ3JpdGVyaWEpO1xuXG4gICAgICAgIHJldHVybiBmaWVsZENyaXRlcmlhO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBtYXBFbnVtRW1wdHlPcHRpb24oZmllbGRDcml0ZXJpYTogU2VhcmNoQ3JpdGVyaWFGaWVsZEZpbHRlciwgZmllbGQ6IEZpZWxkKTogdm9pZCB7XG4gICAgICAgIGlmICghWydtdWx0aWVudW0nLCAnZW51bSddLmluY2x1ZGVzKGZpZWxkQ3JpdGVyaWEuZmllbGRUeXBlKSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgbGV0IGVtcHR5T3B0aW9uID0gdGhpcy5nZXRFbXB0eU9wdGlvbihmaWVsZCk7XG5cbiAgICAgICAgaWYgKCFlbXB0eU9wdGlvbikge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCFmaWVsZENyaXRlcmlhLnZhbHVlcyB8fCAhZmllbGRDcml0ZXJpYS52YWx1ZXMubGVuZ3RoKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBmaWVsZENyaXRlcmlhLnZhbHVlcyA9IGZpZWxkQ3JpdGVyaWEudmFsdWVzLm1hcCh2YWx1ZSA9PiB7XG4gICAgICAgICAgICBpZiAodmFsdWUgIT09ICcnKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuIHZhbHVlO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICByZXR1cm4gJ19fU3VpdGVDUk1FbXB0eVN0cmluZ19fJztcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIG1hcFJlbGF0ZUZpZWxkcyhmaWVsZENyaXRlcmlhOiBTZWFyY2hDcml0ZXJpYUZpZWxkRmlsdGVyLCBmaWVsZDogRmllbGQsIHNlYXJjaENyaXRlcmlhOiBTZWFyY2hDcml0ZXJpYSk6IHZvaWQge1xuICAgICAgICBpZiAoIVsncmVsYXRlJ10uaW5jbHVkZXMoZmllbGRDcml0ZXJpYS5maWVsZFR5cGUpKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIWZpZWxkQ3JpdGVyaWEudmFsdWVzIHx8ICFmaWVsZENyaXRlcmlhLnZhbHVlcy5sZW5ndGgpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IGlkRmllbGROYW1lID0gKGZpZWxkICYmIGZpZWxkLmRlZmluaXRpb24gJiYgZmllbGQuZGVmaW5pdGlvbi5pZF9uYW1lKSB8fCAnJztcbiAgICAgICAgY29uc3QgcmVsYXRlRmllbGROYW1lID0gKGZpZWxkICYmIGZpZWxkLmRlZmluaXRpb24gJiYgZmllbGQuZGVmaW5pdGlvbi5ybmFtZSkgfHwgJ25hbWUnO1xuICAgICAgICBjb25zdCBpZFZhbHVlcyA9IHNlYXJjaENyaXRlcmlhPy5maWx0ZXJzW2lkRmllbGROYW1lXT8udmFsdWVzID8/IFtdO1xuXG4gICAgICAgIGZpZWxkQ3JpdGVyaWEudmFsdWVPYmplY3RBcnJheSA9IGZpZWxkQ3JpdGVyaWEudmFsdWVPYmplY3RBcnJheSA/PyBbXTtcbiAgICAgICAgY29uc3QgcmVsYXRlRmllbGRNYXAgPSB7fTtcbiAgICAgICAgaWYgKGZpZWxkQ3JpdGVyaWEudmFsdWVPYmplY3RBcnJheS5sZW5ndGgpIHtcbiAgICAgICAgICAgIGZpZWxkQ3JpdGVyaWEudmFsdWVPYmplY3RBcnJheS5mb3JFYWNoKHZhbHVlID0+IHtcbiAgICAgICAgICAgICAgICByZWxhdGVGaWVsZE1hcFt2YWx1ZS5pZF0gPSB2YWx1ZTtcbiAgICAgICAgICAgIH0pXG4gICAgICAgIH1cblxuICAgICAgICBmb3IgKGxldCBpID0gMDsgaSA8IGZpZWxkQ3JpdGVyaWEudmFsdWVzLmxlbmd0aDsgaSsrKSB7XG4gICAgICAgICAgICBsZXQgdmFsdWUgPSBmaWVsZENyaXRlcmlhLnZhbHVlc1tpXTtcblxuICAgICAgICAgICAgY29uc3QgcmVsYXRlVmFsdWUgPSB7fTtcbiAgICAgICAgICAgIHJlbGF0ZVZhbHVlW3JlbGF0ZUZpZWxkTmFtZV0gPSB2YWx1ZTtcbiAgICAgICAgICAgIHJlbGF0ZVZhbHVlWydpZCddID0gaWRWYWx1ZXNbaV0gPz8gJyc7XG5cbiAgICAgICAgICAgIGlmICghcmVsYXRlRmllbGRNYXBbcmVsYXRlVmFsdWVbJ2lkJ11dKSB7XG4gICAgICAgICAgICAgICAgcmVsYXRlRmllbGRNYXBbcmVsYXRlVmFsdWVbJ2lkJ11dID0gcmVsYXRlVmFsdWU7XG4gICAgICAgICAgICAgICAgZmllbGRDcml0ZXJpYS52YWx1ZU9iamVjdEFycmF5LnB1c2gocmVsYXRlVmFsdWUpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGdldEVtcHR5T3B0aW9uKGZpZWxkOiBGaWVsZCk6IE9wdGlvbiB7XG4gICAgICAgIGxldCBlbXB0eU9wdGlvbiA9IG51bGw7XG4gICAgICAgIGNvbnN0IGV4dHJhT3B0aW9ucyA9IGZpZWxkPy5kZWZpbml0aW9uPy5tZXRhZGF0YT8uZXh0cmFPcHRpb25zID8/IFtdO1xuXG4gICAgICAgIGV4dHJhT3B0aW9ucy5mb3JFYWNoKG9wdGlvbiA9PiB7XG4gICAgICAgICAgICBpZiAob3B0aW9uLnZhbHVlID09PSAnX19TdWl0ZUNSTUVtcHR5U3RyaW5nX18nKSB7XG4gICAgICAgICAgICAgICAgZW1wdHlPcHRpb24gPSBvcHRpb247XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIHJldHVybiBlbXB0eU9wdGlvbjtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBJcyBjcml0ZXJpYSBmaWVsZCBpbml0aWFsaXplZCBpbiByZWNvcmRcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSByZWNvcmQgUmVjb3JkXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IGZpZWxkTmFtZSBmaWVsZFxuICAgICAqIEByZXR1cm5zIHtib29sZWFufSBpc0luaXRpYWxpemVkXG4gICAgICovXG4gICAgcHVibGljIGlzQ3JpdGVyaWFGaWVsZEluaXRpYWxpemVkKHJlY29yZDogU2F2ZWRGaWx0ZXIsIGZpZWxkTmFtZTogc3RyaW5nKTogYm9vbGVhbiB7XG4gICAgICAgIGNvbnN0IGNyaXRlcmlhRmllbGQgPSByZWNvcmQuY3JpdGVyaWFGaWVsZHNbZmllbGROYW1lXTtcbiAgICAgICAgcmV0dXJuICEhY3JpdGVyaWFGaWVsZCAmJiAhY3JpdGVyaWFGaWVsZC52YXJkZWZCYXNlZDtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBBZGQgZmllbGQgdG8gU2F2ZWRGaWx0ZXJcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBzYXZlZEZpbHRlciBTYXZlZEZpbHRlclxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBuYW1lIHN0cmluZ1xuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBmaWVsZCBGaWVsZFxuICAgICAqL1xuICAgIHB1YmxpYyBhZGRUb1NhdmVkRmlsdGVyKHNhdmVkRmlsdGVyOiBTYXZlZEZpbHRlciwgbmFtZTogc3RyaW5nLCBmaWVsZDogRmllbGQpOiB2b2lkIHtcblxuICAgICAgICBpZiAoIXNhdmVkRmlsdGVyIHx8ICFuYW1lIHx8ICFmaWVsZCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCFzYXZlZEZpbHRlci5jcml0ZXJpYUZpZWxkcykge1xuICAgICAgICAgICAgc2F2ZWRGaWx0ZXIuY3JpdGVyaWFGaWVsZHMgPSB7fTtcbiAgICAgICAgfVxuXG4gICAgICAgIHNhdmVkRmlsdGVyLmNyaXRlcmlhRmllbGRzW25hbWVdID0gZmllbGQ7XG5cbiAgICAgICAgaWYgKCFzYXZlZEZpbHRlci5jcml0ZXJpYS5maWx0ZXJzKSB7XG4gICAgICAgICAgICBzYXZlZEZpbHRlci5jcml0ZXJpYS5maWx0ZXJzID0ge307XG4gICAgICAgIH1cblxuICAgICAgICBzYXZlZEZpbHRlci5jcml0ZXJpYS5maWx0ZXJzW25hbWVdID0gZmllbGQuY3JpdGVyaWE7XG5cbiAgICAgICAgaWYgKHNhdmVkRmlsdGVyLmNyaXRlcmlhRm9ybUdyb3VwICYmIGZpZWxkLmZvcm1Db250cm9sKSB7XG4gICAgICAgICAgICBzYXZlZEZpbHRlci5jcml0ZXJpYUZvcm1Hcm91cC5hZGRDb250cm9sKG5hbWUsIGZpZWxkLmZvcm1Db250cm9sKTtcbiAgICAgICAgfVxuICAgIH1cbn1cbiJdfQ==