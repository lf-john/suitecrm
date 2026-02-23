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
import { BaseFieldComponent } from './base-field.component';
import { Component } from '@angular/core';
import { isEmptyString, isVoid } from 'common';
import { DataTypeFormatter } from '../../services/formatters/data-type.formatter.service';
import { LanguageStore } from '../../store/language/language.store';
import { FieldLogicManager } from '../field-logic/field-logic.manager';
import { FieldLogicDisplayManager } from '../field-logic-display/field-logic-display.manager';
import { isNull, isObject } from "lodash-es";
import * as i0 from "@angular/core";
import * as i1 from "../../store/language/language.store";
import * as i2 from "../../services/formatters/data-type.formatter.service";
import * as i3 from "../field-logic/field-logic.manager";
import * as i4 from "../field-logic-display/field-logic-display.manager";
class BaseEnumComponent extends BaseFieldComponent {
    constructor(languages, typeFormatter, logic, logicDisplay) {
        super(typeFormatter, logic, logicDisplay);
        this.languages = languages;
        this.typeFormatter = typeFormatter;
        this.logic = logic;
        this.logicDisplay = logicDisplay;
        this.selectedValues = [];
        this.valueLabel = '';
        this.options = [];
        this.subs = [];
        this.isDynamicEnum = false;
    }
    ngOnInit() {
        super.ngOnInit();
        const options$ = this?.field?.metadata?.options$ ?? null;
        if (options$) {
            this.subs.push(this.field.metadata.options$.subscribe((options) => {
                this.buildProvidedOptions(options);
                this.initValue();
            }));
            return;
        }
        const options = this?.field?.definition?.options ?? null;
        if (options) {
            this.subs.push(this.languages.vm$.subscribe((strings) => {
                this.buildAppStringListOptions(strings.appListStrings);
                this.initValue();
            }));
        }
        if (!options && !options$) {
            this.initValue();
        }
    }
    ngOnDestroy() {
        this.isDynamicEnum = false;
        this.subs.forEach(sub => sub.unsubscribe());
        this.options = [];
        this.optionsMap = {};
        this.selectedValues = [];
    }
    getInvalidClass() {
        if (this.field.formControl && this.field.formControl.invalid && this.field.formControl.touched) {
            return 'is-invalid';
        }
        return '';
    }
    buildProvidedOptions(options) {
        this.options = options;
        this.optionsMap = {};
        options.forEach(option => {
            this.optionsMap[option.value] = option.label;
        });
    }
    buildAppStringListOptions(appStrings) {
        this.optionsMap = {};
        this.addExtraOptions();
        if (appStrings && this.field.definition.options && appStrings[this.field.definition.options]) {
            const options = appStrings[this.field.definition.options];
            if (this.options && Object.keys(this.options)) {
                this.optionsMap = { ...this.optionsMap, ...options };
            }
        }
        this.buildOptionsArray(appStrings);
    }
    addExtraOptions() {
        const extraOptions = (this.field.metadata && this.field.metadata.extraOptions) || [];
        extraOptions.forEach((item) => {
            if (isVoid(item.value)) {
                return;
            }
            let label = item.label || '';
            if (item.labelKey) {
                label = this.languages.getFieldLabel(item.labelKey);
            }
            this.optionsMap[item.value] = label;
        });
    }
    buildOptionsArray(appStrings) {
        this.options = [];
        Object.keys(this.optionsMap).forEach(key => {
            if (isEmptyString(this.optionsMap[key]) && !this.addEmptyStringOption()) {
                return;
            }
            this.options.push({
                value: key,
                label: this.optionsMap[key]
            });
        });
        if (this.isDynamicEnum) {
            this.buildDynamicEnumOptions(appStrings);
        }
    }
    addEmptyStringOption() {
        return this.field.type !== 'multienum';
    }
    initValue() {
        this.selectedValues = [];
        if (this.field.criteria) {
            this.initValueLabel();
            return;
        }
        if (typeof this.field.value !== 'string') {
            return;
        }
        if (!this.optionsMap) {
            return;
        }
        if (typeof this.optionsMap[this.field.value] !== 'string') {
            return;
        }
        this.initValueLabel();
    }
    initValueLabel() {
        const fieldValue = this.field.value || this.field.criteria?.target || undefined;
        if (fieldValue !== undefined) {
            this.valueLabel = this.optionsMap[fieldValue];
            this.selectedValues.push({
                value: fieldValue,
                label: this.valueLabel
            });
        }
    }
    /**
     *  Initialize the default value for the enum
     *
     *  @returns {void}
     *  @description set default enum value, if defined in vardefs
     * */
    initEnumDefault() {
        if (!isEmptyString(this.record?.id)) {
            this.field?.formControl.setValue('');
            return;
        }
        let defaultVal = this?.field?.default ?? this?.field?.definition?.default ?? null;
        if (typeof defaultVal === 'string') {
            defaultVal = defaultVal.trim();
        }
        if (!defaultVal) {
            this.field.formControl.setValue('');
            return;
        }
        this.selectedValues.push({
            value: defaultVal,
            label: this.optionsMap[defaultVal]
        });
        this.initEnumDefaultFieldValues(defaultVal);
    }
    initEnumDefaultFieldValues(defaultVal) {
        if (this.field.type === 'multienum') {
            const defaultValues = this.selectedValues.map(option => option.value);
            this.field.valueList = defaultValues;
            this.field.formControl.setValue(defaultValues);
        }
        else {
            this.field.value = defaultVal;
            this.field.formControl.setValue(defaultVal);
        }
        this.field.formControl.markAsDirty();
    }
    checkAndInitAsDynamicEnum() {
        const definition = (this.field && this.field.definition) || {};
        const dynamic = (definition && definition.dynamic) || false;
        const parentEnumKey = (definition && definition.parentenum) || '';
        const fields = (this.record && this.record.fields) || null;
        if (dynamic && parentEnumKey && fields) {
            this.isDynamicEnum = true;
            const parentEnum = fields[parentEnumKey];
            if (parentEnum) {
                this.subscribeToParentValueChanges(parentEnum);
            }
        }
    }
    buildDynamicEnumOptions(appStrings) {
        const parentEnum = this.record.fields[this.field.definition.parentenum];
        if (parentEnum) {
            const parentOptionMap = appStrings[parentEnum.definition.options];
            if (parentOptionMap && Object.keys(parentOptionMap).length !== 0) {
                this.mappedOptions = this.createParentChildOptionsMap(parentOptionMap, this.options);
                let parentValues = [];
                if (parentEnum.definition.type === 'multienum') {
                    parentValues = parentEnum.valueList;
                }
                else {
                    parentValues.push(parentEnum.value);
                }
                this.options = this.filterMatchingOptions(parentValues);
                if (parentValues && parentValues.length) {
                    this.setValueToAvailableOption();
                }
            }
        }
    }
    filterMatchingOptions(values) {
        let filteredOptions = [];
        if (!values || !values.length) {
            return [];
        }
        values.forEach(value => {
            if (!this.mappedOptions[value]) {
                return;
            }
            filteredOptions = filteredOptions.concat([...this.mappedOptions[value]]);
        });
        return filteredOptions;
    }
    createParentChildOptionsMap(parentOptions, childOptions) {
        const mappedOptions = {};
        Object.keys(parentOptions).forEach(key => {
            mappedOptions[key] = childOptions.filter(option => String(option.value).startsWith(key));
        });
        return mappedOptions;
    }
    subscribeToParentValueChanges(parentEnum) {
        if (parentEnum.formControl) {
            this.subs.push(parentEnum.formControl.valueChanges.subscribe(values => {
                if (typeof values === 'string') {
                    values = [values];
                }
                // Rebuild available enum options
                this.options = this.filterMatchingOptions(values);
                this.setValueToAvailableOption();
                this.initValue();
            }));
        }
    }
    setValueToAvailableOption() {
        if (!this?.options?.length) {
            this.field.value = '';
            this.field.formControl.setValue('');
            return;
        }
        if (!this.options.some(option => option.value === this.field.value)) {
            this.field.value = this.options[0].value;
            this.field.formControl.setValue(this.options[0].value);
        }
    }
    buildOptionFromValue(value) {
        const option = { value: '', label: '' };
        if (isNull(value)) {
            return option;
        }
        option.value = (typeof value !== 'string' ? JSON.stringify(value) : value).trim();
        option.label = option.value;
        const valueLabel = this.optionsMap[option.value] ?? option.label;
        if (isObject(valueLabel)) {
            return option;
        }
        option.label = (typeof valueLabel !== 'string' ? JSON.stringify(valueLabel) : valueLabel).trim();
        return option;
    }
    static { this.ɵfac = function BaseEnumComponent_Factory(t) { return new (t || BaseEnumComponent)(i0.ɵɵdirectiveInject(i1.LanguageStore), i0.ɵɵdirectiveInject(i2.DataTypeFormatter), i0.ɵɵdirectiveInject(i3.FieldLogicManager), i0.ɵɵdirectiveInject(i4.FieldLogicDisplayManager)); }; }
    static { this.ɵcmp = /*@__PURE__*/ i0.ɵɵdefineComponent({ type: BaseEnumComponent, selectors: [["ng-component"]], features: [i0.ɵɵInheritDefinitionFeature], decls: 0, vars: 0, template: function BaseEnumComponent_Template(rf, ctx) { }, encapsulation: 2 }); }
}
export { BaseEnumComponent };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(BaseEnumComponent, [{
        type: Component,
        args: [{ template: '' }]
    }], function () { return [{ type: i1.LanguageStore }, { type: i2.DataTypeFormatter }, { type: i3.FieldLogicManager }, { type: i4.FieldLogicDisplayManager }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYmFzZS1lbnVtLmNvbXBvbmVudC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uL2NvcmUvYXBwL2NvcmUvc3JjL2xpYi9maWVsZHMvYmFzZS9iYXNlLWVudW0uY29tcG9uZW50LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0F3Qkc7QUFFSCxPQUFPLEVBQUMsa0JBQWtCLEVBQUMsTUFBTSx3QkFBd0IsQ0FBQztBQUMxRCxPQUFPLEVBQUMsU0FBUyxFQUFvQixNQUFNLGVBQWUsQ0FBQztBQUUzRCxPQUFPLEVBQXlCLGFBQWEsRUFBRSxNQUFNLEVBQVMsTUFBTSxRQUFRLENBQUM7QUFDN0UsT0FBTyxFQUFDLGlCQUFpQixFQUFDLE1BQU0sdURBQXVELENBQUM7QUFDeEYsT0FBTyxFQUVILGFBQWEsRUFHaEIsTUFBTSxxQ0FBcUMsQ0FBQztBQUM3QyxPQUFPLEVBQUMsaUJBQWlCLEVBQUMsTUFBTSxvQ0FBb0MsQ0FBQztBQUNyRSxPQUFPLEVBQUMsd0JBQXdCLEVBQUMsTUFBTSxvREFBb0QsQ0FBQztBQUM1RixPQUFPLEVBQUMsTUFBTSxFQUFFLFFBQVEsRUFBQyxNQUFNLFdBQVcsQ0FBQzs7Ozs7O0FBRTNDLE1BQ2EsaUJBQWtCLFNBQVEsa0JBQWtCO0lBVXJELFlBQ2MsU0FBd0IsRUFDeEIsYUFBZ0MsRUFDaEMsS0FBd0IsRUFDeEIsWUFBc0M7UUFFaEQsS0FBSyxDQUFDLGFBQWEsRUFBRSxLQUFLLEVBQUUsWUFBWSxDQUFDLENBQUM7UUFMaEMsY0FBUyxHQUFULFNBQVMsQ0FBZTtRQUN4QixrQkFBYSxHQUFiLGFBQWEsQ0FBbUI7UUFDaEMsVUFBSyxHQUFMLEtBQUssQ0FBbUI7UUFDeEIsaUJBQVksR0FBWixZQUFZLENBQTBCO1FBYnBELG1CQUFjLEdBQWEsRUFBRSxDQUFDO1FBQzlCLGVBQVUsR0FBRyxFQUFFLENBQUM7UUFFaEIsWUFBTyxHQUFhLEVBQUUsQ0FB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S0FBSyxDQUFDO1FBQ2pFLElBQUksUUFBUSxDQUFDLFVBQVUsQ0FBQyxFQUFFO1lBQ3RCLE9BQU8sTUFBTSxDQUFDO1NBQ2pCO1FBQ0QsTUFBTSxDQUFDLEtBQUssR0FBRyxDQUFDLE9BQU8sVUFBVSxLQUFLLFFBQVEsQ0FBQyxDQUFDLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxVQUFVLENBQUMsQ0FBQyxDQUFDLENBQUMsVUFBVSxDQUFDLENBQUMsSUFBSSxFQUFFLENBQUM7UUFFakcsT0FBTyxNQUFNLENBQUM7SUFDbEIsQ0FBQztrRkF6VVEsaUJBQWlCO29FQUFqQixpQkFBaUI7O1NBQWpCLGlCQUFpQjt1RkFBakIsaUJBQWlCO2NBRDdCLFNBQVM7ZUFBQyxFQUFDLFFBQVEsRUFBRSxFQUFFLEVBQUMiLCJzb3VyY2VzQ29udGVudCI6WyIvKipcbiAqIFN1aXRlQ1JNIGlzIGEgY3VzdG9tZXIgcmVsYXRpb25zaGlwIG1hbmFnZW1lbnQgcHJvZ3JhbSBkZXZlbG9wZWQgYnkgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqIENvcHlyaWdodCAoQykgMjAyMSBTYWxlc0FnaWxpdHkgTHRkLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBmcmVlIHNvZnR3YXJlOyB5b3UgY2FuIHJlZGlzdHJpYnV0ZSBpdCBhbmQvb3IgbW9kaWZ5IGl0IHVuZGVyXG4gKiB0aGUgdGVybXMgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSB2ZXJzaW9uIDMgYXMgcHVibGlzaGVkIGJ5IHRoZVxuICogRnJlZSBTb2Z0d2FyZSBGb3VuZGF0aW9uIHdpdGggdGhlIGFkZGl0aW9uIG9mIHRoZSBmb2xsb3dpbmcgcGVybWlzc2lvbiBhZGRlZFxuICogdG8gU2VjdGlvbiAxNSBhcyBwZXJtaXR0ZWQgaW4gU2VjdGlvbiA3KGEpOiBGT1IgQU5ZIFBBUlQgT0YgVEhFIENPVkVSRUQgV09SS1xuICogSU4gV0hJQ0ggVEhFIENPUFlSSUdIVCBJUyBPV05FRCBCWSBTQUxFU0FHSUxJVFksIFNBTEVTQUdJTElUWSBESVNDTEFJTVMgVEhFXG4gKiBXQVJSQU5UWSBPRiBOT04gSU5GUklOR0VNRU5UIE9GIFRISVJEIFBBUlRZIFJJR0hUUy5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZGlzdHJpYnV0ZWQgaW4gdGhlIGhvcGUgdGhhdCBpdCB3aWxsIGJlIHVzZWZ1bCwgYnV0IFdJVEhPVVRcbiAqIEFOWSBXQVJSQU5UWTsgd2l0aG91dCBldmVuIHRoZSBpbXBsaWVkIHdhcnJhbnR5IG9mIE1FUkNIQU5UQUJJTElUWSBvciBGSVRORVNTXG4gKiBGT1IgQSBQQVJUSUNVTEFSIFBVUlBPU0UuIFNlZSB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIGZvciBtb3JlXG4gKiBkZXRhaWxzLlxuICpcbiAqIFlvdSBzaG91bGQgaGF2ZSByZWNlaXZlZCBhIGNvcHkgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogYWxvbmcgd2l0aCB0aGlzIHByb2dyYW0uICBJZiBub3QsIHNlZSA8aHR0cDovL3d3dy5nbnUub3JnL2xpY2Vuc2VzLz4uXG4gKlxuICogSW4gYWNjb3JkYW5jZSB3aXRoIFNlY3Rpb24gNyhiKSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiB2ZXJzaW9uIDMsIHRoZXNlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCByZXRhaW4gdGhlIGRpc3BsYXkgb2YgdGhlXG4gKiBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiIGxvZ28uIElmIHRoZSBkaXNwbGF5IG9mIHRoZSBsb2dvcyBpcyBub3QgcmVhc29uYWJseVxuICogZmVhc2libGUgZm9yIHRlY2huaWNhbCByZWFzb25zLCB0aGUgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IGRpc3BsYXlcbiAqIHRoZSB3b3JkcyBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiLlxuICovXG5cbmltcG9ydCB7QmFzZUZpZWxkQ29tcG9uZW50fSBmcm9tICcuL2Jhc2UtZmllbGQuY29tcG9uZW50JztcbmltcG9ydCB7Q29tcG9uZW50LCBPbkRlc3Ryb3ksIE9uSW5pdH0gZnJvbSAnQGFuZ3VsYXIvY29yZSc7XG5pbXBvcnQge1N1YnNjcmlwdGlvbn0gZnJvbSAncnhqcyc7XG5pbXBvcnQge0ZpZWxkLCBGaWVsZERlZmluaXRpb24sIGlzRW1wdHlTdHJpbmcsIGlzVm9pZCwgT3B0aW9ufSBmcm9tICdjb21tb24nO1xuaW1wb3J0IHtEYXRhVHlwZUZvcm1hdHRlcn0gZnJvbSAnLi4vLi4vc2VydmljZXMvZm9ybWF0dGVycy9kYXRhLXR5cGUuZm9ybWF0dGVyLnNlcnZpY2UnO1xuaW1wb3J0IHtcbiAgICBMYW5ndWFnZUxpc3RTdHJpbmdNYXAsXG4gICAgTGFuZ3VhZ2VTdG9yZSxcbiAgICBMYW5ndWFnZVN0cmluZ01hcCxcbiAgICBMYW5ndWFnZVN0cmluZ3Ncbn0gZnJvbSAnLi4vLi4vc3RvcmUvbGFuZ3VhZ2UvbGFuZ3VhZ2Uuc3RvcmUnO1xuaW1wb3J0IHtGaWVsZExvZ2ljTWFuYWdlcn0gZnJvbSAnLi4vZmllbGQtbG9naWMvZmllbGQtbG9naWMubWFuYWdlcic7XG5pbXBvcnQge0ZpZWxkTG9naWNEaXNwbGF5TWFuYWdlcn0gZnJvbSAnLi4vZmllbGQtbG9naWMtZGlzcGxheS9maWVsZC1sb2dpYy1kaXNwbGF5Lm1hbmFnZXInO1xuaW1wb3J0IHtpc051bGwsIGlzT2JqZWN0fSBmcm9tIFwibG9kYXNoLWVzXCI7XG5cbkBDb21wb25lbnQoe3RlbXBsYXRlOiAnJ30pXG5leHBvcnQgY2xhc3MgQmFzZUVudW1Db21wb25lbnQgZXh0ZW5kcyBCYXNlRmllbGRDb21wb25lbnQgaW1wbGVtZW50cyBPbkluaXQsIE9uRGVzdHJveSB7XG4gICAgc2VsZWN0ZWRWYWx1ZXM6IE9wdGlvbltdID0gW107XG4gICAgdmFsdWVMYWJlbCA9ICcnO1xuICAgIG9wdGlvbnNNYXA6IExhbmd1YWdlU3RyaW5nTWFwO1xuICAgIG9wdGlvbnM6IE9wdGlvbltdID0gW107XG4gICAgbGFiZWxzOiBMYW5ndWFnZVN0cmluZ01hcDtcbiAgICBwcm90ZWN0ZWQgc3ViczogU3Vic2NyaXB0aW9uW10gPSBbXTtcbiAgICBwcm90ZWN0ZWQgbWFwcGVkT3B0aW9uczogeyBba2V5OiBzdHJpbmddOiBPcHRpb25bXSB9O1xuICAgIHByb3RlY3RlZCBpc0R5bmFtaWNFbnVtID0gZmFsc2U7XG5cbiAgICBjb25zdHJ1Y3RvcihcbiAgICAgICAgcHJvdGVjdGVkIGxhbmd1YWdlczogTGFuZ3VhZ2VTdG9yZSxcbiAgICAgICAgcHJvdGVjdGVkIHR5cGVGb3JtYXR0ZXI6IERhdGFUeXBlRm9ybWF0dGVyLFxuICAgICAgICBwcm90ZWN0ZWQgbG9naWM6IEZpZWxkTG9naWNNYW5hZ2VyLFxuICAgICAgICBwcm90ZWN0ZWQgbG9naWNEaXNwbGF5OiBGaWVsZExvZ2ljRGlzcGxheU1hbmFnZXJcbiAgICApIHtcbiAgICAgICAgc3VwZXIodHlwZUZvcm1hdHRlciwgbG9naWMsIGxvZ2ljRGlzcGxheSk7XG4gICAgfVxuXG4gICAgbmdPbkluaXQoKTogdm9pZCB7XG5cbiAgICAgICAgc3VwZXIubmdPbkluaXQoKTtcblxuICAgICAgICBjb25zdCBvcHRpb25zJCA9IHRoaXM/LmZpZWxkPy5tZXRhZGF0YT8ub3B0aW9ucyQgPz8gbnVsbDtcbiAgICAgICAgaWYgKG9wdGlvbnMkKSB7XG4gICAgICAgICAgICB0aGlzLnN1YnMucHVzaCh0aGlzLmZpZWxkLm1ldGFkYXRhLm9wdGlvbnMkLnN1YnNjcmliZSgob3B0aW9uczogT3B0aW9uW10pID0+IHtcbiAgICAgICAgICAgICAgICB0aGlzLmJ1aWxkUHJvdmlkZWRPcHRpb25zKG9wdGlvbnMpO1xuXG4gICAgICAgICAgICAgICAgdGhpcy5pbml0VmFsdWUoKTtcblxuICAgICAgICAgICAgfSkpO1xuICAgICAgICAgICAgcmV0dXJuO1xuXG4gICAgICAgIH1cblxuICAgICAgICBjb25zdCBvcHRpb25zID0gdGhpcz8uZmllbGQ/LmRlZmluaXRpb24/Lm9wdGlvbnMgPz8gbnVsbDtcbiAgICAgICAgaWYgKG9wdGlvbnMpIHtcbiAgICAgICAgICAgIHRoaXMuc3Vicy5wdXNoKHRoaXMubGFuZ3VhZ2VzLnZtJC5zdWJzY3JpYmUoKHN0cmluZ3M6IExhbmd1YWdlU3RyaW5ncykgPT4ge1xuXG4gICAgICAgICAgICAgICAgdGhpcy5idWlsZEFwcFN0cmluZ0xpc3RPcHRpb25zKHN0cmluZ3MuYXBwTGlzdFN0cmluZ3MpO1xuICAgICAgICAgICAgICAgIHRoaXMuaW5pdFZhbHVlKCk7XG5cbiAgICAgICAgICAgIH0pKTtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICghb3B0aW9ucyAmJiAhb3B0aW9ucyQpIHtcbiAgICAgICAgICAgIHRoaXMuaW5pdFZhbHVlKCk7XG4gICAgICAgIH1cblxuICAgIH1cblxuICAgIG5nT25EZXN0cm95KCk6IHZvaWQge1xuICAgICAgICB0aGlzLmlzRHluYW1pY0VudW0gPSBmYWxzZTtcbiAgICAgICAgdGhpcy5zdWJzLmZvckVhY2goc3ViID0+IHN1Yi51bnN1YnNjcmliZSgpKTtcbiAgICAgICAgdGhpcy5vcHRpb25zID0gW107XG4gICAgICAgIHRoaXMub3B0aW9uc01hcCA9IHt9O1xuICAgICAgICB0aGlzLnNlbGVjdGVkVmFsdWVzID0gW107XG4gICAgfVxuXG4gICAgZ2V0SW52YWxpZENsYXNzKCk6IHN0cmluZyB7XG4gICAgICAgIGlmICh0aGlzLmZpZWxkLmZvcm1Db250cm9sICYmIHRoaXMuZmllbGQuZm9ybUNvbnRyb2wuaW52YWxpZCAmJiB0aGlzLmZpZWxkLmZvcm1Db250cm9sLnRvdWNoZWQpIHtcbiAgICAgICAgICAgIHJldHVybiAnaXMtaW52YWxpZCc7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuICcnO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBidWlsZFByb3ZpZGVkT3B0aW9ucyhvcHRpb25zOiBPcHRpb25bXSk6IHZvaWQge1xuICAgICAgICB0aGlzLm9wdGlvbnMgPSBvcHRpb25zO1xuICAgICAgICB0aGlzLm9wdGlvbnNNYXAgPSB7fTtcblxuICAgICAgICBvcHRpb25zLmZvckVhY2gob3B0aW9uID0+IHtcbiAgICAgICAgICAgIHRoaXMub3B0aW9uc01hcFtvcHRpb24udmFsdWVdID0gb3B0aW9uLmxhYmVsO1xuICAgICAgICB9KTtcblxuICAgIH1cblxuICAgIHByb3RlY3RlZCBidWlsZEFwcFN0cmluZ0xpc3RPcHRpb25zKGFwcFN0cmluZ3M6IExhbmd1YWdlTGlzdFN0cmluZ01hcCk6IHZvaWQge1xuXG4gICAgICAgIHRoaXMub3B0aW9uc01hcCA9IHt9IGFzIExhbmd1YWdlU3RyaW5nTWFwO1xuICAgICAgICB0aGlzLmFkZEV4dHJhT3B0aW9ucygpO1xuXG4gICAgICAgIGlmIChhcHBTdHJpbmdzICYmIHRoaXMuZmllbGQuZGVmaW5pdGlvbi5vcHRpb25zICYmIGFwcFN0cmluZ3NbdGhpcy5maWVsZC5kZWZpbml0aW9uLm9wdGlvbnNdKSB7XG4gICAgICAgICAgICBjb25zdCBvcHRpb25zID0gYXBwU3RyaW5nc1t0aGlzLmZpZWxkLmRlZmluaXRpb24ub3B0aW9uc10gYXMgTGFuZ3VhZ2VTdHJpbmdNYXA7XG5cbiAgICAgICAgICAgIGlmICh0aGlzLm9wdGlvbnMgJiYgT2JqZWN0LmtleXModGhpcy5vcHRpb25zKSkge1xuICAgICAgICAgICAgICAgIHRoaXMub3B0aW9uc01hcCA9IHsuLi50aGlzLm9wdGlvbnNNYXAsIC4uLm9wdGlvbnN9O1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5idWlsZE9wdGlvbnNBcnJheShhcHBTdHJpbmdzKTtcbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgYWRkRXh0cmFPcHRpb25zKCk6IHZvaWQge1xuICAgICAgICBjb25zdCBleHRyYU9wdGlvbnMgPSAodGhpcy5maWVsZC5tZXRhZGF0YSAmJiB0aGlzLmZpZWxkLm1ldGFkYXRhLmV4dHJhT3B0aW9ucykgfHwgW107XG5cbiAgICAgICAgZXh0cmFPcHRpb25zLmZvckVhY2goKGl0ZW06IE9wdGlvbikgPT4ge1xuICAgICAgICAgICAgaWYgKGlzVm9pZChpdGVtLnZhbHVlKSkge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgbGV0IGxhYmVsID0gaXRlbS5sYWJlbCB8fCAnJztcbiAgICAgICAgICAgIGlmIChpdGVtLmxhYmVsS2V5KSB7XG4gICAgICAgICAgICAgICAgbGFiZWwgPSB0aGlzLmxhbmd1YWdlcy5nZXRGaWVsZExhYmVsKGl0ZW0ubGFiZWxLZXkpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICB0aGlzLm9wdGlvbnNNYXBbaXRlbS52YWx1ZV0gPSBsYWJlbDtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGJ1aWxkT3B0aW9uc0FycmF5KGFwcFN0cmluZ3M6IExhbmd1YWdlTGlzdFN0cmluZ01hcCk6IHZvaWQge1xuXG4gICAgICAgIHRoaXMub3B0aW9ucyA9IFtdO1xuICAgICAgICBPYmplY3Qua2V5cyh0aGlzLm9wdGlvbnNNYXApLmZvckVhY2goa2V5ID0+IHtcblxuICAgICAgICAgICAgaWYgKGlzRW1wdHlTdHJpbmcodGhpcy5vcHRpb25zTWFwW2tleV0pICYmICF0aGlzLmFkZEVtcHR5U3RyaW5nT3B0aW9uKCkpIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHRoaXMub3B0aW9ucy5wdXNoKHtcbiAgICAgICAgICAgICAgICB2YWx1ZToga2V5LFxuICAgICAgICAgICAgICAgIGxhYmVsOiB0aGlzLm9wdGlvbnNNYXBba2V5XVxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH0pO1xuXG4gICAgICAgIGlmICh0aGlzLmlzRHluYW1pY0VudW0pIHtcbiAgICAgICAgICAgIHRoaXMuYnVpbGREeW5hbWljRW51bU9wdGlvbnMoYXBwU3RyaW5ncyk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgYWRkRW1wdHlTdHJpbmdPcHRpb24oKTogYm9vbGVhbiB7XG4gICAgICAgIHJldHVybiB0aGlzLmZpZWxkLnR5cGUgIT09ICdtdWx0aWVudW0nO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBpbml0VmFsdWUoKTogdm9pZCB7XG5cbiAgICAgICAgdGhpcy5zZWxlY3RlZFZhbHVlcyA9IFtdO1xuXG4gICAgICAgIGlmICh0aGlzLmZpZWxkLmNyaXRlcmlhKSB7XG4gICAgICAgICAgICB0aGlzLmluaXRWYWx1ZUxhYmVsKCk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodHlwZW9mIHRoaXMuZmllbGQudmFsdWUgIT09ICdzdHJpbmcnKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIXRoaXMub3B0aW9uc01hcCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHR5cGVvZiB0aGlzLm9wdGlvbnNNYXBbdGhpcy5maWVsZC52YWx1ZV0gIT09ICdzdHJpbmcnKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLmluaXRWYWx1ZUxhYmVsKCk7XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGluaXRWYWx1ZUxhYmVsKCkge1xuICAgICAgICBjb25zdCBmaWVsZFZhbHVlID0gdGhpcy5maWVsZC52YWx1ZSB8fCB0aGlzLmZpZWxkLmNyaXRlcmlhPy50YXJnZXQgfHwgdW5kZWZpbmVkO1xuICAgICAgICBpZiAoZmllbGRWYWx1ZSAhPT0gdW5kZWZpbmVkKSB7XG4gICAgICAgICAgICB0aGlzLnZhbHVlTGFiZWwgPSB0aGlzLm9wdGlvbnNNYXBbZmllbGRWYWx1ZV07XG4gICAgICAgICAgICB0aGlzLnNlbGVjdGVkVmFsdWVzLnB1c2goe1xuICAgICAgICAgICAgICAgIHZhbHVlOiBmaWVsZFZhbHVlLFxuICAgICAgICAgICAgICAgIGxhYmVsOiB0aGlzLnZhbHVlTGFiZWxcbiAgICAgICAgICAgIH0pO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogIEluaXRpYWxpemUgdGhlIGRlZmF1bHQgdmFsdWUgZm9yIHRoZSBlbnVtXG4gICAgICpcbiAgICAgKiAgQHJldHVybnMge3ZvaWR9XG4gICAgICogIEBkZXNjcmlwdGlvbiBzZXQgZGVmYXVsdCBlbnVtIHZhbHVlLCBpZiBkZWZpbmVkIGluIHZhcmRlZnNcbiAgICAgKiAqL1xuICAgIHByb3RlY3RlZCBpbml0RW51bURlZmF1bHQoKTogdm9pZCB7XG5cbiAgICAgICAgaWYgKCFpc0VtcHR5U3RyaW5nKHRoaXMucmVjb3JkPy5pZCkpIHtcbiAgICAgICAgICAgIHRoaXMuZmllbGQ/LmZvcm1Db250cm9sLnNldFZhbHVlKCcnKTtcblxuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgbGV0IGRlZmF1bHRWYWwgPSB0aGlzPy5maWVsZD8uZGVmYXVsdCA/PyB0aGlzPy5maWVsZD8uZGVmaW5pdGlvbj8uZGVmYXVsdCA/PyBudWxsO1xuICAgICAgICBpZiAodHlwZW9mIGRlZmF1bHRWYWwgPT09ICdzdHJpbmcnKSB7XG4gICAgICAgICAgICBkZWZhdWx0VmFsID0gZGVmYXVsdFZhbC50cmltKCk7XG4gICAgICAgIH1cbiAgICAgICAgaWYgKCFkZWZhdWx0VmFsKSB7XG4gICAgICAgICAgICB0aGlzLmZpZWxkLmZvcm1Db250cm9sLnNldFZhbHVlKCcnKTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuICAgICAgICB0aGlzLnNlbGVjdGVkVmFsdWVzLnB1c2goe1xuICAgICAgICAgICAgdmFsdWU6IGRlZmF1bHRWYWwsXG4gICAgICAgICAgICBsYWJlbDogdGhpcy5vcHRpb25zTWFwW2RlZmF1bHRWYWxdXG4gICAgICAgIH0pO1xuICAgICAgICB0aGlzLmluaXRFbnVtRGVmYXVsdEZpZWxkVmFsdWVzKGRlZmF1bHRWYWwpO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBpbml0RW51bURlZmF1bHRGaWVsZFZhbHVlcyhkZWZhdWx0VmFsOiBzdHJpbmcpOiB2b2lkIHtcblxuICAgICAgICBpZiAodGhpcy5maWVsZC50eXBlID09PSAnbXVsdGllbnVtJykge1xuICAgICAgICAgICAgY29uc3QgZGVmYXVsdFZhbHVlcyA9IHRoaXMuc2VsZWN0ZWRWYWx1ZXMubWFwKG9wdGlvbiA9PiBvcHRpb24udmFsdWUpO1xuICAgICAgICAgICAgdGhpcy5maWVsZC52YWx1ZUxpc3QgPSBkZWZhdWx0VmFsdWVzO1xuICAgICAgICAgICAgdGhpcy5maWVsZC5mb3JtQ29udHJvbC5zZXRWYWx1ZShkZWZhdWx0VmFsdWVzKTtcblxuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgdGhpcy5maWVsZC52YWx1ZSA9IGRlZmF1bHRWYWw7XG4gICAgICAgICAgICB0aGlzLmZpZWxkLmZvcm1Db250cm9sLnNldFZhbHVlKGRlZmF1bHRWYWwpO1xuICAgICAgICB9XG4gICAgICAgIHRoaXMuZmllbGQuZm9ybUNvbnRyb2wubWFya0FzRGlydHkoKTtcbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgY2hlY2tBbmRJbml0QXNEeW5hbWljRW51bSgpOiB2b2lkIHtcblxuICAgICAgICBjb25zdCBkZWZpbml0aW9uID0gKHRoaXMuZmllbGQgJiYgdGhpcy5maWVsZC5kZWZpbml0aW9uKSB8fCB7fSBhcyBGaWVsZERlZmluaXRpb247XG4gICAgICAgIGNvbnN0IGR5bmFtaWMgPSAoZGVmaW5pdGlvbiAmJiBkZWZpbml0aW9uLmR5bmFtaWMpIHx8IGZhbHNlO1xuICAgICAgICBjb25zdCBwYXJlbnRFbnVtS2V5ID0gKGRlZmluaXRpb24gJiYgZGVmaW5pdGlvbi5wYXJlbnRlbnVtKSB8fCAnJztcbiAgICAgICAgY29uc3QgZmllbGRzID0gKHRoaXMucmVjb3JkICYmIHRoaXMucmVjb3JkLmZpZWxkcykgfHwgbnVsbDtcblxuICAgICAgICBpZiAoZHluYW1pYyAmJiBwYXJlbnRFbnVtS2V5ICYmIGZpZWxkcykge1xuICAgICAgICAgICAgdGhpcy5pc0R5bmFtaWNFbnVtID0gdHJ1ZTtcbiAgICAgICAgICAgIGNvbnN0IHBhcmVudEVudW06IEZpZWxkID0gZmllbGRzW3BhcmVudEVudW1LZXldO1xuICAgICAgICAgICAgaWYgKHBhcmVudEVudW0pIHtcbiAgICAgICAgICAgICAgICB0aGlzLnN1YnNjcmliZVRvUGFyZW50VmFsdWVDaGFuZ2VzKHBhcmVudEVudW0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGJ1aWxkRHluYW1pY0VudW1PcHRpb25zKGFwcFN0cmluZ3M6IExhbmd1YWdlTGlzdFN0cmluZ01hcCk6IHZvaWQge1xuXG4gICAgICAgIGNvbnN0IHBhcmVudEVudW0gPSB0aGlzLnJlY29yZC5maWVsZHNbdGhpcy5maWVsZC5kZWZpbml0aW9uLnBhcmVudGVudW1dO1xuXG4gICAgICAgIGlmIChwYXJlbnRFbnVtKSB7XG5cbiAgICAgICAgICAgIGNvbnN0IHBhcmVudE9wdGlvbk1hcDogTGFuZ3VhZ2VTdHJpbmdNYXAgPSBhcHBTdHJpbmdzW3BhcmVudEVudW0uZGVmaW5pdGlvbi5vcHRpb25zXSBhcyBMYW5ndWFnZVN0cmluZ01hcDtcblxuICAgICAgICAgICAgaWYgKHBhcmVudE9wdGlvbk1hcCAmJiBPYmplY3Qua2V5cyhwYXJlbnRPcHRpb25NYXApLmxlbmd0aCAhPT0gMCkge1xuXG4gICAgICAgICAgICAgICAgdGhpcy5tYXBwZWRPcHRpb25zID0gdGhpcy5jcmVhdGVQYXJlbnRDaGlsZE9wdGlvbnNNYXAocGFyZW50T3B0aW9uTWFwLCB0aGlzLm9wdGlvbnMpO1xuXG4gICAgICAgICAgICAgICAgbGV0IHBhcmVudFZhbHVlczogc3RyaW5nW10gPSBbXTtcbiAgICAgICAgICAgICAgICBpZiAocGFyZW50RW51bS5kZWZpbml0aW9uLnR5cGUgPT09ICdtdWx0aWVudW0nKSB7XG4gICAgICAgICAgICAgICAgICAgIHBhcmVudFZhbHVlcyA9IHBhcmVudEVudW0udmFsdWVMaXN0O1xuICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgIHBhcmVudFZhbHVlcy5wdXNoKHBhcmVudEVudW0udmFsdWUpO1xuICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB0aGlzLm9wdGlvbnMgPSB0aGlzLmZpbHRlck1hdGNoaW5nT3B0aW9ucyhwYXJlbnRWYWx1ZXMpO1xuXG4gICAgICAgICAgICAgICAgaWYgKHBhcmVudFZhbHVlcyAmJiBwYXJlbnRWYWx1ZXMubGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuc2V0VmFsdWVUb0F2YWlsYWJsZU9wdGlvbigpO1xuICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgfVxuICAgICAgICB9XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGZpbHRlck1hdGNoaW5nT3B0aW9ucyh2YWx1ZXM6IHN0cmluZ1tdKTogT3B0aW9uW10ge1xuXG4gICAgICAgIGxldCBmaWx0ZXJlZE9wdGlvbnM6IE9wdGlvbltdID0gW107XG5cbiAgICAgICAgaWYgKCF2YWx1ZXMgfHwgIXZhbHVlcy5sZW5ndGgpIHtcbiAgICAgICAgICAgIHJldHVybiBbXTtcbiAgICAgICAgfVxuXG4gICAgICAgIHZhbHVlcy5mb3JFYWNoKHZhbHVlID0+IHtcbiAgICAgICAgICAgIGlmICghdGhpcy5tYXBwZWRPcHRpb25zW3ZhbHVlXSkge1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIGZpbHRlcmVkT3B0aW9ucyA9IGZpbHRlcmVkT3B0aW9ucy5jb25jYXQoWy4uLnRoaXMubWFwcGVkT3B0aW9uc1t2YWx1ZV1dKTtcbiAgICAgICAgfSk7XG5cbiAgICAgICAgcmV0dXJuIGZpbHRlcmVkT3B0aW9ucztcbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgY3JlYXRlUGFyZW50Q2hpbGRPcHRpb25zTWFwKHBhcmVudE9wdGlvbnM6IExhbmd1YWdlU3RyaW5nTWFwLCBjaGlsZE9wdGlvbnM6IE9wdGlvbltdKTogeyBba2V5OiBzdHJpbmddOiBPcHRpb25bXSB9IHtcbiAgICAgICAgY29uc3QgbWFwcGVkT3B0aW9uczogeyBba2V5OiBzdHJpbmddOiBPcHRpb25bXSB9ID0ge307XG4gICAgICAgIE9iamVjdC5rZXlzKHBhcmVudE9wdGlvbnMpLmZvckVhY2goa2V5ID0+IHtcbiAgICAgICAgICAgIG1hcHBlZE9wdGlvbnNba2V5XSA9IGNoaWxkT3B0aW9ucy5maWx0ZXIoXG4gICAgICAgICAgICAgICAgb3B0aW9uID0+IFN0cmluZyhvcHRpb24udmFsdWUpLnN0YXJ0c1dpdGgoa2V5KVxuICAgICAgICAgICAgKTtcbiAgICAgICAgfSk7XG4gICAgICAgIHJldHVybiBtYXBwZWRPcHRpb25zO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBzdWJzY3JpYmVUb1BhcmVudFZhbHVlQ2hhbmdlcyhwYXJlbnRFbnVtOiBGaWVsZCk6IHZvaWQge1xuICAgICAgICBpZiAocGFyZW50RW51bS5mb3JtQ29udHJvbCkge1xuICAgICAgICAgICAgdGhpcy5zdWJzLnB1c2gocGFyZW50RW51bS5mb3JtQ29udHJvbC52YWx1ZUNoYW5nZXMuc3Vic2NyaWJlKHZhbHVlcyA9PiB7XG5cbiAgICAgICAgICAgICAgICBpZiAodHlwZW9mIHZhbHVlcyA9PT0gJ3N0cmluZycpIHtcbiAgICAgICAgICAgICAgICAgICAgdmFsdWVzID0gW3ZhbHVlc107XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgLy8gUmVidWlsZCBhdmFpbGFibGUgZW51bSBvcHRpb25zXG4gICAgICAgICAgICAgICAgdGhpcy5vcHRpb25zID0gdGhpcy5maWx0ZXJNYXRjaGluZ09wdGlvbnModmFsdWVzKTtcbiAgICAgICAgICAgICAgICB0aGlzLnNldFZhbHVlVG9BdmFpbGFibGVPcHRpb24oKTtcblxuICAgICAgICAgICAgICAgIHRoaXMuaW5pdFZhbHVlKCk7XG4gICAgICAgICAgICB9KSk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgc2V0VmFsdWVUb0F2YWlsYWJsZU9wdGlvbigpOiB2b2lkIHtcbiAgICAgICAgaWYgKCF0aGlzPy5vcHRpb25zPy5sZW5ndGgpIHtcbiAgICAgICAgICAgIHRoaXMuZmllbGQudmFsdWUgPSAnJztcbiAgICAgICAgICAgIHRoaXMuZmllbGQuZm9ybUNvbnRyb2wuc2V0VmFsdWUoJycpO1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCF0aGlzLm9wdGlvbnMuc29tZShvcHRpb24gPT4gb3B0aW9uLnZhbHVlID09PSB0aGlzLmZpZWxkLnZhbHVlKSkge1xuICAgICAgICAgICAgdGhpcy5maWVsZC52YWx1ZSA9IHRoaXMub3B0aW9uc1swXS52YWx1ZTtcbiAgICAgICAgICAgIHRoaXMuZmllbGQuZm9ybUNvbnRyb2wuc2V0VmFsdWUodGhpcy5vcHRpb25zWzBdLnZhbHVlKTtcbiAgICAgICAgfVxuICAgIH1cblxuICAgIHByb3RlY3RlZCBidWlsZE9wdGlvbkZyb21WYWx1ZSh2YWx1ZTogc3RyaW5nKTogT3B0aW9uIHtcbiAgICAgICAgY29uc3Qgb3B0aW9uOiBPcHRpb24gPSB7dmFsdWU6ICcnLCBsYWJlbDogJyd9O1xuXG4gICAgICAgIGlmIChpc051bGwodmFsdWUpKSB7XG4gICAgICAgICAgICByZXR1cm4gb3B0aW9uO1xuICAgICAgICB9XG4gICAgICAgIG9wdGlvbi52YWx1ZSA9ICh0eXBlb2YgdmFsdWUgIT09ICdzdHJpbmcnID8gSlNPTi5zdHJpbmdpZnkodmFsdWUpIDogdmFsdWUpLnRyaW0oKTtcbiAgICAgICAgb3B0aW9uLmxhYmVsID0gb3B0aW9uLnZhbHVlO1xuXG4gICAgICAgIGNvbnN0IHZhbHVlTGFiZWwgPSB0aGlzLm9wdGlvbnNNYXBbb3B0aW9uLnZhbHVlXSA/PyBvcHRpb24ubGFiZWw7XG4gICAgICAgIGlmIChpc09iamVjdCh2YWx1ZUxhYmVsKSkge1xuICAgICAgICAgICAgcmV0dXJuIG9wdGlvbjtcbiAgICAgICAgfVxuICAgICAgICBvcHRpb24ubGFiZWwgPSAodHlwZW9mIHZhbHVlTGFiZWwgIT09ICdzdHJpbmcnID8gSlNPTi5zdHJpbmdpZnkodmFsdWVMYWJlbCkgOiB2YWx1ZUxhYmVsKS50cmltKCk7XG5cbiAgICAgICAgcmV0dXJuIG9wdGlvbjtcbiAgICB9XG5cbn1cbiJdfQ==