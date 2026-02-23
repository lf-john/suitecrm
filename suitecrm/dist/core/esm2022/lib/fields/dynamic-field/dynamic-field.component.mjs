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
import { ChangeDetectorRef, Component, HostBinding, Input, Type } from '@angular/core';
import { EDITABLE_VIEW_MODES } from 'common';
import { Router } from '@angular/router';
import { ModuleNameMapper } from '../../services/navigation/module-name-mapper/module-name-mapper.service';
import { ModuleNavigation } from '../../services/navigation/module-navigation/module-navigation.service';
import { DynamicLabelService } from '../../services/language/dynamic-label.service';
import { LinkRouteAsyncActionService } from '../../services/navigation/link-route-async-action/link-route-async-action.service';
import * as i0 from "@angular/core";
import * as i1 from "../../services/navigation/module-navigation/module-navigation.service";
import * as i2 from "../../services/navigation/module-name-mapper/module-name-mapper.service";
import * as i3 from "@angular/router";
import * as i4 from "../../services/language/dynamic-label.service";
import * as i5 from "../../services/navigation/link-route-async-action/link-route-async-action.service";
import * as i6 from "@angular/common";
import * as i7 from "../../components/dynamic-label/dynamic-label.component";
import * as i8 from "ng-dynamic-component";
const _c0 = function (a0, a1, a2, a3, a4, a5) { return { "mode": a0, "originalMode": a1, "field": a2, "klass": a3, "record": a4, "parent": a5 }; };
function DynamicFieldComponent_ng_container_0_ng_container_1_Template(rf, ctx) { if (rf & 1) {
    const _r7 = i0.ɵɵgetCurrentView();
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵelementStart(1, "a", 1);
    i0.ɵɵlistener("click", function DynamicFieldComponent_ng_container_0_ng_container_1_Template_a_click_1_listener() { i0.ɵɵrestoreView(_r7); const ctx_r6 = i0.ɵɵnextContext(2); return i0.ɵɵresetView(ctx_r6.onClick()); });
    i0.ɵɵelement(2, "ndc-dynamic", 2);
    i0.ɵɵelementEnd();
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r3 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("ndcDynamicComponent", ctx_r3.componentType)("ndcDynamicInputs", i0.ɵɵpureFunction6(2, _c0, ctx_r3.mode, ctx_r3.originalMode, ctx_r3.field, ctx_r3.klass, ctx_r3.record, ctx_r3.parent));
} }
function DynamicFieldComponent_ng_container_0_ng_container_2_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵelementStart(1, "a", 3);
    i0.ɵɵelement(2, "ndc-dynamic", 2);
    i0.ɵɵelementEnd();
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r4 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("routerLink", ctx_r4.getLink());
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ndcDynamicComponent", ctx_r4.componentType)("ndcDynamicInputs", i0.ɵɵpureFunction6(3, _c0, ctx_r4.mode, ctx_r4.originalMode, ctx_r4.field, ctx_r4.klass, ctx_r4.record, ctx_r4.parent));
} }
function DynamicFieldComponent_ng_container_0_ng_container_3_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵelement(1, "ndc-dynamic", 2);
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r5 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ndcDynamicComponent", ctx_r5.componentType)("ndcDynamicInputs", i0.ɵɵpureFunction6(2, _c0, ctx_r5.mode, ctx_r5.originalMode, ctx_r5.field, ctx_r5.klass, ctx_r5.record, ctx_r5.parent));
} }
function DynamicFieldComponent_ng_container_0_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵtemplate(1, DynamicFieldComponent_ng_container_0_ng_container_1_Template, 3, 9, "ng-container", 0);
    i0.ɵɵtemplate(2, DynamicFieldComponent_ng_container_0_ng_container_2_Template, 3, 10, "ng-container", 0);
    i0.ɵɵtemplate(3, DynamicFieldComponent_ng_container_0_ng_container_3_Template, 2, 9, "ng-container", 0);
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r0 = i0.ɵɵnextContext();
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", ctx_r0.hasOnClick());
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", ctx_r0.isLink() && !ctx_r0.hasOnClick());
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", !ctx_r0.isLink() && !ctx_r0.hasOnClick());
} }
function DynamicFieldComponent_ng_container_1_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵelementStart(1, "div", 4);
    i0.ɵɵelement(2, "div", 5);
    i0.ɵɵelementEnd();
    i0.ɵɵelementContainerEnd();
} }
const _c1 = function (a0) { return { field: a0 }; };
function DynamicFieldComponent_ng_container_2_ng_container_1_div_1_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", 7);
    i0.ɵɵelement(1, "scrm-dynamic-label", 8);
    i0.ɵɵelementEnd();
} if (rf & 2) {
    const item_r10 = ctx.$implicit;
    const ctx_r9 = i0.ɵɵnextContext(3);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("context", ctx_r9.getMessageContext(item_r10.value, ctx_r9.record))("fields", i0.ɵɵpureFunction1(3, _c1, ctx_r9.field))("labelKey", ctx_r9.getMessageLabelKey(item_r10.value));
} }
function DynamicFieldComponent_ng_container_2_ng_container_1_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵtemplate(1, DynamicFieldComponent_ng_container_2_ng_container_1_div_1_Template, 2, 5, "div", 6);
    i0.ɵɵpipe(2, "keyvalue");
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r8 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngForOf", i0.ɵɵpipeBind1(2, 1, ctx_r8.field.formControl.errors));
} }
function DynamicFieldComponent_ng_container_2_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵtemplate(1, DynamicFieldComponent_ng_container_2_ng_container_1_Template, 3, 3, "ng-container", 0);
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r2 = i0.ɵɵnextContext();
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", ctx_r2.field.formControl.invalid && ctx_r2.field.formControl.touched);
} }
class DynamicFieldComponent {
    constructor(navigation, moduleNameMapper, router, dynamicLabelService, linkRouteAsyncActionService, cd) {
        this.navigation = navigation;
        this.moduleNameMapper = moduleNameMapper;
        this.router = router;
        this.dynamicLabelService = dynamicLabelService;
        this.linkRouteAsyncActionService = linkRouteAsyncActionService;
        this.cd = cd;
        this.record = null;
        this.parent = null;
        this.klass = null;
        this.class = 'dynamic-field';
    }
    get getRelateLink() {
        let linkModule = this.field.definition.module;
        if (this.field.definition.type_name === 'parent_type') {
            linkModule = this.record.attributes.parent_type;
        }
        if (this.field.definition.id_name && linkModule) {
            const moduleName = this.moduleNameMapper.toFrontend(linkModule);
            return this.navigation.getRecordRouterLink(moduleName, this.record.attributes[this.field.definition.id_name]);
        }
        return '';
    }
    ngOnInit() {
        this.setHostClass();
        this.cd.detectChanges();
    }
    isLink() {
        if (EDITABLE_VIEW_MODES.includes(this.mode)) {
            return false;
        }
        if (!this.field || !this.record) {
            return false;
        }
        if (this.type === 'relate') {
            return true;
        }
        return !!(this?.field?.metadata && this?.field?.metadata?.link);
    }
    hasOnClick() {
        const fieldMetadata = this?.field?.metadata ?? {};
        const linkAsyncAction = fieldMetadata?.linkAsyncAction ?? null;
        const linkOnClick = fieldMetadata?.onClick ?? null;
        return !!(linkAsyncAction || linkOnClick);
    }
    isEdit() {
        return this.mode === 'edit' || this.mode === 'filter';
    }
    getLink() {
        if (this.type === 'relate') {
            return this.getRelateLink;
        }
        const fieldMetadata = this?.field?.metadata ?? null;
        const linkRoute = fieldMetadata.linkRoute ?? null;
        if (fieldMetadata && linkRoute) {
            return this.dynamicLabelService.parse(linkRoute, {}, this.record.fields);
        }
        return this.navigation.getRecordRouterLink(this.record.module, this.record.id);
    }
    getMessageContext(item, record) {
        const context = item && item.message && item.message.context || {};
        context.module = (record && record.module) || '';
        return context;
    }
    getMessageLabelKey(item) {
        return (item && item.message && item.message.labelKey) || '';
    }
    onClick() {
        const fieldMetadata = this?.field?.metadata ?? null;
        if (fieldMetadata && fieldMetadata.onClick) {
            this.field.metadata.onClick(this.field, this.record);
            return;
        }
        const linkAsyncAction = fieldMetadata.linkAsyncAction ?? null;
        if (fieldMetadata && linkAsyncAction) {
            this.linkRouteAsyncActionService.run(linkAsyncAction, this.field, this.record);
            return;
        }
        this.router.navigateByUrl(this.getLink()).then();
        return false;
    }
    setHostClass() {
        const classes = [];
        classes.push('dynamic-field');
        if (this.mode) {
            classes.push('dynamic-field-mode-' + this.mode);
        }
        if (this.type) {
            classes.push('dynamic-field-type-' + this.type);
        }
        if (this.field && this.field.name) {
            classes.push('dynamic-field-name-' + this.field.name);
        }
        this.class = classes.join(' ');
    }
    static { this.ɵfac = function DynamicFieldComponent_Factory(t) { return new (t || DynamicFieldComponent)(i0.ɵɵdirectiveInject(i1.ModuleNavigation), i0.ɵɵdirectiveInject(i2.ModuleNameMapper), i0.ɵɵdirectiveInject(i3.Router), i0.ɵɵdirectiveInject(i4.DynamicLabelService), i0.ɵɵdirectiveInject(i5.LinkRouteAsyncActionService), i0.ɵɵdirectiveInject(i0.ChangeDetectorRef)); }; }
    static { this.ɵcmp = /*@__PURE__*/ i0.ɵɵdefineComponent({ type: DynamicFieldComponent, selectors: [["scrm-dynamic-field"]], hostVars: 2, hostBindings: function DynamicFieldComponent_HostBindings(rf, ctx) { if (rf & 2) {
            i0.ɵɵclassMap(ctx.class);
        } }, inputs: { mode: "mode", originalMode: "originalMode", type: "type", field: "field", record: "record", parent: "parent", klass: "klass", componentType: "componentType" }, decls: 3, vars: 3, consts: [[4, "ngIf"], [1, "clickable", "field-link", 3, "click"], [3, "ndcDynamicComponent", "ndcDynamicInputs"], [1, "field-link", 3, "routerLink"], [1, "dynamic-field"], [1, "flex-grow-1", "text-break", "rounded", "box-loading", "skeleton-field-content"], ["class", "invalid-feedback d-block", 4, "ngFor", "ngForOf"], [1, "invalid-feedback", "d-block"], [3, "context", "fields", "labelKey"]], template: function DynamicFieldComponent_Template(rf, ctx) { if (rf & 1) {
            i0.ɵɵtemplate(0, DynamicFieldComponent_ng_container_0_Template, 4, 3, "ng-container", 0);
            i0.ɵɵtemplate(1, DynamicFieldComponent_ng_container_1_Template, 3, 0, "ng-container", 0);
            i0.ɵɵtemplate(2, DynamicFieldComponent_ng_container_2_Template, 2, 1, "ng-container", 0);
        } if (rf & 2) {
            i0.ɵɵproperty("ngIf", !ctx.field.loading);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx.field.loading);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx.isEdit() && ctx.field.formControl && ctx.field.formControl.errors);
        } }, dependencies: [i6.NgForOf, i6.NgIf, i3.RouterLink, i7.DynamicLabelComponent, i8.DynamicIoDirective, i8.DynamicComponent, i6.KeyValuePipe], encapsulation: 2 }); }
}
export { DynamicFieldComponent };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(DynamicFieldComponent, [{
        type: Component,
        args: [{ selector: 'scrm-dynamic-field', template: "<! --\n/**\n* SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.\n* Copyright (C) 2021 SalesAgility Ltd.\n*\n* This program is free software; you can redistribute it and/or modify it under\n* the terms of the GNU Affero General Public License version 3 as published by the\n* Free Software Foundation with the addition of the following permission added\n* to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK\n* IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE\n* WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.\n*\n* This program is distributed in the hope that it will be useful, but WITHOUT\n* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS\n* FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more\n* details.\n*\n* You should have received a copy of the GNU Affero General Public License\n* along with this program.  If not, see http://www.gnu.org/licenses.\n*\n* In accordance with Section 7(b) of the GNU Affero General Public License\n* version 3, these Appropriate Legal Notices must retain the display of the\n* \"Supercharged by SuiteCRM\" logo. If the display of the logos is not reasonably\n* feasible for technical reasons, the Appropriate Legal Notices must display\n* the words \"Supercharged by SuiteCRM\".\n*/\n-->\n<ng-container *ngIf=\"!field.loading\">\n    <ng-container *ngIf=\"hasOnClick()\">\n        <a (click)=\"onClick()\" class=\"clickable field-link\">\n            <ndc-dynamic\n                [ndcDynamicComponent]=\"componentType\"\n                [ndcDynamicInputs]=\"{\n                'mode': mode,\n                'originalMode': originalMode,\n                'field': field,\n                'klass': klass,\n                'record': record,\n                'parent': parent\n            }\"\n            ></ndc-dynamic>\n        </a>\n    </ng-container>\n    <ng-container *ngIf=\"isLink() && !hasOnClick()\">\n        <a [routerLink]=\"getLink()\" class=\"field-link\">\n            <ndc-dynamic\n                [ndcDynamicComponent]=\"componentType\"\n                [ndcDynamicInputs]=\"{\n                'mode': mode,\n                'originalMode': originalMode,\n                'field': field,\n                'klass': klass,\n                'record': record,\n                'parent': parent\n            }\"\n            ></ndc-dynamic>\n        </a>\n    </ng-container>\n    <ng-container *ngIf=\"!isLink() && !hasOnClick()\">\n        <ndc-dynamic\n            [ndcDynamicComponent]=\"componentType\"\n            [ndcDynamicInputs]=\"{\n            'mode': mode,\n            'originalMode': originalMode,\n            'field': field,\n            'klass': klass,\n            'record': record,\n            'parent': parent\n        }\"\n        ></ndc-dynamic>\n    </ng-container>\n</ng-container>\n\n<ng-container *ngIf=\"field.loading\">\n   <div class= \"dynamic-field\">\n        <div class=\"flex-grow-1 text-break rounded box-loading skeleton-field-content\"></div>\n   </div>\n</ng-container>\n\n<ng-container *ngIf=\"isEdit() && field.formControl && field.formControl.errors\">\n    <ng-container *ngIf=\"field.formControl.invalid && field.formControl.touched\">\n        <div *ngFor=\"let item of field.formControl.errors | keyvalue\" class=\"invalid-feedback d-block\">\n            <scrm-dynamic-label [context]=\"getMessageContext(item.value, record)\"\n                                [fields]=\"{field: field}\"\n                                [labelKey]=\"getMessageLabelKey(item.value)\">\n            </scrm-dynamic-label>\n        </div>\n    </ng-container>\n</ng-container>\n" }]
    }], function () { return [{ type: i1.ModuleNavigation }, { type: i2.ModuleNameMapper }, { type: i3.Router }, { type: i4.DynamicLabelService }, { type: i5.LinkRouteAsyncActionService }, { type: i0.ChangeDetectorRef }]; }, { mode: [{
            type: Input,
            args: ['mode']
        }], originalMode: [{
            type: Input,
            args: ['originalMode']
        }], type: [{
            type: Input,
            args: ['type']
        }], field: [{
            type: Input,
            args: ['field']
        }], record: [{
            type: Input,
            args: ['record']
        }], parent: [{
            type: Input,
            args: ['parent']
        }], klass: [{
            type: Input,
            args: ['klass']
        }], componentType: [{
            type: Input,
            args: ['componentType']
        }], class: [{
            type: HostBinding,
            args: ['class']
        }] }); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZHluYW1pYy1maWVsZC5jb21wb25lbnQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvZmllbGRzL2R5bmFtaWMtZmllbGQvZHluYW1pYy1maWVsZC5jb21wb25lbnQudHMiLCIuLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvZmllbGRzL2R5bmFtaWMtZmllbGQvZHluYW1pYy1maWVsZC5jb21wb25lbnQuaHRtbCJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0dBd0JHO0FBRUgsT0FBTyxFQUFDLGlCQUFpQixFQUFFLFNBQVMsRUFBRSxXQUFXLEVBQUUsS0FBSyxFQUFVLElBQUksRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUM3RixPQUFPLEVBQUMsbUJBQW1CLEVBQXFDLE1BQU0sUUFBUSxDQUFDO0FBQy9FLE9BQU8sRUFBQyxNQUFNLEVBQUMsTUFBTSxpQkFBaUIsQ0FBQztBQUN2QyxPQUFPLEVBQUMsZ0JBQWdCLEVBQUMsTUFBTSx5RUFBeUUsQ0FBQztBQUN6RyxPQUFPLEVBQUMsZ0JBQWdCLEVBQUMsTUFBTSx1RUFBdUUsQ0FBQztBQUN2RyxPQUFPLEVBQUMsbUJBQW1CLEVBQUMsTUFBTSwrQ0FBK0MsQ0FBQztBQUNsRixPQUFPLEVBQ0gsMkJBQTJCLEVBQzlCLE1BQU0sbUZBQW1GLENBQUM7Ozs7Ozs7Ozs7Ozs7SUNOdkYsNkJBQW1DO0lBQy9CLDRCQUFvRDtJQUFqRCxzTEFBUyxlQUFBLGdCQUFTLENBQUEsSUFBQztJQUNsQixpQ0FVZTtJQUNuQixpQkFBSTtJQUNSLDBCQUFlOzs7SUFYSCxlQUFxQztJQUFyQywwREFBcUMsNElBQUE7OztJQVlqRCw2QkFBZ0Q7SUFDNUMsNEJBQStDO0lBQzNDLGlDQVVlO0lBQ25CLGlCQUFJO0lBQ1IsMEJBQWU7OztJQWJSLGVBQXdCO0lBQXhCLDZDQUF3QjtJQUVuQixl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TSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuXG5pbXBvcnQge0NoYW5nZURldGVjdG9yUmVmLCBDb21wb25lbnQsIEhvc3RCaW5kaW5nLCBJbnB1dCwgT25Jbml0LCBUeXBlfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7RURJVEFCTEVfVklFV19NT0RFUywgRmllbGQsIFJlY29yZCwgU3RyaW5nTWFwLCBWaWV3TW9kZX0gZnJvbSAnY29tbW9uJztcbmltcG9ydCB7Um91dGVyfSBmcm9tICdAYW5ndWxhci9yb3V0ZXInO1xuaW1wb3J0IHtNb2R1bGVOYW1lTWFwcGVyfSBmcm9tICcuLi8uLi9zZXJ2aWNlcy9uYXZpZ2F0aW9uL21vZHVsZS1uYW1lLW1hcHBlci9tb2R1bGUtbmFtZS1tYXBwZXIuc2VydmljZSc7XG5pbXBvcnQge01vZHVsZU5hdmlnYXRpb259IGZyb20gJy4uLy4uL3NlcnZpY2VzL25hdmlnYXRpb24vbW9kdWxlLW5hdmlnYXRpb24vbW9kdWxlLW5hdmlnYXRpb24uc2VydmljZSc7XG5pbXBvcnQge0R5bmFtaWNMYWJlbFNlcnZpY2V9IGZyb20gJy4uLy4uL3NlcnZpY2VzL2xhbmd1YWdlL2R5bmFtaWMtbGFiZWwuc2VydmljZSc7XG5pbXBvcnQge1xuICAgIExpbmtSb3V0ZUFzeW5jQWN0aW9uU2VydmljZVxufSBmcm9tICcuLi8uLi9zZXJ2aWNlcy9uYXZpZ2F0aW9uL2xpbmstcm91dGUtYXN5bmMtYWN0aW9uL2xpbmstcm91dGUtYXN5bmMtYWN0aW9uLnNlcnZpY2UnO1xuXG5AQ29tcG9uZW50KHtcbiAgICBzZWxlY3RvcjogJ3Njcm0tZHluYW1pYy1maWVsZCcsXG4gICAgdGVtcGxhdGVVcmw6ICcuL2R5bmFtaWMtZmllbGQuY29tcG9uZW50Lmh0bWwnLFxuICAgIHN0eWxlVXJsczogW11cbn0pXG5leHBvcnQgY2xhc3MgRHluYW1pY0ZpZWxkQ29tcG9uZW50IGltcGxlbWVudHMgT25Jbml0IHtcblxuICAgIEBJbnB1dCgnbW9kZScpIG1vZGU6IHN0cmluZztcbiAgICBASW5wdXQoJ29yaWdpbmFsTW9kZScpIG9yaWdpbmFsTW9kZTogc3RyaW5nO1xuICAgIEBJbnB1dCgndHlwZScpIHR5cGU6IHN0cmluZztcbiAgICBASW5wdXQoJ2ZpZWxkJykgZmllbGQ6IEZpZWxkO1xuICAgIEBJbnB1dCgncmVjb3JkJykgcmVjb3JkOiBSZWNvcmQgPSBudWxsO1xuICAgIEBJbnB1dCgncGFyZW50JykgcGFyZW50OiBSZWNvcmQgPSBudWxsO1xuICAgIEBJbnB1dCgna2xhc3MnKSBrbGFzczogeyBba2V5OiBzdHJpbmddOiBhbnkgfSA9IG51bGw7XG4gICAgQElucHV0KCdjb21wb25lbnRUeXBlJykgY29tcG9uZW50VHlwZTogVHlwZTxhbnk+O1xuXG4gICAgQEhvc3RCaW5kaW5nKCdjbGFzcycpIGNsYXNzID0gJ2R5bmFtaWMtZmllbGQnO1xuXG4gICAgY29uc3RydWN0b3IoXG4gICAgICAgIHByb3RlY3RlZCBuYXZpZ2F0aW9uOiBNb2R1bGVOYXZpZ2F0aW9uLFxuICAgICAgICBwcm90ZWN0ZWQgbW9kdWxlTmFtZU1hcHBlcjogTW9kdWxlTmFtZU1hcHBlcixcbiAgICAgICAgcHJvdGVjdGVkIHJvdXRlcjogUm91dGVyLFxuICAgICAgICBwcm90ZWN0ZWQgZHluYW1pY0xhYmVsU2VydmljZTogRHluYW1pY0xhYmVsU2VydmljZSxcbiAgICAgICAgcHJvdGVjdGVkIGxpbmtSb3V0ZUFzeW5jQWN0aW9uU2VydmljZTogTGlua1JvdXRlQXN5bmNBY3Rpb25TZXJ2aWNlLFxuICAgICAgICBwcml2YXRlIGNkOiBDaGFuZ2VEZXRlY3RvclJlZlxuICAgICkge1xuICAgIH1cblxuICAgIGdldCBnZXRSZWxhdGVMaW5rKCk6IHN0cmluZyB7XG4gICAgICAgIGxldCBsaW5rTW9kdWxlID0gdGhpcy5maWVsZC5kZWZpbml0aW9uLm1vZHVsZTtcblxuICAgICAgICBpZiAodGhpcy5maWVsZC5kZWZpbml0aW9uLnR5cGVfbmFtZSA9PT0gJ3BhcmVudF90eXBlJykge1xuICAgICAgICAgICAgbGlua01vZHVsZSA9IHRoaXMucmVjb3JkLmF0dHJpYnV0ZXMucGFyZW50X3R5cGU7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5maWVsZC5kZWZpbml0aW9uLmlkX25hbWUgJiYgbGlua01vZHVsZSkge1xuICAgICAgICAgICAgY29uc3QgbW9kdWxlTmFtZSA9IHRoaXMubW9kdWxlTmFtZU1hcHBlci50b0Zyb250ZW5kKGxpbmtNb2R1bGUpO1xuICAgICAgICAgICAgcmV0dXJuIHRoaXMubmF2aWdhdGlvbi5nZXRSZWNvcmRSb3V0ZXJMaW5rKFxuICAgICAgICAgICAgICAgIG1vZHVsZU5hbWUsXG4gICAgICAgICAgICAgICAgdGhpcy5yZWNvcmQuYXR0cmlidXRlc1t0aGlzLmZpZWxkLmRlZmluaXRpb24uaWRfbmFtZV1cbiAgICAgICAgICAgICk7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gJyc7XG4gICAgfVxuXG4gICAgbmdPbkluaXQoKTogdm9pZCB7XG4gICAgICAgIHRoaXMuc2V0SG9zdENsYXNzKCk7XG4gICAgICAgIHRoaXMuY2QuZGV0ZWN0Q2hhbmdlcygpO1xuICAgIH1cblxuICAgIGlzTGluaygpOiBib29sZWFuIHtcblxuICAgICAgICBpZiAoRURJVEFCTEVfVklFV19NT0RFUy5pbmNsdWRlcyh0aGlzLm1vZGUgYXMgVmlld01vZGUpKSB7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIXRoaXMuZmllbGQgfHwgIXRoaXMucmVjb3JkKSB7XG4gICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy50eXBlID09PSAncmVsYXRlJykge1xuICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gISEodGhpcz8uZmllbGQ/Lm1ldGFkYXRhICYmIHRoaXM/LmZpZWxkPy5tZXRhZGF0YT8ubGluayk7XG4gICAgfVxuXG4gICAgaGFzT25DbGljaygpOiBib29sZWFuIHtcblxuICAgICAgICBjb25zdCBmaWVsZE1ldGFkYXRhID0gdGhpcz8uZmllbGQ/Lm1ldGFkYXRhID8/IHt9O1xuICAgICAgICBjb25zdCBsaW5rQXN5bmNBY3Rpb24gPSBmaWVsZE1ldGFkYXRhPy5saW5rQXN5bmNBY3Rpb24gPz8gbnVsbDtcbiAgICAgICAgY29uc3QgbGlua09uQ2xpY2sgPSBmaWVsZE1ldGFkYXRhPy5vbkNsaWNrID8/IG51bGw7XG5cbiAgICAgICAgcmV0dXJuICEhKGxpbmtBc3luY0FjdGlvbiB8fCBsaW5rT25DbGljayk7XG4gICAgfVxuXG4gICAgaXNFZGl0KCk6IGJvb2xlYW4ge1xuICAgICAgICByZXR1cm4gdGhpcy5tb2RlID09PSAnZWRpdCcgfHwgdGhpcy5tb2RlID09PSAnZmlsdGVyJztcbiAgICB9XG5cbiAgICBnZXRMaW5rKCk6IHN0cmluZyB7XG4gICAgICAgIGlmICh0aGlzLnR5cGUgPT09ICdyZWxhdGUnKSB7XG4gICAgICAgICAgICByZXR1cm4gdGhpcy5nZXRSZWxhdGVMaW5rO1xuICAgICAgICB9XG5cbiAgICAgICAgY29uc3QgZmllbGRNZXRhZGF0YSA9IHRoaXM/LmZpZWxkPy5tZXRhZGF0YSA/PyBudWxsO1xuICAgICAgICBjb25zdCBsaW5rUm91dGUgPSBmaWVsZE1ldGFkYXRhLmxpbmtSb3V0ZSA/PyBudWxsO1xuICAgICAgICBpZiAoZmllbGRNZXRhZGF0YSAmJiBsaW5rUm91dGUpIHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLmR5bmFtaWNMYWJlbFNlcnZpY2UucGFyc2UobGlua1JvdXRlLCB7fSwgdGhpcy5yZWNvcmQuZmllbGRzKTtcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiB0aGlzLm5hdmlnYXRpb24uZ2V0UmVjb3JkUm91dGVyTGluayh0aGlzLnJlY29yZC5tb2R1bGUsIHRoaXMucmVjb3JkLmlkKTtcbiAgICB9XG5cbiAgICBnZXRNZXNzYWdlQ29udGV4dChpdGVtOiBhbnksIHJlY29yZDogUmVjb3JkKTogU3RyaW5nTWFwIHtcbiAgICAgICAgY29uc3QgY29udGV4dCA9IGl0ZW0gJiYgaXRlbS5tZXNzYWdlICYmIGl0ZW0ubWVzc2FnZS5jb250ZXh0IHx8IHt9O1xuICAgICAgICBjb250ZXh0Lm1vZHVsZSA9IChyZWNvcmQgJiYgcmVjb3JkLm1vZHVsZSkgfHwgJyc7XG5cbiAgICAgICAgcmV0dXJuIGNvbnRleHQ7XG4gICAgfVxuXG4gICAgZ2V0TWVzc2FnZUxhYmVsS2V5KGl0ZW06IGFueSk6IHN0cmluZyB7XG4gICAgICAgIHJldHVybiAoaXRlbSAmJiBpdGVtLm1lc3NhZ2UgJiYgaXRlbS5tZXNzYWdlLmxhYmVsS2V5KSB8fCAnJztcbiAgICB9XG5cbiAgICBvbkNsaWNrKCk6IGJvb2xlYW4ge1xuXG4gICAgICAgIGNvbnN0IGZpZWxkTWV0YWRhdGEgPSB0aGlzPy5maWVsZD8ubWV0YWRhdGEgPz8gbnVsbDtcbiAgICAgICAgaWYgKGZpZWxkTWV0YWRhdGEgJiYgZmllbGRNZXRhZGF0YS5vbkNsaWNrKSB7XG4gICAgICAgICAgICB0aGlzLmZpZWxkLm1ldGFkYXRhLm9uQ2xpY2sodGhpcy5maWVsZCwgdGhpcy5yZWNvcmQpO1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgY29uc3QgbGlua0FzeW5jQWN0aW9uID0gZmllbGRNZXRhZGF0YS5saW5rQXN5bmNBY3Rpb24gPz8gbnVsbDtcbiAgICAgICAgaWYgKGZpZWxkTWV0YWRhdGEgJiYgbGlua0FzeW5jQWN0aW9uKSB7XG4gICAgICAgICAgICB0aGlzLmxpbmtSb3V0ZUFzeW5jQWN0aW9uU2VydmljZS5ydW4obGlua0FzeW5jQWN0aW9uLCB0aGlzLmZpZWxkLCB0aGlzLnJlY29yZCk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnJvdXRlci5uYXZpZ2F0ZUJ5VXJsKHRoaXMuZ2V0TGluaygpKS50aGVuKCk7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICB9XG5cbiAgICBwdWJsaWMgc2V0SG9zdENsYXNzKCkge1xuICAgICAgICBjb25zdCBjbGFzc2VzID0gW107XG4gICAgICAgIGNsYXNzZXMucHVzaCgnZHluYW1pYy1maWVsZCcpO1xuXG4gICAgICAgIGlmICh0aGlzLm1vZGUpIHtcbiAgICAgICAgICAgIGNsYXNzZXMucHVzaCgnZHluYW1pYy1maWVsZC1tb2RlLScgKyB0aGlzLm1vZGUpXG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy50eXBlKSB7XG4gICAgICAgICAgICBjbGFzc2VzLnB1c2goJ2R5bmFtaWMtZmllbGQtdHlwZS0nICsgdGhpcy50eXBlKVxuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHRoaXMuZmllbGQgJiYgdGhpcy5maWVsZC5uYW1lKSB7XG4gICAgICAgICAgICBjbGFzc2VzLnB1c2goJ2R5bmFtaWMtZmllbGQtbmFtZS0nICsgdGhpcy5maWVsZC5uYW1lKVxuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5jbGFzcyA9IGNsYXNzZXMuam9pbignICcpO1xuICAgIH1cblxufVxuIiwiPCEgLS1cbi8qKlxuKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4qIENvcHlyaWdodCAoQykgMjAyMSBTYWxlc0FnaWxpdHkgTHRkLlxuKlxuKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuKiB0aGUgdGVybXMgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSB2ZXJzaW9uIDMgYXMgcHVibGlzaGVkIGJ5IHRoZVxuKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4qIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiogSU4gV0hJQ0ggVEhFIENPUFlSSUdIVCBJUyBPV05FRCBCWSBTQUxFU0FHSUxJVFksIFNBTEVTQUdJTElUWSBESVNDTEFJTVMgVEhFXG4qIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuKlxuKiBUaGlzIHByb2dyYW0gaXMgZGlzdHJpYnV0ZWQgaW4gdGhlIGhvcGUgdGhhdCBpdCB3aWxsIGJlIHVzZWZ1bCwgYnV0IFdJVEhPVVRcbiogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuKiBkZXRhaWxzLlxuKlxuKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiogYWxvbmcgd2l0aCB0aGlzIHByb2dyYW0uICBJZiBub3QsIHNlZSBodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMuXG4qXG4qIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuKiB2ZXJzaW9uIDMsIHRoZXNlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCByZXRhaW4gdGhlIGRpc3BsYXkgb2YgdGhlXG4qIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4qIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4qIHRoZSB3b3JkcyBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiLlxuKi9cbi0tPlxuPG5nLWNvbnRhaW5lciAqbmdJZj1cIiFmaWVsZC5sb2FkaW5nXCI+XG4gICAgPG5nLWNvbnRhaW5lciAqbmdJZj1cImhhc09uQ2xpY2soKVwiPlxuICAgICAgICA8YSAoY2xpY2spPVwib25DbGljaygpXCIgY2xhc3M9XCJjbGlja2FibGUgZmllbGQtbGlua1wiPlxuICAgICAgICAgICAgPG5kYy1keW5hbWljXG4gICAgICAgICAgICAgICAgW25kY0R5bmFtaWNDb21wb25lbnRdPVwiY29tcG9uZW50VHlwZVwiXG4gICAgICAgICAgICAgICAgW25kY0R5bmFtaWNJbnB1dHNdPVwie1xuICAgICAgICAgICAgICAgICdtb2RlJzogbW9kZSxcbiAgICAgICAgICAgICAgICAnb3JpZ2luYWxNb2RlJzogb3JpZ2luYWxNb2RlLFxuICAgICAgICAgICAgICAgICdmaWVsZCc6IGZpZWxkLFxuICAgICAgICAgICAgICAgICdrbGFzcyc6IGtsYXNzLFxuICAgICAgICAgICAgICAgICdyZWNvcmQnOiByZWNvcmQsXG4gICAgICAgICAgICAgICAgJ3BhcmVudCc6IHBhcmVudFxuICAgICAgICAgICAgfVwiXG4gICAgICAgICAgICA+PC9uZGMtZHluYW1pYz5cbiAgICAgICAgPC9hPlxuICAgIDwvbmctY29udGFpbmVyPlxuICAgIDxuZy1jb250YWluZXIgKm5nSWY9XCJpc0xpbmsoKSAmJiAhaGFzT25DbGljaygpXCI+XG4gICAgICAgIDxhIFtyb3V0ZXJMaW5rXT1cImdldExpbmsoKVwiIGNsYXNzPVwiZmllbGQtbGlua1wiPlxuICAgICAgICAgICAgPG5kYy1keW5hbWljXG4gICAgICAgICAgICAgICAgW25kY0R5bmFtaWNDb21wb25lbnRdPVwiY29tcG9uZW50VHlwZVwiXG4gICAgICAgICAgICAgICAgW25kY0R5bmFtaWNJbnB1dHNdPVwie1xuICAgICAgICAgICAgICAgICdtb2RlJzogbW9kZSxcbiAgICAgICAgICAgICAgICAnb3JpZ2luYWxNb2RlJzogb3JpZ2luYWxNb2RlLFxuICAgICAgICAgICAgICAgICdmaWVsZCc6IGZpZWxkLFxuICAgICAgICAgICAgICAgICdrbGFzcyc6IGtsYXNzLFxuICAgICAgICAgICAgICAgICdyZWNvcmQnOiByZWNvcmQsXG4gICAgICAgICAgICAgICAgJ3BhcmVudCc6IHBhcmVudFxuICAgICAgICAgICAgfVwiXG4gICAgICAgICAgICA+PC9uZGMtZHluYW1pYz5cbiAgICAgICAgPC9hPlxuICAgIDwvbmctY29udGFpbmVyPlxuICAgIDxuZy1jb250YWluZXIgKm5nSWY9XCIhaXNMaW5rKCkgJiYgIWhhc09uQ2xpY2soKVwiPlxuICAgICAgICA8bmRjLWR5bmFtaWNcbiAgICAgICAgICAgIFtuZGNEeW5hbWljQ29tcG9uZW50XT1cImNvbXBvbmVudFR5cGVcIlxuICAgICAgICAgICAgW25kY0R5bmFtaWNJbnB1dHNdPVwie1xuICAgICAgICAgICAgJ21vZGUnOiBtb2RlLFxuICAgICAgICAgICAgJ29yaWdpbmFsTW9kZSc6IG9yaWdpbmFsTW9kZSxcbiAgICAgICAgICAgICdmaWVsZCc6IGZpZWxkLFxuICAgICAgICAgICAgJ2tsYXNzJzoga2xhc3MsXG4gICAgICAgICAgICAncmVjb3JkJzogcmVjb3JkLFxuICAgICAgICAgICAgJ3BhcmVudCc6IHBhcmVudFxuICAgICAgICB9XCJcbiAgICAgICAgPjwvbmRjLWR5bmFtaWM+XG4gICAgPC9uZy1jb250YWluZXI+XG48L25nLWNvbnRhaW5lcj5cblxuPG5nLWNvbnRhaW5lciAqbmdJZj1cImZpZWxkLmxvYWRpbmdcIj5cbiAgIDxkaXYgY2xhc3M9IFwiZHluYW1pYy1maWVsZFwiPlxuICAgICAgICA8ZGl2IGNsYXNzPVwiZmxleC1ncm93LTEgdGV4dC1icmVhayByb3VuZGVkIGJveC1sb2FkaW5nIHNrZWxldG9uLWZpZWxkLWNvbnRlbnRcIj48L2Rpdj5cbiAgIDwvZGl2PlxuPC9uZy1jb250YWluZXI+XG5cbjxuZy1jb250YWluZXIgKm5nSWY9XCJpc0VkaXQoKSAmJiBmaWVsZC5mb3JtQ29udHJvbCAmJiBmaWVsZC5mb3JtQ29udHJvbC5lcnJvcnNcIj5cbiAgICA8bmctY29udGFpbmVyICpuZ0lmPVwiZmllbGQuZm9ybUNvbnRyb2wuaW52YWxpZCAmJiBmaWVsZC5mb3JtQ29udHJvbC50b3VjaGVkXCI+XG4gICAgICAgIDxkaXYgKm5nRm9yPVwibGV0IGl0ZW0gb2YgZmllbGQuZm9ybUNvbnRyb2wuZXJyb3JzIHwga2V5dmFsdWVcIiBjbGFzcz1cImludmFsaWQtZmVlZGJhY2sgZC1ibG9ja1wiPlxuICAgICAgICAgICAgPHNjcm0tZHluYW1pYy1sYWJlbCBbY29udGV4dF09XCJnZXRNZXNzYWdlQ29udGV4dChpdGVtLnZhbHVlLCByZWNvcmQpXCJcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgW2ZpZWxkc109XCJ7ZmllbGQ6IGZpZWxkfVwiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFtsYWJlbEtleV09XCJnZXRNZXNzYWdlTGFiZWxLZXkoaXRlbS52YWx1ZSlcIj5cbiAgICAgICAgICAgIDwvc2NybS1keW5hbWljLWxhYmVsPlxuICAgICAgICA8L2Rpdj5cbiAgICA8L25nLWNvbnRhaW5lcj5cbjwvbmctY29udGFpbmVyPlxuIl19