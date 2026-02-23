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
import { Component, inject, Input } from '@angular/core';
import { isVoid } from 'common';
import { BehaviorSubject } from 'rxjs';
import { DataTypeFormatter } from '../../services/formatters/data-type.formatter.service';
import { debounceTime } from 'rxjs/operators';
import { FieldLogicManager } from '../field-logic/field-logic.manager';
import { FieldLogicDisplayManager } from '../field-logic-display/field-logic-display.manager';
import { isEqual } from "lodash-es";
import { FieldHandlerRegistry } from "../../services/record/field/handler/field-handler.registry";
import * as i0 from "@angular/core";
import * as i1 from "../../services/formatters/data-type.formatter.service";
import * as i2 from "../field-logic/field-logic.manager";
import * as i3 from "../field-logic-display/field-logic-display.manager";
class BaseFieldComponent {
    get mode() {
        return this._mode;
    }
    set mode(value) {
        this._mode = value;
        this.modeState.next(this._mode);
    }
    constructor(typeFormatter, logic, logicDisplay) {
        this.typeFormatter = typeFormatter;
        this.logic = logic;
        this.logicDisplay = logicDisplay;
        this.originalMode = '';
        this.klass = null;
        this._mode = '';
        this.dependentFields = {};
        this.dependentAttributes = [];
        this.subs = [];
        this.modeState = new BehaviorSubject('');
        this.mode$ = this.modeState.asObservable();
        this.fieldHandlerRegistry = inject(FieldHandlerRegistry);
    }
    ngOnInit() {
        this.baseInit();
        if (!this.originalMode) {
            this.originalMode = this.mode;
        }
        const defaultValueModes = this?.field?.defaultValueModes ?? [];
        if (defaultValueModes.includes(this.originalMode)) {
            const fieldHandler = this.fieldHandlerRegistry.get(this.record.module, this.field.type);
            fieldHandler.initDefaultValue(this.field, this.record);
        }
    }
    ngOnDestroy() {
        this.unsubscribeAll();
    }
    baseInit() {
        this.initDependencyHandlers();
    }
    /**
     * Calculate and init dependency handlers
     */
    initDependencyHandlers() {
        if (!this.record) {
            return;
        }
        const fieldKeys = (this.record.fields && Object.keys(this.record.fields)) || [];
        if (fieldKeys.length > 1) {
            this.calculateDependentFields(fieldKeys);
            this.field.previousValue = this.field.value;
            if ((this.dependentFields && Object.keys(this.dependentFields).length) || this.dependentAttributes.length) {
                Object.keys(this.dependentFields).forEach(fieldKey => {
                    const field = this.record.fields[fieldKey] || null;
                    if (!field) {
                        return;
                    }
                    const types = this.dependentFields[fieldKey].type ?? [];
                    if (types.includes('logic')) {
                        this.logic.runLogic(field, this.mode, this.record, 'onFieldInitialize');
                    }
                    if (types.includes('displayLogic')) {
                        this.logicDisplay.runAll(field, this.record, this.mode);
                    }
                });
            }
            if (this.field.valueChanges$ && ((this.dependentFields && Object.keys(this.dependentFields).length) || this.dependentAttributes.length)) {
                this.subs.push(this.field.valueChanges$.pipe(debounceTime(500)).subscribe((data) => {
                    Object.keys(this.dependentFields).forEach(fieldKey => {
                        const dependentField = this.dependentFields[fieldKey];
                        const field = this.record.fields[fieldKey] || null;
                        if (!field) {
                            return;
                        }
                        if (this.field.previousValue != data.value) {
                            const types = dependentField.type ?? [];
                            if (types.includes('logic')) {
                                this.logic.runLogic(field, this.mode, this.record, 'onValueChange');
                            }
                            if (types.includes('displayLogic')) {
                                this.logicDisplay.runAll(field, this.record, this.mode);
                            }
                        }
                    });
                    this.field.previousValue = data.value;
                    this.dependentAttributes.forEach(dependency => {
                        const field = this.record.fields[dependency.field] || {};
                        const attribute = (field && field.attributes && field.attributes[dependency.attribute]) || null;
                        if (!attribute) {
                            return;
                        }
                        this.logic.runLogic(attribute, this.mode, this.record, 'onValueChange');
                    });
                }));
            }
        }
    }
    /**
     * Calculate dependent fields
     * @param {array} fieldKeys
     */
    calculateDependentFields(fieldKeys) {
        fieldKeys.forEach(key => {
            if (this.field.source === 'field' || this.field.source === 'groupField') {
                this.addFieldDependency(key, this.dependentFields, this.dependentAttributes);
                return;
            }
            if (this.field.source === 'attribute') {
                this.addAttributeDependency(key, this.dependentFields, this.dependentAttributes);
                return;
            }
        });
    }
    /**
     * Add field dependency
     * @param {string} fieldKey
     * @param {array} dependentFields
     * @param {object} dependentAttributes
     */
    addFieldDependency(fieldKey, dependentFields, dependentAttributes) {
        const field = this.record.fields[fieldKey];
        const name = this.field.name || this.field.definition.name || '';
        if (fieldKey === name || !field) {
            return;
        }
        if (field.fieldDependencies && this.isDependencyField(field.fieldDependencies)) {
            dependentFields[fieldKey] = field.fieldDependencies[name];
        }
        const attributeKeys = (field.attributes && Object.keys(field.attributes)) || [];
        attributeKeys.forEach(attributeKey => {
            const attribute = field.attributes[attributeKey];
            if (!attribute || !attribute.fieldDependencies || !attribute.fieldDependencies.length) {
                return;
            }
            if (this.isDependencyField(attribute.fieldDependencies)) {
                dependentAttributes.push({
                    field: fieldKey,
                    attribute: attributeKey,
                    types: dependentFields[name]['types'] ?? []
                });
            }
        });
    }
    /**
     * Check if field is dependency
     * @param dependencies
     * @returns {boolean}
     */
    isDependencyField(dependencies) {
        const name = this.field.name || this.field.definition.name || '';
        return !!(dependencies[name] ?? false);
    }
    /**
     * Add attribute dependency
     * @param {string} fieldKey
     * @param {array} dependentFields
     * @param {object} dependentAttributes
     */
    addAttributeDependency(fieldKey, dependentFields, dependentAttributes) {
        const field = this.record.fields[fieldKey];
        const name = this.field.name || this.field.definition.name || '';
        if (fieldKey === name || !field) {
            return;
        }
        if (field.attributeDependencies && field.attributeDependencies.length && this.isDependencyAttribute(field.attributeDependencies)) {
            dependentFields[name] = field.fieldDependencies[name];
        }
        const attributeKeys = (field.attributes && Object.keys(field.attributes)) || [];
        attributeKeys.forEach(attributeKey => {
            const attribute = field.attributes[attributeKey];
            if (!attribute || !attribute.attributeDependencies || !attribute.attributeDependencies.length) {
                return;
            }
            if (this.isDependencyAttribute(attribute.attributeDependencies)) {
                dependentAttributes.push({
                    field: fieldKey,
                    attribute: attributeKey,
                    types: (dependentFields[name] ?? {})['types'] ?? [],
                });
            }
        });
    }
    /**
     * Check if attribute is dependency
     * @param {object} attributeDependencies
     * @returns {boolean}
     */
    isDependencyAttribute(attributeDependencies) {
        const parentKey = this.field.parentKey || '';
        const name = this.field.name || this.field.definition.name || '';
        return attributeDependencies.some(dependency => parentKey === dependency.field && name === dependency.attribute);
    }
    subscribeValueChanges() {
        if (this.field && this.field.formControl) {
            this.subs.push(this.field.formControl.valueChanges.subscribe(value => {
                if (!isVoid(value)) {
                    value = value.trim();
                }
                else {
                    value = '';
                }
                if (this.typeFormatter && this.field.type) {
                    value = this.toInternalFormat(this.field.type, value);
                }
                this.setFieldValue(value);
            }));
        }
    }
    toInternalFormat(fieldType, value) {
        return this.typeFormatter.toInternalFormat(fieldType, value);
    }
    setFieldValue(newValue) {
        this.field.value = newValue;
    }
    setFormControlValue(newValue) {
        this.field.formControl.markAsDirty();
        if (isEqual(this.field.formControl.value, newValue)) {
            return;
        }
        this.field.formControl.setValue(newValue);
    }
    unsubscribeAll() {
        this.subs.forEach(sub => sub.unsubscribe());
    }
    static { this.ɵfac = function BaseFieldComponent_Factory(t) { return new (t || BaseFieldComponent)(i0.ɵɵdirectiveInject(i1.DataTypeFormatter), i0.ɵɵdirectiveInject(i2.FieldLogicManager), i0.ɵɵdirectiveInject(i3.FieldLogicDisplayManager)); }; }
    static { this.ɵcmp = /*@__PURE__*/ i0.ɵɵdefineComponent({ type: BaseFieldComponent, selectors: [["ng-component"]], inputs: { originalMode: "originalMode", field: "field", record: "record", parent: "parent", klass: "klass", mode: "mode" }, decls: 0, vars: 0, template: function BaseFieldComponent_Template(rf, ctx) { }, encapsulation: 2 }); }
}
export { BaseFieldComponent };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(BaseFieldComponent, [{
        type: Component,
        args: [{ template: '' }]
    }], function () { return [{ type: i1.DataTypeFormatter }, { type: i2.FieldLogicManager }, { type: i3.FieldLogicDisplayManager }]; }, { originalMode: [{
            type: Input
        }], field: [{
            type: Input
        }], record: [{
            type: Input
        }], parent: [{
            type: Input
        }], klass: [{
            type: Input
        }], mode: [{
            type: Input
        }] }); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYmFzZS1maWVsZC5jb21wb25lbnQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvZmllbGRzL2Jhc2UvYmFzZS1maWVsZC5jb21wb25lbnQudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQXdCRztBQUVILE9BQU8sRUFBQyxTQUFTLEVBQUUsTUFBTSxFQUFFLEtBQUssRUFBb0IsTUFBTSxlQUFlLENBQUM7QUFFMUUsT0FBTyxFQUE2QixNQUFNLEVBQThCLE1BQU0sUUFBUSxDQUFDO0FBQ3ZGLE9BQU8sRUFBQyxlQUFlLEVBQTJCLE1BQU0sTUFBTSxDQUFDO0FBQy9ELE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLHVEQUF1RCxDQUFDO0FBQ3hGLE9BQU8sRUFBQyxZQUFZLEVBQUMsTUFBTSxnQkFBZ0IsQ0FBQztBQUM1QyxPQUFPLEVBQUMsaUJBQWlCLEVBQUMsTUFBTSxvQ0FBb0MsQ0FBQztBQUNyRSxPQUFPLEVBQUMsd0JBQXdCLEVBQUMsTUFBTSxvREFBb0QsQ0FBQztBQUM1RixPQUFPLEVBQUMsT0FBTyxFQUFDLE1BQU0sV0FBVyxDQUFDO0FBQ2xDLE9BQU8sRUFBQyxvQkFBb0IsRUFBQyxNQUFNLDREQUE0RCxDQUFDOzs7OztBQUVoRyxNQUNhLGtCQUFrQjtJQVEzQixJQUNXLElBQUk7UUFDWCxPQUFPLElBQUksQ0FBQyxLQUFLLENBQUM7SUFDdEIsQ0FBQztJQUVELElBQVcsSUFBSSxDQUFDLEtBQWE7UUFDekIsSUFBSSxDQUFDLEtBQUssR0FBRyxLQUFLLENBQUM7UUFDbkIsSUFBSSxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLEtBQUssQ0FBQyxDQUFDO0lBQ3BDLENBQUM7SUFVRCxZQUNjLGFBQWdDLEVBQ2hDLEtBQXdCLEVBQ3hCLFlBQXNDO1FBRnRDLGtCQUFhLEdBQWIsYUFBYSxDQUFtQjtR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QUksQ0FBQyxhQUFhLENBQUMsZ0JBQWdCLENBQUMsU0FBUyxFQUFFLEtBQUssQ0FBQyxDQUFDO0lBRWpFLENBQUM7SUFFUyxhQUFhLENBQUMsUUFBUTtRQUM1QixJQUFJLENBQUMsS0FBSyxDQUFDLEtBQUssR0FBRyxRQUFRLENBQUM7SUFDaEMsQ0FBQztJQUVTLG1CQUFtQixDQUFDLFFBQTJCO1FBQ3JELElBQUksQ0FBQyxLQUFLLENBQUMsV0FBVyxDQUFDLFdBQVcsRUFBRSxDQUFDO1FBRXJDLElBQUksT0FBTyxDQUFDLElBQUksQ0FBQyxLQUFLLENBQUMsV0FBVyxDQUFDLEtBQUssRUFBRSxRQUFRLENBQUMsRUFBRTtZQUNqRCxPQUFPO1NBQ1Y7UUFDRCxJQUFJLENBQUMsS0FBSyxDQUFDLFdBQVcsQ0FBQyxRQUFRLENBQUMsUUFBUSxDQUFDLENBQUM7SUFDOUMsQ0FBQztJQUVTLGNBQWM7UUFDcEIsSUFBSSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUUsQ0FBQyxHQUFHLENBQUMsV0FBVyxFQUFFLENBQUMsQ0FBQztJQUNoRCxDQUFDO21GQTVSUSxrQkFBa0I7b0VBQWxCLGtCQUFrQjs7U0FBbEIsa0JBQWtCO3VGQUFsQixrQkFBa0I7Y0FEOUIsU0FBUztlQUFDLEVBQUMsUUFBUSxFQUFFLEVBQUUsRUFBQzsySUFHWixZQUFZO2tCQUFwQixLQUFLO1lBQ0csS0FBSztrQkFBYixLQUFLO1lBQ0csTUFBTTtrQkFBZCxLQUFLO1lBQ0csTUFBTTtrQkFBZCxLQUFLO1lBQ0csS0FBSztrQkFBYixLQUFLO1lBR0ssSUFBSTtrQkFEZCxLQUFLIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuXG5pbXBvcnQge0NvbXBvbmVudCwgaW5qZWN0LCBJbnB1dCwgT25EZXN0cm95LCBPbkluaXR9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHtGaWVsZENvbXBvbmVudEludGVyZmFjZX0gZnJvbSAnLi9maWVsZC5pbnRlcmZhY2UnO1xuaW1wb3J0IHtBdHRyaWJ1dGVEZXBlbmRlbmN5LCBGaWVsZCwgaXNWb2lkLCBPYmplY3RNYXAsIFJlY29yZCwgVmlld01vZGV9IGZyb20gJ2NvbW1vbic7XG5pbXBvcnQge0JlaGF2aW9yU3ViamVjdCwgT2JzZXJ2YWJsZSwgU3Vic2NyaXB0aW9ufSBmcm9tICdyeGpzJztcbmltcG9ydCB7RGF0YVR5cGVGb3JtYXR0ZXJ9IGZyb20gJy4uLy4uL3NlcnZpY2VzL2Zvcm1hdHRlcnMvZGF0YS10eXBlLmZvcm1hdHRlci5zZXJ2aWNlJztcbmltcG9ydCB7ZGVib3VuY2VUaW1lfSBmcm9tICdyeGpzL29wZXJhdG9ycyc7XG5pbXBvcnQge0ZpZWxkTG9naWNNYW5hZ2VyfSBmcm9tICcuLi9maWVsZC1sb2dpYy9maWVsZC1sb2dpYy5tYW5hZ2VyJztcbmltcG9ydCB7RmllbGRMb2dpY0Rpc3BsYXlNYW5hZ2VyfSBmcm9tICcuLi9maWVsZC1sb2dpYy1kaXNwbGF5L2ZpZWxkLWxvZ2ljLWRpc3BsYXkubWFuYWdlcic7XG5pbXBvcnQge2lzRXF1YWx9IGZyb20gXCJsb2Rhc2gtZXNcIjtcbmltcG9ydCB7RmllbGRIYW5kbGVyUmVnaXN0cnl9IGZyb20gXCIuLi8uLi9zZXJ2aWNlcy9yZWNvcmQvZmllbGQvaGFuZGxlci9maWVsZC1oYW5kbGVyLnJlZ2lzdHJ5XCI7XG5cbkBDb21wb25lbnQoe3RlbXBsYXRlOiAnJ30pXG5leHBvcnQgY2xhc3MgQmFzZUZpZWxkQ29tcG9uZW50IGltcGxlbWVudHMgRmllbGRDb21wb25lbnRJbnRlcmZhY2UsIE9uSW5pdCwgT25EZXN0cm95IHtcblxuICAgIEBJbnB1dCgpIG9yaWdpbmFsTW9kZTogc3RyaW5nID0gJyc7XG4gICAgQElucHV0KCkgZmllbGQ6IEZpZWxkO1xuICAgIEBJbnB1dCgpIHJlY29yZDogUmVjb3JkO1xuICAgIEBJbnB1dCgpIHBhcmVudDogUmVjb3JkO1xuICAgIEBJbnB1dCgpIGtsYXNzOiB7IFtrbGFzczogc3RyaW5nXTogYW55IH0gPSBudWxsO1xuXG4gICAgQElucHV0KClcbiAgICBwdWJsaWMgZ2V0IG1vZGUoKTogc3RyaW5nIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuX21vZGU7XG4gICAgfVxuXG4gICAgcHVibGljIHNldCBtb2RlKHZhbHVlOiBzdHJpbmcpIHtcbiAgICAgICAgdGhpcy5fbW9kZSA9IHZhbHVlO1xuICAgICAgICB0aGlzLm1vZGVTdGF0ZS5uZXh0KHRoaXMuX21vZGUpO1xuICAgIH1cblxuICAgIF9tb2RlOiBzdHJpbmcgPSAnJztcbiAgICBkZXBlbmRlbnRGaWVsZHM6IE9iamVjdE1hcCA9IHt9O1xuICAgIGRlcGVuZGVudEF0dHJpYnV0ZXM6IEF0dHJpYnV0ZURlcGVuZGVuY3lbXSA9IFtdO1xuICAgIHByb3RlY3RlZCBzdWJzOiBTdWJzY3JpcHRpb25bXSA9IFtdO1xuICAgIHByb3RlY3RlZCBtb2RlU3RhdGU6IEJlaGF2aW9yU3ViamVjdDxzdHJpbmc+O1xuICAgIHByb3RlY3RlZCBtb2RlJDogT2JzZXJ2YWJsZTxzdHJpbmc+O1xuICAgIHByb3RlY3RlZCBmaWVsZEhhbmRsZXJSZWdpc3RyeTogRmllbGRIYW5kbGVyUmVnaXN0cnk7XG5cbiAgICBjb25zdHJ1Y3RvcihcbiAgICAgICAgcHJvdGVjdGVkIHR5cGVGb3JtYXR0ZXI6IERhdGFUeXBlRm9ybWF0dGVyLFxuICAgICAgICBwcm90ZWN0ZWQgbG9naWM6IEZpZWxkTG9naWNNYW5hZ2VyLFxuICAgICAgICBwcm90ZWN0ZWQgbG9naWNEaXNwbGF5OiBGaWVsZExvZ2ljRGlzcGxheU1hbmFnZXJcbiAgICApIHtcbiAgICAgICAgdGhpcy5tb2RlU3RhdGUgPSBuZXcgQmVoYXZpb3JTdWJqZWN0PHN0cmluZz4oJycpO1xuICAgICAgICB0aGlzLm1vZGUkID0gdGhpcy5tb2RlU3RhdGUuYXNPYnNlcnZhYmxlKCk7XG4gICAgICAgIHRoaXMuZmllbGRIYW5kbGVyUmVnaXN0cnkgPSBpbmplY3QoRmllbGRIYW5kbGVyUmVnaXN0cnkpXG4gICAgfVxuXG4gICAgbmdPbkluaXQoKTogdm9pZCB7XG4gICAgICAgIHRoaXMuYmFzZUluaXQoKTtcblxuICAgICAgICBpZiAoIXRoaXMub3JpZ2luYWxNb2RlKSB7XG4gICAgICAgICAgICB0aGlzLm9yaWdpbmFsTW9kZSA9IHRoaXMubW9kZTtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IGRlZmF1bHRWYWx1ZU1vZGVzID0gdGhpcz8uZmllbGQ/LmRlZmF1bHRWYWx1ZU1vZGVzID8/IFtdO1xuICAgICAgICBpZiAoZGVmYXVsdFZhbHVlTW9kZXMuaW5jbHVkZXModGhpcy5vcmlnaW5hbE1vZGUgYXMgVmlld01vZGUpKSB7XG4gICAgICAgICAgICBjb25zdCBmaWVsZEhhbmRsZXIgPSB0aGlzLmZpZWxkSGFuZGxlclJlZ2lzdHJ5LmdldCh0aGlzLnJlY29yZC5tb2R1bGUsIHRoaXMuZmllbGQudHlwZSk7XG4gICAgICAgICAgICBmaWVsZEhhbmRsZXIuaW5pdERlZmF1bHRWYWx1ZSh0aGlzLmZpZWxkLCB0aGlzLnJlY29yZCk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBuZ09uRGVzdHJveSgpOiB2b2lkIHtcbiAgICAgICAgdGhpcy51bnN1YnNjcmliZUFsbCgpO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBiYXNlSW5pdCgpOiB2b2lkIHtcbiAgICAgICAgdGhpcy5pbml0RGVwZW5kZW5jeUhhbmRsZXJzKCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQ2FsY3VsYXRlIGFuZCBpbml0IGRlcGVuZGVuY3kgaGFuZGxlcnNcbiAgICAgKi9cbiAgICBwcm90ZWN0ZWQgaW5pdERlcGVuZGVuY3lIYW5kbGVycygpOiB2b2lkIHtcbiAgICAgICAgaWYgKCF0aGlzLnJlY29yZCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICAgIGNvbnN0IGZpZWxkS2V5cyA9ICh0aGlzLnJlY29yZC5maWVsZHMgJiYgT2JqZWN0LmtleXModGhpcy5yZWNvcmQuZmllbGRzKSkgfHwgW107XG4gICAgICAgIGlmIChmaWVsZEtleXMubGVuZ3RoID4gMSkge1xuICAgICAgICAgICAgdGhpcy5jYWxjdWxhdGVEZXBlbmRlbnRGaWVsZHMoZmllbGRLZXlzKTtcbiAgICAgICAgICAgIHRoaXMuZmllbGQucHJldmlvdXNWYWx1ZSA9IHRoaXMuZmllbGQudmFsdWU7XG5cbiAgICAgICAgICAgIGlmICgodGhpcy5kZXBlbmRlbnRGaWVsZHMgJiYgT2JqZWN0LmtleXModGhpcy5kZXBlbmRlbnRGaWVsZHMpLmxlbmd0aCkgfHwgdGhpcy5kZXBlbmRlbnRBdHRyaWJ1dGVzLmxlbmd0aCkge1xuICAgICAgICAgICAgICAgIE9iamVjdC5rZXlzKHRoaXMuZGVwZW5kZW50RmllbGRzKS5mb3JFYWNoKGZpZWxkS2V5ID0+IHtcbiAgICAgICAgICAgICAgICAgICAgY29uc3QgZmllbGQgPSB0aGlzLnJlY29yZC5maWVsZHNbZmllbGRLZXldIHx8IG51bGw7XG4gICAgICAgICAgICAgICAgICAgIGlmICghZmllbGQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgIGNvbnN0IHR5cGVzID0gdGhpcy5kZXBlbmRlbnRGaWVsZHNbZmllbGRLZXldLnR5cGUgPz8gW107XG5cbiAgICAgICAgICAgICAgICAgICAgaWYgKHR5cGVzLmluY2x1ZGVzKCdsb2dpYycpKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmxvZ2ljLnJ1bkxvZ2ljKGZpZWxkLCB0aGlzLm1vZGUgYXMgVmlld01vZGUsIHRoaXMucmVjb3JkLCAnb25GaWVsZEluaXRpYWxpemUnKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgIGlmICh0eXBlcy5pbmNsdWRlcygnZGlzcGxheUxvZ2ljJykpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMubG9naWNEaXNwbGF5LnJ1bkFsbChmaWVsZCwgdGhpcy5yZWNvcmQsIHRoaXMubW9kZSBhcyBWaWV3TW9kZSk7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgaWYgKHRoaXMuZmllbGQudmFsdWVDaGFuZ2VzJCAmJiAoKHRoaXMuZGVwZW5kZW50RmllbGRzICYmIE9iamVjdC5rZXlzKHRoaXMuZGVwZW5kZW50RmllbGRzKS5sZW5ndGgpIHx8IHRoaXMuZGVwZW5kZW50QXR0cmlidXRlcy5sZW5ndGgpKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5zdWJzLnB1c2godGhpcy5maWVsZC52YWx1ZUNoYW5nZXMkLnBpcGUoZGVib3VuY2VUaW1lKDUwMCkpLnN1YnNjcmliZSgoZGF0YSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBPYmplY3Qua2V5cyh0aGlzLmRlcGVuZGVudEZpZWxkcykuZm9yRWFjaChmaWVsZEtleSA9PiB7XG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zdCBkZXBlbmRlbnRGaWVsZCA9IHRoaXMuZGVwZW5kZW50RmllbGRzW2ZpZWxkS2V5XTtcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbnN0IGZpZWxkID0gdGhpcy5yZWNvcmQuZmllbGRzW2ZpZWxkS2V5XSB8fCBudWxsO1xuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCFmaWVsZCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHRoaXMuZmllbGQucHJldmlvdXNWYWx1ZSAhPSBkYXRhLnZhbHVlKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgdHlwZXMgPSBkZXBlbmRlbnRGaWVsZC50eXBlID8/IFtdO1xuXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHR5cGVzLmluY2x1ZGVzKCdsb2dpYycpKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMubG9naWMucnVuTG9naWMoZmllbGQsIHRoaXMubW9kZSBhcyBWaWV3TW9kZSwgdGhpcy5yZWNvcmQsICdvblZhbHVlQ2hhbmdlJyk7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHR5cGVzLmluY2x1ZGVzKCdkaXNwbGF5TG9naWMnKSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmxvZ2ljRGlzcGxheS5ydW5BbGwoZmllbGQsIHRoaXMucmVjb3JkLCB0aGlzLm1vZGUgYXMgVmlld01vZGUpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuZmllbGQucHJldmlvdXNWYWx1ZSA9IGRhdGEudmFsdWU7XG5cbiAgICAgICAgICAgICAgICAgICAgdGhpcy5kZXBlbmRlbnRBdHRyaWJ1dGVzLmZvckVhY2goZGVwZW5kZW5jeSA9PiB7XG4gICAgICAgICAgICAgICAgICAgICAgICBjb25zdCBmaWVsZCA9IHRoaXMucmVjb3JkLmZpZWxkc1tkZXBlbmRlbmN5LmZpZWxkXSB8fCB7fSBhcyBGaWVsZDtcbiAgICAgICAgICAgICAgICAgICAgICAgIGNvbnN0IGF0dHJpYnV0ZSA9IChmaWVsZCAmJiBmaWVsZC5hdHRyaWJ1dGVzICYmIGZpZWxkLmF0dHJpYnV0ZXNbZGVwZW5kZW5jeS5hdHRyaWJ1dGVdKSB8fCBudWxsO1xuXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAoIWF0dHJpYnV0ZSkge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5sb2dpYy5ydW5Mb2dpYyhhdHRyaWJ1dGUsIHRoaXMubW9kZSBhcyBWaWV3TW9kZSwgdGhpcy5yZWNvcmQsICdvblZhbHVlQ2hhbmdlJyk7XG4gICAgICAgICAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICAgICAgfSkpO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgIH1cbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDYWxjdWxhdGUgZGVwZW5kZW50IGZpZWxkc1xuICAgICAqIEBwYXJhbSB7YXJyYXl9IGZpZWxkS2V5c1xuICAgICAqL1xuICAgIHByb3RlY3RlZCBjYWxjdWxhdGVEZXBlbmRlbnRGaWVsZHMoZmllbGRLZXlzOiBzdHJpbmdbXSk6IHZvaWQge1xuICAgICAgICBmaWVsZEtleXMuZm9yRWFjaChrZXkgPT4ge1xuXG4gICAgICAgICAgICBpZiAodGhpcy5maWVsZC5zb3VyY2UgPT09ICdmaWVsZCcgfHwgdGhpcy5maWVsZC5zb3VyY2UgPT09ICdncm91cEZpZWxkJykge1xuICAgICAgICAgICAgICAgIHRoaXMuYWRkRmllbGREZXBlbmRlbmN5KGtleSwgdGhpcy5kZXBlbmRlbnRGaWVsZHMsIHRoaXMuZGVwZW5kZW50QXR0cmlidXRlcyk7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBpZiAodGhpcy5maWVsZC5zb3VyY2UgPT09ICdhdHRyaWJ1dGUnKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5hZGRBdHRyaWJ1dGVEZXBlbmRlbmN5KGtleSwgdGhpcy5kZXBlbmRlbnRGaWVsZHMsIHRoaXMuZGVwZW5kZW50QXR0cmlidXRlcyk7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEFkZCBmaWVsZCBkZXBlbmRlbmN5XG4gICAgICogQHBhcmFtIHtzdHJpbmd9IGZpZWxkS2V5XG4gICAgICogQHBhcmFtIHthcnJheX0gZGVwZW5kZW50RmllbGRzXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGRlcGVuZGVudEF0dHJpYnV0ZXNcbiAgICAgKi9cbiAgICBwcm90ZWN0ZWQgYWRkRmllbGREZXBlbmRlbmN5KGZpZWxkS2V5OiBzdHJpbmcsIGRlcGVuZGVudEZpZWxkczogT2JqZWN0TWFwLCBkZXBlbmRlbnRBdHRyaWJ1dGVzOiBBdHRyaWJ1dGVEZXBlbmRlbmN5W10pOiB2b2lkIHtcbiAgICAgICAgY29uc3QgZmllbGQgPSB0aGlzLnJlY29yZC5maWVsZHNbZmllbGRLZXldO1xuICAgICAgICBjb25zdCBuYW1lID0gdGhpcy5maWVsZC5uYW1lIHx8IHRoaXMuZmllbGQuZGVmaW5pdGlvbi5uYW1lIHx8ICcnO1xuICAgICAgICBpZiAoZmllbGRLZXkgPT09IG5hbWUgfHwgIWZpZWxkKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoZmllbGQuZmllbGREZXBlbmRlbmNpZXMgJiYgdGhpcy5pc0RlcGVuZGVuY3lGaWVsZChmaWVsZC5maWVsZERlcGVuZGVuY2llcykpIHtcbiAgICAgICAgICAgIGRlcGVuZGVudEZpZWxkc1tmaWVsZEtleV0gPSBmaWVsZC5maWVsZERlcGVuZGVuY2llc1tuYW1lXTtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IGF0dHJpYnV0ZUtleXMgPSAoZmllbGQuYXR0cmlidXRlcyAmJiBPYmplY3Qua2V5cyhmaWVsZC5hdHRyaWJ1dGVzKSkgfHwgW107XG5cbiAgICAgICAgYXR0cmlidXRlS2V5cy5mb3JFYWNoKGF0dHJpYnV0ZUtleSA9PiB7XG5cbiAgICAgICAgICAgIGNvbnN0IGF0dHJpYnV0ZSA9IGZpZWxkLmF0dHJpYnV0ZXNbYXR0cmlidXRlS2V5XTtcbiAgICAgICAgICAgIGlmICghYXR0cmlidXRlIHx8ICFhdHRyaWJ1dGUuZmllbGREZXBlbmRlbmNpZXMgfHwgIWF0dHJpYnV0ZS5maWVsZERlcGVuZGVuY2llcy5sZW5ndGgpIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGlmICh0aGlzLmlzRGVwZW5kZW5jeUZpZWxkKGF0dHJpYnV0ZS5maWVsZERlcGVuZGVuY2llcykpIHtcbiAgICAgICAgICAgICAgICBkZXBlbmRlbnRBdHRyaWJ1dGVzLnB1c2goe1xuICAgICAgICAgICAgICAgICAgICBmaWVsZDogZmllbGRLZXksXG4gICAgICAgICAgICAgICAgICAgIGF0dHJpYnV0ZTogYXR0cmlidXRlS2V5LFxuICAgICAgICAgICAgICAgICAgICB0eXBlczogZGVwZW5kZW50RmllbGRzW25hbWVdWyd0eXBlcyddID8/IFtdXG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENoZWNrIGlmIGZpZWxkIGlzIGRlcGVuZGVuY3lcbiAgICAgKiBAcGFyYW0gZGVwZW5kZW5jaWVzXG4gICAgICogQHJldHVybnMge2Jvb2xlYW59XG4gICAgICovXG4gICAgcHJvdGVjdGVkIGlzRGVwZW5kZW5jeUZpZWxkKGRlcGVuZGVuY2llczogT2JqZWN0TWFwKTogYm9vbGVhbiB7XG4gICAgICAgIGNvbnN0IG5hbWUgPSB0aGlzLmZpZWxkLm5hbWUgfHwgdGhpcy5maWVsZC5kZWZpbml0aW9uLm5hbWUgfHwgJyc7XG5cbiAgICAgICAgcmV0dXJuICEhKGRlcGVuZGVuY2llc1tuYW1lXSA/PyBmYWxzZSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQWRkIGF0dHJpYnV0ZSBkZXBlbmRlbmN5XG4gICAgICogQHBhcmFtIHtzdHJpbmd9IGZpZWxkS2V5XG4gICAgICogQHBhcmFtIHthcnJheX0gZGVwZW5kZW50RmllbGRzXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGRlcGVuZGVudEF0dHJpYnV0ZXNcbiAgICAgKi9cbiAgICBwcm90ZWN0ZWQgYWRkQXR0cmlidXRlRGVwZW5kZW5jeShmaWVsZEtleTogc3RyaW5nLCBkZXBlbmRlbnRGaWVsZHM6IE9iamVjdE1hcCwgZGVwZW5kZW50QXR0cmlidXRlczogQXR0cmlidXRlRGVwZW5kZW5jeVtdKTogdm9pZCB7XG4gICAgICAgIGNvbnN0IGZpZWxkID0gdGhpcy5yZWNvcmQuZmllbGRzW2ZpZWxkS2V5XTtcbiAgICAgICAgY29uc3QgbmFtZSA9IHRoaXMuZmllbGQubmFtZSB8fCB0aGlzLmZpZWxkLmRlZmluaXRpb24ubmFtZSB8fCAnJztcbiAgICAgICAgaWYgKGZpZWxkS2V5ID09PSBuYW1lIHx8ICFmaWVsZCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKGZpZWxkLmF0dHJpYnV0ZURlcGVuZGVuY2llcyAmJiBmaWVsZC5hdHRyaWJ1dGVEZXBlbmRlbmNpZXMubGVuZ3RoICYmIHRoaXMuaXNEZXBlbmRlbmN5QXR0cmlidXRlKGZpZWxkLmF0dHJpYnV0ZURlcGVuZGVuY2llcykpIHtcbiAgICAgICAgICAgIGRlcGVuZGVudEZpZWxkc1tuYW1lXSA9IGZpZWxkLmZpZWxkRGVwZW5kZW5jaWVzW25hbWVdO1xuICAgICAgICB9XG5cbiAgICAgICAgY29uc3QgYXR0cmlidXRlS2V5cyA9IChmaWVsZC5hdHRyaWJ1dGVzICYmIE9iamVjdC5rZXlzKGZpZWxkLmF0dHJpYnV0ZXMpKSB8fCBbXTtcblxuICAgICAgICBhdHRyaWJ1dGVLZXlzLmZvckVhY2goYXR0cmlidXRlS2V5ID0+IHtcblxuICAgICAgICAgICAgY29uc3QgYXR0cmlidXRlID0gZmllbGQuYXR0cmlidXRlc1thdHRyaWJ1dGVLZXldO1xuICAgICAgICAgICAgaWYgKCFhdHRyaWJ1dGUgfHwgIWF0dHJpYnV0ZS5hdHRyaWJ1dGVEZXBlbmRlbmNpZXMgfHwgIWF0dHJpYnV0ZS5hdHRyaWJ1dGVEZXBlbmRlbmNpZXMubGVuZ3RoKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBpZiAodGhpcy5pc0RlcGVuZGVuY3lBdHRyaWJ1dGUoYXR0cmlidXRlLmF0dHJpYnV0ZURlcGVuZGVuY2llcykpIHtcbiAgICAgICAgICAgICAgICBkZXBlbmRlbnRBdHRyaWJ1dGVzLnB1c2goe1xuICAgICAgICAgICAgICAgICAgICBmaWVsZDogZmllbGRLZXksXG4gICAgICAgICAgICAgICAgICAgIGF0dHJpYnV0ZTogYXR0cmlidXRlS2V5LFxuICAgICAgICAgICAgICAgICAgICB0eXBlczogKGRlcGVuZGVudEZpZWxkc1tuYW1lXSA/PyB7fSlbJ3R5cGVzJ10gPz8gW10sXG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENoZWNrIGlmIGF0dHJpYnV0ZSBpcyBkZXBlbmRlbmN5XG4gICAgICogQHBhcmFtIHtvYmplY3R9IGF0dHJpYnV0ZURlcGVuZGVuY2llc1xuICAgICAqIEByZXR1cm5zIHtib29sZWFufVxuICAgICAqL1xuICAgIHByb3RlY3RlZCBpc0RlcGVuZGVuY3lBdHRyaWJ1dGUoYXR0cmlidXRlRGVwZW5kZW5jaWVzOiBBdHRyaWJ1dGVEZXBlbmRlbmN5W10pOiBib29sZWFuIHtcblxuICAgICAgICBjb25zdCBwYXJlbnRLZXkgPSB0aGlzLmZpZWxkLnBhcmVudEtleSB8fCAnJztcbiAgICAgICAgY29uc3QgbmFtZSA9IHRoaXMuZmllbGQubmFtZSB8fCB0aGlzLmZpZWxkLmRlZmluaXRpb24ubmFtZSB8fCAnJztcblxuICAgICAgICByZXR1cm4gYXR0cmlidXRlRGVwZW5kZW5jaWVzLnNvbWUoZGVwZW5kZW5jeSA9PiBwYXJlbnRLZXkgPT09IGRlcGVuZGVuY3kuZmllbGQgJiYgbmFtZSA9PT0gZGVwZW5kZW5jeS5hdHRyaWJ1dGUpO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBzdWJzY3JpYmVWYWx1ZUNoYW5nZXMoKTogdm9pZCB7XG4gICAgICAgIGlmICh0aGlzLmZpZWxkICYmIHRoaXMuZmllbGQuZm9ybUNvbnRyb2wpIHtcbiAgICAgICAgICAgIHRoaXMuc3Vicy5wdXNoKHRoaXMuZmllbGQuZm9ybUNvbnRyb2wudmFsdWVDaGFuZ2VzLnN1YnNjcmliZSh2YWx1ZSA9PiB7XG5cbiAgICAgICAgICAgICAgICBpZiAoIWlzVm9pZCh2YWx1ZSkpIHtcbiAgICAgICAgICAgICAgICAgICAgdmFsdWUgPSB2YWx1ZS50cmltKCk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgdmFsdWUgPSAnJztcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICBpZiAodGhpcy50eXBlRm9ybWF0dGVyICYmIHRoaXMuZmllbGQudHlwZSkge1xuICAgICAgICAgICAgICAgICAgICB2YWx1ZSA9IHRoaXMudG9JbnRlcm5hbEZvcm1hdCh0aGlzLmZpZWxkLnR5cGUsIHZhbHVlKTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICB0aGlzLnNldEZpZWxkVmFsdWUodmFsdWUpO1xuICAgICAgICAgICAgfSkpO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIHRvSW50ZXJuYWxGb3JtYXQoZmllbGRUeXBlLCB2YWx1ZSk6IHN0cmluZyB7XG4gICAgICAgIHJldHVybiB0aGlzLnR5cGVGb3JtYXR0ZXIudG9JbnRlcm5hbEZvcm1hdChmaWVsZFR5cGUsIHZhbHVlKTtcblxuICAgIH1cblxuICAgIHByb3RlY3RlZCBzZXRGaWVsZFZhbHVlKG5ld1ZhbHVlKTogdm9pZCB7XG4gICAgICAgIHRoaXMuZmllbGQudmFsdWUgPSBuZXdWYWx1ZTtcbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgc2V0Rm9ybUNvbnRyb2xWYWx1ZShuZXdWYWx1ZTogc3RyaW5nIHwgc3RyaW5nW10pOiB2b2lkIHtcbiAgICAgICAgdGhpcy5maWVsZC5mb3JtQ29udHJvbC5tYXJrQXNEaXJ0eSgpO1xuXG4gICAgICAgIGlmIChpc0VxdWFsKHRoaXMuZmllbGQuZm9ybUNvbnRyb2wudmFsdWUsIG5ld1ZhbHVlKSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG4gICAgICAgIHRoaXMuZmllbGQuZm9ybUNvbnRyb2wuc2V0VmFsdWUobmV3VmFsdWUpO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCB1bnN1YnNjcmliZUFsbCgpOiB2b2lkIHtcbiAgICAgICAgdGhpcy5zdWJzLmZvckVhY2goc3ViID0+IHN1Yi51bnN1YnNjcmliZSgpKTtcbiAgICB9XG59XG4iXX0=