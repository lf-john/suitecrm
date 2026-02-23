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
import { Component, ElementRef, HostListener, signal, ViewChild } from '@angular/core';
import { BaseFieldComponent } from '../base/base-field.component';
import { FieldLogicManager } from '../field-logic/field-logic.manager';
import { DataTypeFormatter } from '../../services/formatters/data-type.formatter.service';
import { StandardFieldRegistry } from '../standard-field.registry';
import { FieldLogicDisplayManager } from '../field-logic-display/field-logic-display.manager';
import { SystemConfigStore } from "../../store/system-config/system-config.store";
import { Subject } from "rxjs";
import { debounceTime } from "rxjs/operators";
import * as i0 from "@angular/core";
import * as i1 from "../../services/formatters/data-type.formatter.service";
import * as i2 from "../standard-field.registry";
import * as i3 from "../field-logic/field-logic.manager";
import * as i4 from "../field-logic-display/field-logic-display.manager";
import * as i5 from "../../store/system-config/system-config.store";
import * as i6 from "@angular/common";
import * as i7 from "../../components/label/label.component";
import * as i8 from "../dynamic-field/dynamic-field.component";
const _c0 = ["wrapper"];
function GroupFieldComponent_ng_container_0_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵelement(1, "scrm-label", 1);
    i0.ɵɵelementContainerEnd();
} }
function GroupFieldComponent_ng_container_1_ng_container_3_div_1_span_1_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "span", 8)(1, "label");
    i0.ɵɵelement(2, "scrm-label", 9);
    i0.ɵɵelementEnd()();
} if (rf & 2) {
    const groupField_r4 = i0.ɵɵnextContext(2).$implicit;
    const ctx_r6 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("labelKey", groupField_r4.labelKey)("module", ctx_r6.getModule());
} }
function GroupFieldComponent_ng_container_1_ng_container_3_div_1_span_2_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "span", 10);
    i0.ɵɵelement(1, "scrm-dynamic-field", 11);
    i0.ɵɵelementEnd();
} if (rf & 2) {
    const groupField_r4 = i0.ɵɵnextContext(2).$implicit;
    const ctx_r7 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("componentType", ctx_r7.getComponentType(groupField_r4.type, groupField_r4.definition))("field", groupField_r4)("klass", ctx_r7.klass)("mode", ctx_r7.mode)("originalMode", ctx_r7.originalMode)("record", ctx_r7.record)("parent", ctx_r7.parent)("type", groupField_r4.type);
} }
function GroupFieldComponent_ng_container_1_ng_container_3_div_1_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", 5);
    i0.ɵɵtemplate(1, GroupFieldComponent_ng_container_1_ng_container_3_div_1_span_1_Template, 3, 2, "span", 6);
    i0.ɵɵtemplate(2, GroupFieldComponent_ng_container_1_ng_container_3_div_1_span_2_Template, 2, 8, "span", 7);
    i0.ɵɵelementEnd();
} if (rf & 2) {
    const groupField_r4 = i0.ɵɵnextContext().$implicit;
    const ctx_r5 = i0.ɵɵnextContext(2);
    i0.ɵɵclassProp("flex-fill", ctx_r5.mode === "edit" && ctx_r5.direction() === "flex-row")("h-100", ctx_r5.direction() === "flex-row");
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", groupField_r4.labelKey && ctx_r5.showLabel(groupField_r4.definition.name));
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", groupField_r4.type);
} }
function GroupFieldComponent_ng_container_1_ng_container_3_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵtemplate(1, GroupFieldComponent_ng_container_1_ng_container_3_div_1_Template, 3, 6, "div", 4);
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const groupField_r4 = ctx.$implicit;
    const ctx_r3 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", ctx_r3.isModeEnabled(ctx_r3.mode, groupField_r4));
} }
function GroupFieldComponent_ng_container_1_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵelementStart(1, "div", null, 2);
    i0.ɵɵtemplate(3, GroupFieldComponent_ng_container_1_ng_container_3_Template, 2, 1, "ng-container", 3);
    i0.ɵɵelementEnd();
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r1 = i0.ɵɵnextContext();
    i0.ɵɵadvance(1);
    i0.ɵɵclassMapInterpolate1("d-flex ", ctx_r1.direction(), " justify-content-start align-items-start field-group h-100 w-100");
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("ngForOf", ctx_r1.getFields());
} }
class GroupFieldComponent extends BaseFieldComponent {
    onResize() {
        this.triggerRecalculateDirection();
    }
    constructor(typeFormatter, registry, logic, logicDisplay, config) {
        super(typeFormatter, logic, logicDisplay);
        this.typeFormatter = typeFormatter;
        this.registry = registry;
        this.logic = logic;
        this.logicDisplay = logicDisplay;
        this.config = config;
        this.direction = signal('');
        this.recalculateDirectionBuffer = new Subject();
        this.recalculateDirectionBuffer$ = this.recalculateDirectionBuffer.asObservable();
    }
    ngOnInit() {
        super.ngOnInit();
        this.subs.push(this.recalculateDirectionBuffer$.pipe(debounceTime(50)).subscribe(() => {
            this.calculateDirection();
        }));
        this.subs.push(this.mode$.subscribe(() => {
            this.triggerRecalculateDirection();
        }));
        this.triggerRecalculateDirection();
        this.hasValidConfig = this.isConfigured();
    }
    ngAfterViewInit() {
        this.triggerRecalculateDirection();
    }
    getComponentType(type, definition) {
        let module = (this.record && this.record.module) || 'default';
        const displayType = (definition && definition.displayType) || '';
        return this.registry.getDisplayType(module, type, displayType, this.mode, this.field.name);
    }
    /**
     * Get the group fields from the record
     *
     * @returns {object} Field[]
     */
    getFields() {
        const fields = [];
        this.field.definition.layout.forEach(name => {
            if (!this.record.fields[name] || this.record.fields[name].display === 'none') {
                return;
            }
            fields.push(this.record.fields[name]);
        });
        return fields;
    }
    getModule() {
        if (!this.record) {
            return null;
        }
        return this.record.module;
    }
    /**
     * Get flex direction to be used
     */
    calculateDirection() {
        const wrapperWidth = this?.wrapper?.nativeElement?.offsetWidth ?? null;
        let direction = 'flex-column';
        if ((this?.field?.definition?.display ?? '') === 'inline') {
            direction = 'flex-row';
        }
        if (!wrapperWidth || this.mode === 'detail' || this.mode === 'list') {
            this.direction.set(direction);
            return;
        }
        const breakpoint = this?.config?.getUi('group_field_mobile_breakdown_limit') ?? 350;
        if (wrapperWidth < breakpoint) {
            this.direction.set('flex-column');
            return;
        }
        this.direction.set(direction);
    }
    /**
     * Check if is configured
     *
     * @returns {boolean} is configured
     */
    isConfigured() {
        return this.hasDisplay() && this.hasLayout() && this.hasGroupFields();
    }
    showLabel(fieldName) {
        const definition = this.field.definition || null;
        const showLabel = definition.showLabel || null;
        if (!definition || !showLabel) {
            return false;
        }
        const showLabelOptions = definition.showLabel[this.mode] || null;
        // showLabel > viewMode not defined || defined without any values e.g. edit:
        if (!showLabelOptions || typeof (showLabelOptions) === 'undefined') {
            return false;
        }
        return (showLabelOptions.includes('*') || showLabelOptions.includes(fieldName));
    }
    isModeEnabled(mode, groupField) {
        const modes = groupField.definition.modes;
        if (!modes || modes.length < 1) {
            return true;
        }
        return modes.includes(mode);
    }
    /**
     * Add re-calculation trigger to buffer
     * @protected
     */
    triggerRecalculateDirection() {
        this.recalculateDirectionBuffer.next(true);
    }
    /**
     * Check if groupFields are configured
     *
     * @returns {boolean} has groupFields
     */
    hasGroupFields() {
        return !!(this.field.definition.groupFields && Object.keys(this.field.definition.groupFields).length);
    }
    /**
     * Check if layout is configured
     *
     * @returns {boolean} has layout
     */
    hasLayout() {
        return !!(this.field.definition.layout && this.field.definition.layout.length);
    }
    /**
     * Check if display is configured
     *
     * @returns {boolean} has display
     */
    hasDisplay() {
        return !!this.field.definition.display;
    }
    static { this.ɵfac = function GroupFieldComponent_Factory(t) { return new (t || GroupFieldComponent)(i0.ɵɵdirectiveInject(i1.DataTypeFormatter), i0.ɵɵdirectiveInject(i2.StandardFieldRegistry), i0.ɵɵdirectiveInject(i3.FieldLogicManager), i0.ɵɵdirectiveInject(i4.FieldLogicDisplayManager), i0.ɵɵdirectiveInject(i5.SystemConfigStore)); }; }
    static { this.ɵcmp = /*@__PURE__*/ i0.ɵɵdefineComponent({ type: GroupFieldComponent, selectors: [["scrm-group-field"]], viewQuery: function GroupFieldComponent_Query(rf, ctx) { if (rf & 1) {
            i0.ɵɵviewQuery(_c0, 5);
        } if (rf & 2) {
            let _t;
            i0.ɵɵqueryRefresh(_t = i0.ɵɵloadQuery()) && (ctx.wrapper = _t.first);
        } }, hostBindings: function GroupFieldComponent_HostBindings(rf, ctx) { if (rf & 1) {
            i0.ɵɵlistener("resize", function GroupFieldComponent_resize_HostBindingHandler($event) { return ctx.onResize($event); }, false, i0.ɵɵresolveWindow);
        } }, features: [i0.ɵɵInheritDefinitionFeature], decls: 2, vars: 2, consts: [[4, "ngIf"], ["labelKey", "LBL_BAD_GROUP_FIELD_CONFIG"], ["wrapper", ""], [4, "ngFor", "ngForOf"], ["class", "field-group-item d-flex flex-column justify-content-end w-100", 3, "flex-fill", "h-100", 4, "ngIf"], [1, "field-group-item", "d-flex", "flex-column", "justify-content-end", "w-100"], ["class", "field-group-label pt-2 pr-1", 4, "ngIf"], ["class", "field-group-field pr-1", 4, "ngIf"], [1, "field-group-label", "pt-2", "pr-1"], [3, "labelKey", "module"], [1, "field-group-field", "pr-1"], [3, "componentType", "field", "klass", "mode", "originalMode", "record", "parent", "type"]], template: function GroupFieldComponent_Template(rf, ctx) { if (rf & 1) {
            i0.ɵɵtemplate(0, GroupFieldComponent_ng_container_0_Template, 2, 0, "ng-container", 0);
            i0.ɵɵtemplate(1, GroupFieldComponent_ng_container_1_Template, 4, 4, "ng-container", 0);
        } if (rf & 2) {
            i0.ɵɵproperty("ngIf", !ctx.hasValidConfig);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx.hasValidConfig);
        } }, dependencies: [i6.NgForOf, i6.NgIf, i7.LabelComponent, i8.DynamicFieldComponent], encapsulation: 2 }); }
}
export { GroupFieldComponent };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(GroupFieldComponent, [{
        type: Component,
        args: [{ selector: 'scrm-group-field', template: "<! --\n/**\n* SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.\n* Copyright (C) 2021 SalesAgility Ltd.\n*\n* This program is free software; you can redistribute it and/or modify it under\n* the terms of the GNU Affero General Public License version 3 as published by the\n* Free Software Foundation with the addition of the following permission added\n* to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK\n* IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE\n* WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.\n*\n* This program is distributed in the hope that it will be useful, but WITHOUT\n* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS\n* FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more\n* details.\n*\n* You should have received a copy of the GNU Affero General Public License\n* along with this program.  If not, see http://www.gnu.org/licenses.\n*\n* In accordance with Section 7(b) of the GNU Affero General Public License\n* version 3, these Appropriate Legal Notices must retain the display of the\n* \"Supercharged by SuiteCRM\" logo. If the display of the logos is not reasonably\n* feasible for technical reasons, the Appropriate Legal Notices must display\n* the words \"Supercharged by SuiteCRM\".\n*/\n-->\n<ng-container *ngIf=\"!this.hasValidConfig\">\n    <scrm-label labelKey=\"LBL_BAD_GROUP_FIELD_CONFIG\"></scrm-label>\n</ng-container>\n\n<ng-container *ngIf=\"this.hasValidConfig\">\n    <div #wrapper class=\"d-flex {{direction()}} justify-content-start align-items-start field-group h-100 w-100\">\n\n        <ng-container *ngFor=\"let groupField of getFields()\">\n\n            <div *ngIf=\"isModeEnabled(mode, groupField)\"\n                 [class.flex-fill]=\"mode ==='edit' && direction() === 'flex-row'\"\n                 class=\"field-group-item d-flex flex-column justify-content-end w-100\"\n                 [class.h-100]=\"direction() === 'flex-row'\">\n\n                <!-- LABEL -->\n                <span *ngIf=\"groupField.labelKey && showLabel(groupField.definition.name)\"\n                      class=\"field-group-label pt-2 pr-1\">\n                    <label>\n                        <scrm-label [labelKey]=\"groupField.labelKey\" [module]=\"getModule()\"></scrm-label>\n                    </label>\n                </span>\n\n                <!-- VALUE -->\n                <span *ngIf=\"groupField.type\" class=\"field-group-field pr-1\">\n\n                    <scrm-dynamic-field [componentType]=\"getComponentType(groupField.type, groupField.definition)\"\n                                        [field]=\"groupField\"\n                                        [klass]=\"klass\"\n                                        [mode]=\"mode\"\n                                        [originalMode]=\"originalMode\"\n                                        [record]=\"record\"\n                                        [parent]=\"parent\"\n                                        [type]=\"groupField.type\">\n                    </scrm-dynamic-field>\n\n                </span>\n            </div>\n        </ng-container>\n\n    </div>\n</ng-container>\n\n\n" }]
    }], function () { return [{ type: i1.DataTypeFormatter }, { type: i2.StandardFieldRegistry }, { type: i3.FieldLogicManager }, { type: i4.FieldLogicDisplayManager }, { type: i5.SystemConfigStore }]; }, { wrapper: [{
            type: ViewChild,
            args: ['wrapper']
        }], onResize: [{
            type: HostListener,
            args: ['window:resize', ['$event']]
        }] }); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ3JvdXAtZmllbGQuY29tcG9uZW50LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vY29yZS9hcHAvY29yZS9zcmMvbGliL2ZpZWxkcy9ncm91cC1maWVsZC9ncm91cC1maWVsZC5jb21wb25lbnQudHMiLCIuLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvZmllbGRzL2dyb3VwLWZpZWxkL2dyb3VwLWZpZWxkLmNvbXBvbmVudC5odG1sIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0F3Qkc7QUFFSCxPQUFPLEVBQWdCLFNBQVMsRUFBRSxVQUFVLEVBQUUsWUFBWSxFQUFFLE1BQU0sRUFBRSxTQUFTLEVBQWlCLE1BQU0sZUFBZSxDQUFDO0FBRXBILE9BQU8sRUFBQyxrQkFBa0IsRUFBQyxNQUFNLDhCQUE4QixDQUFDO0FBQ2hFLE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLG9DQUFvQyxDQUFDO0FBQ3JFLE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLHVEQUF1RCxDQUFDO0FBQ3hGLE9BQU8sRUFBQyxxQkFBcUIsRUFBQyxNQUFNLDRCQUE0QixDQUFDO0FBQ2pFLE9BQU8sRUFBQyx3QkFBd0IsRUFBQyxNQUFNLG9EQUFvRCxDQUFDO0FBQzVGLE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLCtDQUErQyxDQUFDO0FBQ2hGLE9BQU8sRUFBYSxPQUFPLEVBQUMsTUFBTSxNQUFNLENBQUM7QUFDekMsT0FBTyxFQUFDLFlBQVksRUFBQyxNQUFNLGdCQUFnQixDQUFDOzs7Ozs7Ozs7Ozs7SUNSNUMsNkJBQTJDO0lBQ3ZDLGdDQUErRDtJQUNuRSwwQkFBZTs7O0lBYUMsK0JBQzBDLFlBQUE7SUFFbEMsZ0NBQWlGO0lBQ3JGLGlCQUFRLEVBQUE7Ozs7SUFEUSxlQUFnQztJQUFoQyxpREFBZ0MsOEJBQUE7OztJQUtwRCxnQ0FBNkQ7SUFFekQseUNBUXFC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bWFuYWdlbWVudCBwcm9ncmFtIGRldmVsb3BlZCBieSBTYWxlc0FnaWxpdHkgTHRkLlxuICogQ29weXJpZ2h0IChDKSAyMDIxIFNhbGVzQWdpbGl0eSBMdGQuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGZyZWUgc29mdHdhcmU7IHlvdSBjYW4gcmVkaXN0cmlidXRlIGl0IGFuZC9vciBtb2RpZnkgaXQgdW5kZXJcbiAqIHRoZSB0ZXJtcyBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIHZlcnNpb24gMyBhcyBwdWJsaXNoZWQgYnkgdGhlXG4gKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4gKiB0byBTZWN0aW9uIDE1IGFzIHBlcm1pdHRlZCBpbiBTZWN0aW9uIDcoYSk6IEZPUiBBTlkgUEFSVCBPRiBUSEUgQ09WRVJFRCBXT1JLXG4gKiBJTiBXSElDSCBUSEUgQ09QWVJJR0hUIElTIE9XTkVEIEJZIFNBTEVTQUdJTElUWSwgU0FMRVNBR0lMSVRZIERJU0NMQUlNUyBUSEVcbiAqIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBkaXN0cmlidXRlZCBpbiB0aGUgaG9wZSB0aGF0IGl0IHdpbGwgYmUgdXNlZnVsLCBidXQgV0lUSE9VVFxuICogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiAqIEZPUiBBIFBBUlRJQ1VMQVIgUFVSUE9TRS4gU2VlIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgZm9yIG1vcmVcbiAqIGRldGFpbHMuXG4gKlxuICogWW91IHNob3VsZCBoYXZlIHJlY2VpdmVkIGEgY29weSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiBhbG9uZyB3aXRoIHRoaXMgcHJvZ3JhbS4gIElmIG5vdCwgc2VlIDxodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvPi5cbiAqXG4gKiBJbiBhY2NvcmRhbmNlIHdpdGggU2VjdGlvbiA3KGIpIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIHZlcnNpb24gMywgdGhlc2UgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IHJldGFpbiB0aGUgZGlzcGxheSBvZiB0aGVcbiAqIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4gKiBmZWFzaWJsZSBmb3IgdGVjaG5pY2FsIHJlYXNvbnMsIHRoZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgZGlzcGxheVxuICogdGhlIHdvcmRzIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIuXG4gKi9cblxuaW1wb3J0IHtBZnRlclZpZXdJbml0LCBDb21wb25lbnQsIEVsZW1lbnRSZWYsIEhvc3RMaXN0ZW5lciwgc2lnbmFsLCBWaWV3Q2hpbGQsIFdyaXRhYmxlU2lnbmFsfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7RmllbGQsIEZpZWxkRGVmaW5pdGlvbiwgVmlld01vZGV9IGZyb20gJ2NvbW1vbic7XG5pbXBvcnQge0Jhc2VGaWVsZENvbXBvbmVudH0gZnJvbSAnLi4vYmFzZS9iYXNlLWZpZWxkLmNvbXBvbmVudCc7XG5pbXBvcnQge0ZpZWxkTG9naWNNYW5hZ2VyfSBmcm9tICcuLi9maWVsZC1sb2dpYy9maWVsZC1sb2dpYy5tYW5hZ2VyJztcbmltcG9ydCB7RGF0YVR5cGVGb3JtYXR0ZXJ9IGZyb20gJy4uLy4uL3NlcnZpY2VzL2Zvcm1hdHRlcnMvZGF0YS10eXBlLmZvcm1hdHRlci5zZXJ2aWNlJztcbmltcG9ydCB7U3RhbmRhcmRGaWVsZFJlZ2lzdHJ5fSBmcm9tICcuLi9zdGFuZGFyZC1maWVsZC5yZWdpc3RyeSc7XG5pbXBvcnQge0ZpZWxkTG9naWNEaXNwbGF5TWFuYWdlcn0gZnJvbSAnLi4vZmllbGQtbG9naWMtZGlzcGxheS9maWVsZC1sb2dpYy1kaXNwbGF5Lm1hbmFnZXInO1xuaW1wb3J0IHtTeXN0ZW1Db25maWdTdG9yZX0gZnJvbSBcIi4uLy4uL3N0b3JlL3N5c3RlbS1jb25maWcvc3lzdGVtLWNvbmZpZy5zdG9yZVwiO1xuaW1wb3J0IHtPYnNlcnZhYmxlLCBTdWJqZWN0fSBmcm9tIFwicnhqc1wiO1xuaW1wb3J0IHtkZWJvdW5jZVRpbWV9IGZyb20gXCJyeGpzL29wZXJhdG9yc1wiO1xuXG5AQ29tcG9uZW50KHtcbiAgICBzZWxlY3RvcjogJ3Njcm0tZ3JvdXAtZmllbGQnLFxuICAgIHRlbXBsYXRlVXJsOiAnLi9ncm91cC1maWVsZC5jb21wb25lbnQuaHRtbCcsXG4gICAgc3R5bGVVcmxzOiBbXVxufSlcbmV4cG9ydCBjbGFzcyBHcm91cEZpZWxkQ29tcG9uZW50IGV4dGVuZHMgQmFzZUZpZWxkQ29tcG9uZW50IGltcGxlbWVudHMgQWZ0ZXJWaWV3SW5pdCB7XG5cbiAgICBAVmlld0NoaWxkKCd3cmFwcGVyJykgd3JhcHBlcjogRWxlbWVudFJlZjtcbiAgICBkaXJlY3Rpb246IFdyaXRhYmxlU2lnbmFsPHN0cmluZz4gPSBzaWduYWw8c3RyaW5nPignJyk7XG4gICAgaGFzVmFsaWRDb25maWc6IGJvb2xlYW47XG4gICAgcHJvdGVjdGVkIHJlY2FsY3VsYXRlRGlyZWN0aW9uQnVmZmVyID0gbmV3IFN1YmplY3Q8Ym9vbGVhbj4oKTtcbiAgICBwcm90ZWN0ZWQgcmVjYWxjdWxhdGVEaXJlY3Rpb25CdWZmZXIkOiBPYnNlcnZhYmxlPGFueT4gPSB0aGlzLnJlY2FsY3VsYXRlRGlyZWN0aW9uQnVmZmVyLmFzT2JzZXJ2YWJsZSgpO1xuXG4gICAgQEhvc3RMaXN0ZW5lcignd2luZG93OnJlc2l6ZScsIFsnJGV2ZW50J10pXG4gICAgb25SZXNpemUoKTogdm9pZCB7XG4gICAgICAgIHRoaXMudHJpZ2dlclJlY2FsY3VsYXRlRGlyZWN0aW9uKCk7XG4gICAgfVxuXG4gICAgY29uc3RydWN0b3IoXG4gICAgICAgIHByb3RlY3RlZCB0eXBlRm9ybWF0dGVyOiBEYXRhVHlwZUZvcm1hdHRlcixcbiAgICAgICAgcHJvdGVjdGVkIHJlZ2lzdHJ5OiBTdGFuZGFyZEZpZWxkUmVnaXN0cnksXG4gICAgICAgIHByb3RlY3RlZCBsb2dpYzogRmllbGRMb2dpY01hbmFnZXIsXG4gICAgICAgIHByb3RlY3RlZCBsb2dpY0Rpc3BsYXk6IEZpZWxkTG9naWNEaXNwbGF5TWFuYWdlcixcbiAgICAgICAgcHJvdGVjdGVkIGNvbmZpZzogU3lzdGVtQ29uZmlnU3RvcmVcbiAgICApIHtcbiAgICAgICAgc3VwZXIodHlwZUZvcm1hdHRlciwgbG9naWMsIGxvZ2ljRGlzcGxheSk7XG4gICAgfVxuXG4gICAgbmdPbkluaXQoKTogdm9pZCB7XG4gICAgICAgIHN1cGVyLm5nT25Jbml0KCk7XG5cbiAgICAgICAgdGhpcy5zdWJzLnB1c2godGhpcy5yZWNhbGN1bGF0ZURpcmVjdGlvbkJ1ZmZlciQucGlwZShkZWJvdW5jZVRpbWUoNTApKS5zdWJzY3JpYmUoKCkgPT4ge1xuICAgICAgICAgICAgdGhpcy5jYWxjdWxhdGVEaXJlY3Rpb24oKTtcbiAgICAgICAgfSkpO1xuXG4gICAgICAgIHRoaXMuc3Vicy5wdXNoKHRoaXMubW9kZSQuc3Vic2NyaWJlKCgpID0+IHtcbiAgICAgICAgICAgIHRoaXMudHJpZ2dlclJlY2FsY3VsYXRlRGlyZWN0aW9uKCk7XG4gICAgICAgIH0pKTtcblxuICAgICAgICB0aGlzLnRyaWdnZXJSZWNhbGN1bGF0ZURpcmVjdGlvbigpO1xuXG4gICAgICAgIHRoaXMuaGFzVmFsaWRDb25maWcgPSB0aGlzLmlzQ29uZmlndXJlZCgpO1xuICAgIH1cblxuICAgIG5nQWZ0ZXJWaWV3SW5pdCgpOiB2b2lkIHtcbiAgICAgICAgdGhpcy50cmlnZ2VyUmVjYWxjdWxhdGVEaXJlY3Rpb24oKTtcbiAgICB9XG5cbiAgICBnZXRDb21wb25lbnRUeXBlKHR5cGU6IHN0cmluZywgZGVmaW5pdGlvbjogRmllbGREZWZpbml0aW9uKTogYW55IHtcbiAgICAgICAgbGV0IG1vZHVsZSA9ICh0aGlzLnJlY29yZCAmJiB0aGlzLnJlY29yZC5tb2R1bGUpIHx8ICdkZWZhdWx0JztcblxuICAgICAgICBjb25zdCBkaXNwbGF5VHlwZSA9IChkZWZpbml0aW9uICYmIGRlZmluaXRpb24uZGlzcGxheVR5cGUpIHx8ICcnO1xuXG4gICAgICAgIHJldHVybiB0aGlzLnJlZ2lzdHJ5LmdldERpc3BsYXlUeXBlKG1vZHVsZSwgdHlwZSwgZGlzcGxheVR5cGUsIHRoaXMubW9kZSwgdGhpcy5maWVsZC5uYW1lKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBHZXQgdGhlIGdyb3VwIGZpZWxkcyBmcm9tIHRoZSByZWNvcmRcbiAgICAgKlxuICAgICAqIEByZXR1cm5zIHtvYmplY3R9IEZpZWxkW11cbiAgICAgKi9cbiAgICBnZXRGaWVsZHMoKTogRmllbGRbXSB7XG4gICAgICAgIGNvbnN0IGZpZWxkczogRmllbGRbXSA9IFtdO1xuXG4gICAgICAgIHRoaXMuZmllbGQuZGVmaW5pdGlvbi5sYXlvdXQuZm9yRWFjaChuYW1lID0+IHtcbiAgICAgICAgICAgIGlmICghdGhpcy5yZWNvcmQuZmllbGRzW25hbWVdIHx8IHRoaXMucmVjb3JkLmZpZWxkc1tuYW1lXS5kaXNwbGF5ID09PSAnbm9uZScpIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGZpZWxkcy5wdXNoKHRoaXMucmVjb3JkLmZpZWxkc1tuYW1lXSk7XG4gICAgICAgIH0pO1xuXG4gICAgICAgIHJldHVybiBmaWVsZHM7XG4gICAgfVxuXG4gICAgZ2V0TW9kdWxlKCk6IHN0cmluZyB7XG4gICAgICAgIGlmICghdGhpcy5yZWNvcmQpIHtcbiAgICAgICAgICAgIHJldHVybiBudWxsO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHRoaXMucmVjb3JkLm1vZHVsZTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBHZXQgZmxleCBkaXJlY3Rpb24gdG8gYmUgdXNlZFxuICAgICAqL1xuICAgIGNhbGN1bGF0ZURpcmVjdGlvbigpOiB2b2lkIHtcblxuICAgICAgICBjb25zdCB3cmFwcGVyV2lkdGggPSB0aGlzPy53cmFwcGVyPy5uYXRpdmVFbGVtZW50Py5vZmZzZXRXaWR0aCA/PyBudWxsO1xuXG4gICAgICAgIGxldCBkaXJlY3Rpb24gPSAnZmxleC1jb2x1bW4nO1xuXG4gICAgICAgIGlmICgodGhpcz8uZmllbGQ/LmRlZmluaXRpb24/LmRpc3BsYXkgPz8gJycpID09PSAnaW5saW5lJykge1xuICAgICAgICAgICAgZGlyZWN0aW9uID0gJ2ZsZXgtcm93JztcbiAgICAgICAgfVxuXG4gICAgICAgIGlmICghd3JhcHBlcldpZHRoIHx8IHRoaXMubW9kZSA9PT0gJ2RldGFpbCcgfHwgdGhpcy5tb2RlID09PSAnbGlzdCcpIHtcbiAgICAgICAgICAgIHRoaXMuZGlyZWN0aW9uLnNldChkaXJlY3Rpb24pO1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgY29uc3QgYnJlYWtwb2ludCA9IHRoaXM/LmNvbmZpZz8uZ2V0VWkoJ2dyb3VwX2ZpZWxkX21vYmlsZV9icmVha2Rvd25fbGltaXQnKSA/PyAzNTA7XG5cbiAgICAgICAgaWYgKHdyYXBwZXJXaWR0aCA8IGJyZWFrcG9pbnQpIHtcbiAgICAgICAgICAgIHRoaXMuZGlyZWN0aW9uLnNldCgnZmxleC1jb2x1bW4nKTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIHRoaXMuZGlyZWN0aW9uLnNldChkaXJlY3Rpb24pO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENoZWNrIGlmIGlzIGNvbmZpZ3VyZWRcbiAgICAgKlxuICAgICAqIEByZXR1cm5zIHtib29sZWFufSBpcyBjb25maWd1cmVkXG4gICAgICovXG4gICAgaXNDb25maWd1cmVkKCk6IGJvb2xlYW4ge1xuICAgICAgICByZXR1cm4gdGhpcy5oYXNEaXNwbGF5KCkgJiYgdGhpcy5oYXNMYXlvdXQoKSAmJiB0aGlzLmhhc0dyb3VwRmllbGRzKCk7XG4gICAgfVxuXG4gICAgc2hvd0xhYmVsKGZpZWxkTmFtZTogc3RyaW5nKTogYm9vbGVhbiB7XG4gICAgICAgIGNvbnN0IGRlZmluaXRpb24gPSB0aGlzLmZpZWxkLmRlZmluaXRpb24gfHwgbnVsbDtcbiAgICAgICAgY29uc3Qgc2hvd0xhYmVsID0gZGVmaW5pdGlvbi5zaG93TGFiZWwgfHwgbnVsbDtcblxuICAgICAgICBpZiAoIWRlZmluaXRpb24gfHwgIXNob3dMYWJlbCkge1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cbiAgICAgICAgY29uc3Qgc2hvd0xhYmVsT3B0aW9ucyA9IGRlZmluaXRpb24uc2hvd0xhYmVsW3RoaXMubW9kZV0gfHwgbnVsbDtcblxuICAgICAgICAvLyBzaG93TGFiZWwgPiB2aWV3TW9kZSBub3QgZGVmaW5lZCB8fCBkZWZpbmVkIHdpdGhvdXQgYW55IHZhbHVlcyBlLmcuIGVkaXQ6XG4gICAgICAgIGlmICghc2hvd0xhYmVsT3B0aW9ucyB8fCB0eXBlb2YgKHNob3dMYWJlbE9wdGlvbnMpID09PSAndW5kZWZpbmVkJykge1xuICAgICAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIChzaG93TGFiZWxPcHRpb25zLmluY2x1ZGVzKCcqJykgfHwgc2hvd0xhYmVsT3B0aW9ucy5pbmNsdWRlcyhmaWVsZE5hbWUpKTtcbiAgICB9XG5cbiAgICBpc01vZGVFbmFibGVkKG1vZGU6IHN0cmluZywgZ3JvdXBGaWVsZDogRmllbGQpIHtcbiAgICAgICAgY29uc3QgbW9kZXMgPSBncm91cEZpZWxkLmRlZmluaXRpb24ubW9kZXM7XG4gICAgICAgIGlmICghbW9kZXMgfHwgbW9kZXMubGVuZ3RoIDwgMSkge1xuICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gbW9kZXMuaW5jbHVkZXMobW9kZSBhcyBWaWV3TW9kZSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQWRkIHJlLWNhbGN1bGF0aW9uIHRyaWdnZXIgdG8gYnVmZmVyXG4gICAgICogQHByb3RlY3RlZFxuICAgICAqL1xuICAgIHByb3RlY3RlZCB0cmlnZ2VyUmVjYWxjdWxhdGVEaXJlY3Rpb24oKTogdm9pZCB7XG4gICAgICAgIHRoaXMucmVjYWxjdWxhdGVEaXJlY3Rpb25CdWZmZXIubmV4dCh0cnVlKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDaGVjayBpZiBncm91cEZpZWxkcyBhcmUgY29uZmlndXJlZFxuICAgICAqXG4gICAgICogQHJldHVybnMge2Jvb2xlYW59IGhhcyBncm91cEZpZWxkc1xuICAgICAqL1xuICAgIHByb3RlY3RlZCBoYXNHcm91cEZpZWxkcygpOiBib29sZWFuIHtcbiAgICAgICAgcmV0dXJuICEhKHRoaXMuZmllbGQuZGVmaW5pdGlvbi5ncm91cEZpZWxkcyAmJiBPYmplY3Qua2V5cyh0aGlzLmZpZWxkLmRlZmluaXRpb24uZ3JvdXBGaWVsZHMpLmxlbmd0aCk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogQ2hlY2sgaWYgbGF5b3V0IGlzIGNvbmZpZ3VyZWRcbiAgICAgKlxuICAgICAqIEByZXR1cm5zIHtib29sZWFufSBoYXMgbGF5b3V0XG4gICAgICovXG4gICAgcHJvdGVjdGVkIGhhc0xheW91dCgpOiBib29sZWFuIHtcbiAgICAgICAgcmV0dXJuICEhKHRoaXMuZmllbGQuZGVmaW5pdGlvbi5sYXlvdXQgJiYgdGhpcy5maWVsZC5kZWZpbml0aW9uLmxheW91dC5sZW5ndGgpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENoZWNrIGlmIGRpc3BsYXkgaXMgY29uZmlndXJlZFxuICAgICAqXG4gICAgICogQHJldHVybnMge2Jvb2xlYW59IGhhcyBkaXNwbGF5XG4gICAgICovXG4gICAgcHJvdGVjdGVkIGhhc0Rpc3BsYXkoKTogYm9vbGVhbiB7XG4gICAgICAgIHJldHVybiAhIXRoaXMuZmllbGQuZGVmaW5pdGlvbi5kaXNwbGF5O1xuICAgIH1cblxufVxuIiwiPCEgLS1cbi8qKlxuKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4qIENvcHlyaWdodCAoQykgMjAyMSBTYWxlc0FnaWxpdHkgTHRkLlxuKlxuKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuKiB0aGUgdGVybXMgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSB2ZXJzaW9uIDMgYXMgcHVibGlzaGVkIGJ5IHRoZVxuKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4qIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiogSU4gV0hJQ0ggVEhFIENPUFlSSUdIVCBJUyBPV05FRCBCWSBTQUxFU0FHSUxJVFksIFNBTEVTQUdJTElUWSBESVNDTEFJTVMgVEhFXG4qIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuKlxuKiBUaGlzIHByb2dyYW0gaXMgZGlzdHJpYnV0ZWQgaW4gdGhlIGhvcGUgdGhhdCBpdCB3aWxsIGJlIHVzZWZ1bCwgYnV0IFdJVEhPVVRcbiogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuKiBkZXRhaWxzLlxuKlxuKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiogYWxvbmcgd2l0aCB0aGlzIHByb2dyYW0uICBJZiBub3QsIHNlZSBodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMuXG4qXG4qIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuKiB2ZXJzaW9uIDMsIHRoZXNlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCByZXRhaW4gdGhlIGRpc3BsYXkgb2YgdGhlXG4qIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4qIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4qIHRoZSB3b3JkcyBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiLlxuKi9cbi0tPlxuPG5nLWNvbnRhaW5lciAqbmdJZj1cIiF0aGlzLmhhc1ZhbGlkQ29uZmlnXCI+XG4gICAgPHNjcm0tbGFiZWwgbGFiZWxLZXk9XCJMQkxfQkFEX0dST1VQX0ZJRUxEX0NPTkZJR1wiPjwvc2NybS1sYWJlbD5cbjwvbmctY29udGFpbmVyPlxuXG48bmctY29udGFpbmVyICpuZ0lmPVwidGhpcy5oYXNWYWxpZENvbmZpZ1wiPlxuICAgIDxkaXYgI3dyYXBwZXIgY2xhc3M9XCJkLWZsZXgge3tkaXJlY3Rpb24oKX19IGp1c3RpZnktY29udGVudC1zdGFydCBhbGlnbi1pdGVtcy1zdGFydCBmaWVsZC1ncm91cCBoLTEwMCB3LTEwMFwiPlxuXG4gICAgICAgIDxuZy1jb250YWluZXIgKm5nRm9yPVwibGV0IGdyb3VwRmllbGQgb2YgZ2V0RmllbGRzKClcIj5cblxuICAgICAgICAgICAgPGRpdiAqbmdJZj1cImlzTW9kZUVuYWJsZWQobW9kZSwgZ3JvdXBGaWVsZClcIlxuICAgICAgICAgICAgICAgICBbY2xhc3MuZmxleC1maWxsXT1cIm1vZGUgPT09J2VkaXQnICYmIGRpcmVjdGlvbigpID09PSAnZmxleC1yb3cnXCJcbiAgICAgICAgICAgICAgICAgY2xhc3M9XCJmaWVsZC1ncm91cC1pdGVtIGQtZmxleCBmbGV4LWNvbHVtbiBqdXN0aWZ5LWNvbnRlbnQtZW5kIHctMTAwXCJcbiAgICAgICAgICAgICAgICAgW2NsYXNzLmgtMTAwXT1cImRpcmVjdGlvbigpID09PSAnZmxleC1yb3cnXCI+XG5cbiAgICAgICAgICAgICAgICA8IS0tIExBQkVMIC0tPlxuICAgICAgICAgICAgICAgIDxzcGFuICpuZ0lmPVwiZ3JvdXBGaWVsZC5sYWJlbEtleSAmJiBzaG93TGFiZWwoZ3JvdXBGaWVsZC5kZWZpbml0aW9uLm5hbWUpXCJcbiAgICAgICAgICAgICAgICAgICAgICBjbGFzcz1cImZpZWxkLWdyb3VwLWxhYmVsIHB0LTIgcHItMVwiPlxuICAgICAgICAgICAgICAgICAgICA8bGFiZWw+XG4gICAgICAgICAgICAgICAgICAgICAgICA8c2NybS1sYWJlbCBbbGFiZWxLZXldPVwiZ3JvdXBGaWVsZC5sYWJlbEtleVwiIFttb2R1bGVdPVwiZ2V0TW9kdWxlKClcIj48L3Njcm0tbGFiZWw+XG4gICAgICAgICAgICAgICAgICAgIDwvbGFiZWw+XG4gICAgICAgICAgICAgICAgPC9zcGFuPlxuXG4gICAgICAgICAgICAgICAgPCEtLSBWQUxVRSAtLT5cbiAgICAgICAgICAgICAgICA8c3BhbiAqbmdJZj1cImdyb3VwRmllbGQudHlwZVwiIGNsYXNzPVwiZmllbGQtZ3JvdXAtZmllbGQgcHItMVwiPlxuXG4gICAgICAgICAgICAgICAgICAgIDxzY3JtLWR5bmFtaWMtZmllbGQgW2NvbXBvbmVudFR5cGVdPVwiZ2V0Q29tcG9uZW50VHlwZShncm91cEZpZWxkLnR5cGUsIGdyb3VwRmllbGQuZGVmaW5pdGlvbilcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFtmaWVsZF09XCJncm91cEZpZWxkXCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBba2xhc3NdPVwia2xhc3NcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFttb2RlXT1cIm1vZGVcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFtvcmlnaW5hbE1vZGVdPVwib3JpZ2luYWxNb2RlXCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBbcmVjb3JkXT1cInJlY29yZFwiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgW3BhcmVudF09XCJwYXJlbnRcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFt0eXBlXT1cImdyb3VwRmllbGQudHlwZVwiPlxuICAgICAgICAgICAgICAgICAgICA8L3Njcm0tZHluYW1pYy1maWVsZD5cblxuICAgICAgICAgICAgICAgIDwvc3Bhbj5cbiAgICAgICAgICAgIDwvZGl2PlxuICAgICAgICA8L25nLWNvbnRhaW5lcj5cblxuICAgIDwvZGl2PlxuPC9uZy1jb250YWluZXI+XG5cblxuIl19