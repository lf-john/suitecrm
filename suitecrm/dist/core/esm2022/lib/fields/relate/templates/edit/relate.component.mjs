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
import { Component, ElementRef, ViewChild } from '@angular/core';
import { emptyObject } from 'common';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ModuleNameMapper } from '../../../../services/navigation/module-name-mapper/module-name-mapper.service';
import { DataTypeFormatter } from '../../../../services/formatters/data-type.formatter.service';
import { RecordListModalComponent } from '../../../../containers/record-list-modal/components/record-list-modal/record-list-modal.component';
import { BaseRelateComponent } from '../../../base/base-relate.component';
import { LanguageStore } from '../../../../store/language/language.store';
import { RelateService } from '../../../../services/record/relate/relate.service';
import { FieldLogicManager } from '../../../field-logic/field-logic.manager';
import { FieldLogicDisplayManager } from '../../../field-logic-display/field-logic-display.manager';
import { map, take } from "rxjs/operators";
import { Dropdown } from "primeng/dropdown";
import * as i0 from "@angular/core";
import * as i1 from "../../../../store/language/language.store";
import * as i2 from "../../../../services/formatters/data-type.formatter.service";
import * as i3 from "../../../../services/record/relate/relate.service";
import * as i4 from "../../../../services/navigation/module-name-mapper/module-name-mapper.service";
import * as i5 from "@ng-bootstrap/ng-bootstrap";
import * as i6 from "../../../field-logic/field-logic.manager";
import * as i7 from "../../../field-logic-display/field-logic-display.manager";
import * as i8 from "@angular/common";
import * as i9 from "@angular/forms";
import * as i10 from "../../../../components/button/button.component";
import * as i11 from "primeng/api";
import * as i12 from "../../../../components/image/image.component";
import * as i13 from "primeng/dropdown";
import * as i14 from "primeng/inputtext";
const _c0 = ["tag"];
const _c1 = ["dropdownFilterInput"];
function RelateEditFieldComponent_ng_container_1_ng_template_4_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelement(0, "scrm-image", 8);
} }
function RelateEditFieldComponent_ng_container_1_ng_template_5_Template(rf, ctx) { if (rf & 1) {
    const _r8 = i0.ɵɵgetCurrentView();
    i0.ɵɵelementStart(0, "div", 9);
    i0.ɵɵlistener("click", function RelateEditFieldComponent_ng_container_1_ng_template_5_Template_div_click_0_listener($event) { return $event.stopPropagation(); });
    i0.ɵɵelementStart(1, "input", 10, 11);
    i0.ɵɵlistener("ngModelChange", function RelateEditFieldComponent_ng_container_1_ng_template_5_Template_input_ngModelChange_1_listener($event) { i0.ɵɵrestoreView(_r8); const ctx_r7 = i0.ɵɵnextContext(2); return i0.ɵɵresetView(ctx_r7.filterValue = $event); })("keyup", function RelateEditFieldComponent_ng_container_1_ng_template_5_Template_input_keyup_1_listener($event) { i0.ɵɵrestoreView(_r8); const ctx_r9 = i0.ɵɵnextContext(2); return i0.ɵɵresetView(ctx_r9.onFilterInput($event)); });
    i0.ɵɵelementEnd();
    i0.ɵɵelement(3, "scrm-image", 12);
    i0.ɵɵelementEnd();
} if (rf & 2) {
    const ctx_r3 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngModel", ctx_r3.filterValue);
} }
function RelateEditFieldComponent_ng_container_1_Template(rf, ctx) { if (rf & 1) {
    const _r11 = i0.ɵɵgetCurrentView();
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵelementStart(1, "div", 2)(2, "p-dropdown", 3, 4);
    i0.ɵɵlistener("ngModelChange", function RelateEditFieldComponent_ng_container_1_Template_p_dropdown_ngModelChange_2_listener($event) { i0.ɵɵrestoreView(_r11); const ctx_r10 = i0.ɵɵnextContext(); return i0.ɵɵresetView(ctx_r10.selectedValue = $event); })("onChange", function RelateEditFieldComponent_ng_container_1_Template_p_dropdown_onChange_2_listener($event) { i0.ɵɵrestoreView(_r11); const ctx_r12 = i0.ɵɵnextContext(); return i0.ɵɵresetView(ctx_r12.onAdd($event.value)); })("onLazyLoad", function RelateEditFieldComponent_ng_container_1_Template_p_dropdown_onLazyLoad_2_listener() { i0.ɵɵrestoreView(_r11); const ctx_r13 = i0.ɵɵnextContext(); return i0.ɵɵresetView(ctx_r13.onFilter()); })("onShow", function RelateEditFieldComponent_ng_container_1_Template_p_dropdown_onShow_2_listener() { i0.ɵɵrestoreView(_r11); const ctx_r14 = i0.ɵɵnextContext(); return i0.ɵɵresetView(ctx_r14.focusFilterInput()); })("onHide", function RelateEditFieldComponent_ng_container_1_Template_p_dropdown_onHide_2_listener() { i0.ɵɵrestoreView(_r11); const _r1 = i0.ɵɵreference(3); const ctx_r15 = i0.ɵɵnextContext(); return i0.ɵɵresetView(ctx_r15.resetFunction(_r1.options)); })("onClear", function RelateEditFieldComponent_ng_container_1_Template_p_dropdown_onClear_2_listener($event) { i0.ɵɵrestoreView(_r11); const ctx_r16 = i0.ɵɵnextContext(); return i0.ɵɵresetView(ctx_r16.onClear($event)); });
    i0.ɵɵtemplate(4, RelateEditFieldComponent_ng_container_1_ng_template_4_Template, 1, 0, "ng-template", 5);
    i0.ɵɵtemplate(5, RelateEditFieldComponent_ng_container_1_ng_template_5_Template, 4, 1, "ng-template", 6);
    i0.ɵɵelementEnd()();
    i0.ɵɵelementStart(6, "div");
    i0.ɵɵelement(7, "scrm-button", 7);
    i0.ɵɵelementEnd();
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r0 = i0.ɵɵnextContext();
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("options", ctx_r0.options)("ngModel", ctx_r0.selectedValue)("optionLabel", ctx_r0.getRelateFieldName())("emptyMessage", ctx_r0.emptyFilterLabel)("emptyFilterMessage", ctx_r0.emptyFilterLabel)("placeholder", ctx_r0.placeholderLabel)("autoOptionFocus", false)("autofocusFilter", false)("focusOnHover", true)("filter", true)("lazy", true)("dataKey", "id")("filterBy", ctx_r0.getRelateFieldName())("showClear", true)("styleClass", "w-100 " + ctx_r0.getInvalidClass());
    i0.ɵɵadvance(5);
    i0.ɵɵproperty("config", ctx_r0.selectButton);
} }
class RelateEditFieldComponent extends BaseRelateComponent {
    /**
     * Constructor
     *
     * @param {object} languages service
     * @param {object} typeFormatter service
     * @param {object} relateService service
     * @param {object} moduleNameMapper service
     * @param {object} modalService service
     * @param {object} logic
     * @param {object} logicDisplay
     */
    constructor(languages, typeFormatter, relateService, moduleNameMapper, modalService, logic, logicDisplay) {
        super(languages, typeFormatter, relateService, moduleNameMapper, logic, logicDisplay);
        this.languages = languages;
        this.typeFormatter = typeFormatter;
        this.relateService = relateService;
        this.moduleNameMapper = moduleNameMapper;
        this.modalService = modalService;
        this.logic = logic;
        this.logicDisplay = logicDisplay;
        this.selectedValue = {};
        this.placeholderLabel = '';
        this.emptyFilterLabel = '';
        this.filterValue = '';
    }
    /**
     * On init handler
     */
    ngOnInit() {
        super.ngOnInit();
        this.init();
        this.getTranslatedLabels();
        this.selectButton = {
            klass: ['btn', 'btn-sm', 'btn-outline-secondary', 'm-0', 'border-0'],
            onClick: () => {
                this.showSelectModal();
            },
            icon: 'cursor'
        };
    }
    init() {
        super.init();
        this.initValue();
        const idFieldName = this.getRelateIdField();
        if (idFieldName && this.record && this.record.fields && this.record.fields[idFieldName]) {
            this.idField = this.record.fields[idFieldName];
        }
    }
    initValue() {
        if (!this.field.valueObject) {
            this.selectedValue = {};
            this.field.formControl.setValue('');
            return;
        }
        if (!this.field.valueObject.id) {
            this.selectedValue = {};
            this.field.formControl.setValue('');
            return;
        }
        if (this.field?.metadata?.relateSearchField) {
            const rname = this.field?.definition?.rname ?? 'name';
            this.field.valueObject[this.field.metadata.relateSearchField] = this.field.valueObject[rname];
        }
        this.selectedValue = this.field.valueObject;
        this.options = [this.field.valueObject];
    }
    /**
     * Handle newly added item
     *
     * @param {object} item added
     */
    onAdd(item) {
        if (item) {
            const relateName = this.getRelateFieldName();
            this.setValue(item.id, item[relateName]);
            return;
        }
        this.setValue('', '');
        this.selectedValue = {};
        return;
    }
    /**
     * Handle item removal
     */
    onRemove() {
        this.setValue('', '');
        this.selectedValue = {};
        this.options = [];
    }
    onClear(event) {
        this.selectedValue = {};
        this.filterValue = '';
        this.options = [];
        this.onRemove();
    }
    onFilter() {
        const relateName = this.getRelateFieldName();
        this.filterValue = this.filterValue ?? '';
        const matches = this.filterValue.match(/^\s*$/g);
        if (matches && matches.length) {
            this.filterValue = '';
        }
        let term = this.filterValue;
        this.search(term).pipe(take(1), map(data => data.filter(item => item[relateName] !== '')), map(filteredData => filteredData.map(item => ({
            id: item.id,
            [relateName]: item[relateName]
        })))).subscribe(filteredOptions => {
            this.options = filteredOptions;
            if (!this?.selectedValue || !this?.selectedValue?.id) {
                return;
            }
            let found = false;
            filteredOptions.some(value => {
                if (value?.id === this.selectedValue.id) {
                    found = true;
                    return true;
                }
                return false;
            });
            if (found === false && this.selectedValue) {
                this.options.push(this.selectedValue);
            }
        });
    }
    resetFunction(options) {
        this.filterValue = '';
        this.options = [];
        if (!emptyObject(this.selectedValue)) {
            this.options = [this.selectedValue];
        }
    }
    onFilterInput(event) {
        event.stopPropagation();
        this.tag.onLazyLoad.emit();
    }
    /**
     * Set value on field
     *
     * @param {string} id to set
     * @param {string} relateValue to set
     */
    setValue(id, relateValue) {
        const relate = this.buildRelate(id, relateValue);
        this.field.value = relateValue;
        this.field.valueObject = relate;
        this.field.formControl.setValue(relateValue);
        this.field.formControl.markAsDirty();
        if (this.idField) {
            this.idField.value = id;
            this.idField.formControl.setValue(id);
            this.idField.formControl.markAsDirty();
        }
        if (relateValue) {
            const relateName = this.getRelateFieldName();
            this.selectedValue = { id: id, [relateName]: relateValue };
        }
        this.options = [this.selectedValue];
    }
    /**
     * Show record selection modal
     */
    showSelectModal() {
        const modal = this.modalService.open(RecordListModalComponent, { size: 'xl', scrollable: true });
        modal.componentInstance.module = this.getRelatedModule();
        modal.result.then((data) => {
            if (!data || !data.selection || !data.selection.selected) {
                return;
            }
            const record = this.getSelectedRecord(data);
            this.setItem(record);
        });
    }
    /**
     * Get Selected Record
     *
     * @param {object} data RecordListModalResult
     * @returns {object} Record
     */
    getSelectedRecord(data) {
        let id = '';
        Object.keys(data.selection.selected).some(selected => {
            id = selected;
            return true;
        });
        let record = null;
        data.records.some(rec => {
            if (rec && rec.id === id) {
                record = rec;
                return true;
            }
        });
        return record;
    }
    /**
     * Set the record as the selected item
     *
     * @param {object} record to set
     */
    setItem(record) {
        this.tag.writeValue(record.attributes);
        this.onAdd(record.attributes);
    }
    getTranslatedLabels() {
        this.placeholderLabel = this.languages.getAppString('LBL_SELECT_ITEM') || '';
        this.emptyFilterLabel = this.languages.getAppString('ERR_SEARCH_NO_RESULTS') || '';
    }
    focusFilterInput() {
        this.dropdownFilterInput.nativeElement.focus();
    }
    static { this.ɵfac = function RelateEditFieldComponent_Factory(t) { return new (t || RelateEditFieldComponent)(i0.ɵɵdirectiveInject(i1.LanguageStore), i0.ɵɵdirectiveInject(i2.DataTypeFormatter), i0.ɵɵdirectiveInject(i3.RelateService), i0.ɵɵdirectiveInject(i4.ModuleNameMapper), i0.ɵɵdirectiveInject(i5.NgbModal), i0.ɵɵdirectiveInject(i6.FieldLogicManager), i0.ɵɵdirectiveInject(i7.FieldLogicDisplayManager)); }; }
    static { this.ɵcmp = /*@__PURE__*/ i0.ɵɵdefineComponent({ type: RelateEditFieldComponent, selectors: [["scrm-relate-edit"]], viewQuery: function RelateEditFieldComponent_Query(rf, ctx) { if (rf & 1) {
            i0.ɵɵviewQuery(_c0, 5);
            i0.ɵɵviewQuery(_c1, 5);
        } if (rf & 2) {
            let _t;
            i0.ɵɵqueryRefresh(_t = i0.ɵɵloadQuery()) && (ctx.tag = _t.first);
            i0.ɵɵqueryRefresh(_t = i0.ɵɵloadQuery()) && (ctx.dropdownFilterInput = _t.first);
        } }, features: [i0.ɵɵProvidersFeature([RelateService]), i0.ɵɵInheritDefinitionFeature], decls: 2, vars: 1, consts: [[1, "d-flex", "align-items-center"], [4, "ngIf"], [1, "flex-grow-1", "w-100", "mr-1"], [3, "options", "ngModel", "optionLabel", "emptyMessage", "emptyFilterMessage", "placeholder", "autoOptionFocus", "autofocusFilter", "focusOnHover", "filter", "lazy", "dataKey", "filterBy", "showClear", "styleClass", "ngModelChange", "onChange", "onLazyLoad", "onShow", "onHide", "onClear"], ["tag", ""], ["pTemplate", "dropdownicon"], ["pTemplate", "filter"], [3, "config"], ["image", "down_carret"], [1, "p-dropdown-filter-container", 3, "click"], ["type", "text", "pInputText", "", "autocomplete", "off", "tabindex", "0", 1, "p-dropdown-filter", "p-component", 3, "ngModel", "ngModelChange", "keyup"], ["dropdownFilterInput", ""], ["image", "search", 1, "p-element", "p-dropdown-filter-search-icon"]], template: function RelateEditFieldComponent_Template(rf, ctx) { if (rf & 1) {
            i0.ɵɵelementStart(0, "div", 0);
            i0.ɵɵtemplate(1, RelateEditFieldComponent_ng_container_1_Template, 8, 16, "ng-container", 1);
            i0.ɵɵelementEnd();
        } if (rf & 2) {
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx.initModule);
        } }, dependencies: [i8.NgIf, i9.DefaultValueAccessor, i9.NgControlStatus, i9.NgModel, i10.ButtonComponent, i11.PrimeTemplate, i12.ImageComponent, i13.Dropdown, i14.InputText], encapsulation: 2 }); }
}
export { RelateEditFieldComponent };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(RelateEditFieldComponent, [{
        type: Component,
        args: [{ selector: 'scrm-relate-edit', providers: [RelateService], template: "<! --\n/**\n* SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.\n* Copyright (C) 2021 SalesAgility Ltd.\n*\n* This program is free software; you can redistribute it and/or modify it under\n* the terms of the GNU Affero General Public License version 3 as published by the\n* Free Software Foundation with the addition of the following permission added\n* to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK\n* IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE\n* WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.\n*\n* This program is distributed in the hope that it will be useful, but WITHOUT\n* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS\n* FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more\n* details.\n*\n* You should have received a copy of the GNU Affero General Public License\n* along with this program.  If not, see http://www.gnu.org/licenses.\n*\n* In accordance with Section 7(b) of the GNU Affero General Public License\n* version 3, these Appropriate Legal Notices must retain the display of the\n* \"Supercharged by SuiteCRM\" logo. If the display of the logos is not reasonably\n* feasible for technical reasons, the Appropriate Legal Notices must display\n* the words \"Supercharged by SuiteCRM\".\n*/\n-->\n<div class=\"d-flex align-items-center\">\n    <ng-container *ngIf=\"initModule\">\n        <div class=\"flex-grow-1 w-100 mr-1\">\n            <p-dropdown\n                #tag\n                [options]=\"options\"\n                [(ngModel)]=\"selectedValue\"\n                [optionLabel]=\"getRelateFieldName()\"\n                (onChange)=\"onAdd($event.value)\"\n                (onLazyLoad)=\"onFilter()\"\n                (onShow)=\"focusFilterInput()\"\n                (onHide)=\"resetFunction(tag.options)\"\n                [emptyMessage]=\"emptyFilterLabel\"\n                [emptyFilterMessage]=\"emptyFilterLabel\"\n                [placeholder]=\"placeholderLabel\"\n                [autoOptionFocus]=\"false\"\n                [autofocusFilter]=\"false\"\n                [focusOnHover] = \"true\"\n                [filter]=\"true\"\n                [lazy]=\"true\"\n                [dataKey]=\"'id'\"\n                [filterBy]=\"getRelateFieldName()\"\n                [showClear]=\"true\"\n                (onClear)=\"onClear($event)\"\n                [styleClass]=\"'w-100 ' + getInvalidClass()\"\n            >\n                <ng-template pTemplate=\"dropdownicon\">\n                    <scrm-image image=\"down_carret\"></scrm-image>\n                </ng-template>\n                <ng-template pTemplate=\"filter\" let-options=\"options\">\n\n                    <div class=\"p-dropdown-filter-container\" (click)=\"$event.stopPropagation()\">\n                        <input #dropdownFilterInput\n                               type=\"text\"\n                               pInputText\n                               autocomplete=\"off\"\n                               class=\"p-dropdown-filter p-component\"\n                               [(ngModel)]=\"filterValue\"\n                               (keyup)=\"onFilterInput($event)\"\n                               tabindex=\"0\">\n                        <scrm-image image=\"search\" class=\"p-element p-dropdown-filter-search-icon\"></scrm-image>\n                    </div>\n\n                </ng-template>\n            </p-dropdown>\n        </div>\n        <div>\n            <scrm-button [config]=\"selectButton\">\n            </scrm-button>\n        </div>\n    </ng-container>\n</div>\n\n" }]
    }], function () { return [{ type: i1.LanguageStore }, { type: i2.DataTypeFormatter }, { type: i3.RelateService }, { type: i4.ModuleNameMapper }, { type: i5.NgbModal }, { type: i6.FieldLogicManager }, { type: i7.FieldLogicDisplayManager }]; }, { tag: [{
            type: ViewChild,
            args: ['tag']
        }], dropdownFilterInput: [{
            type: ViewChild,
            args: ['dropdownFilterInput']
        }] }); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicmVsYXRlLmNvbXBvbmVudC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL2NvcmUvYXBwL2NvcmUvc3JjL2xpYi9maWVsZHMvcmVsYXRlL3RlbXBsYXRlcy9lZGl0L3JlbGF0ZS5jb21wb25lbnQudHMiLCIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvZmllbGRzL3JlbGF0ZS90ZW1wbGF0ZXMvZWRpdC9yZWxhdGUuY29tcG9uZW50Lmh0bWwiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQXdCRztBQUVILE9BQU8sRUFBQyxTQUFTLEVBQUUsVUFBVSxFQUFFLFNBQVMsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUMvRCxPQUFPLEVBQWdDLFdBQVcsRUFBZ0IsTUFBTSxRQUFRLENBQUM7QUFDakYsT0FBTyxFQUFDLFFBQVEsRUFBQyxNQUFNLDRCQUE0QixDQUFDO0FBQ3BELE9BQU8sRUFBQyxnQkFBZ0IsRUFBQyxNQUFNLCtFQUErRSxDQUFDO0FBQy9HLE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLDZEQUE2RCxDQUFDO0FBQzlGLE9BQU8sRUFDSCx3QkFBd0IsRUFDM0IsTUFBTSxtR0FBbUcsQ0FBQztBQUMzRyxPQUFPLEVBQUMsbUJBQW1CLEVBQUMsTUFBTSxxQ0FBcUMsQ0FBQztBQUN4RSxPQUFPLEVBQUMsYUFBYSxFQUFDLE1BQU0sMkNBQTJDLENBQUM7QUFDeEUsT0FBTyxFQUFDLGFBQWEsRUFBQyxNQUFNLG1EQUFtRCxDQUFDO0FBSWhGLE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLDBDQUEwQyxDQUFDO0FBQzNFLE9BQU8sRUFBQyx3QkFBd0IsRUFBQyxNQUFNLDBEQUEwRCxDQUFDO0FBQ2xHLE9BQU8sRUFBQyxHQUFHLEVBQUUsSUFBSSxFQUFDLE1BQU0sZ0JBQWdCLENBQUM7QUFDekMsT0FBTyxFQUFDLFFBQVEs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Z0JBQ2IsT0FBTyxJQUFJLENBQUM7YUFDZjtRQUNMLENBQUMsQ0FBQyxDQUFDO1FBRUgsT0FBTyxNQUFNLENBQUM7SUFDbEIsQ0FBQztJQUVEOzs7O09BSUc7SUFDTyxPQUFPLENBQUMsTUFBYztRQUM1QixJQUFJLENBQUMsR0FBRyxDQUFDLFVBQVUsQ0FBQyxNQUFNLENBQUMsVUFBVSxDQUFDLENBQUM7UUFDdkMsSUFBSSxDQUFDLEtBQUssQ0FBQyxNQUFNLENBQUMsVUFBVSxDQUFDLENBQUM7SUFDbEMsQ0FBQztJQUVNLG1CQUFtQjtRQUN0QixJQUFJLENBQUMsZ0JBQWdCLEdBQUcsSUFBSSxDQUFDLFNBQVMsQ0FBQyxZQUFZLENBQUMsaUJBQWlCLENBQUMsSUFBSSxFQUFFLENBQUM7UUFDN0UsSUFBSSxDQUFDLGdCQUFnQixHQUFHLElBQUksQ0FBQyxTQUFTLENBQUMsWUFBWSxDQUFDLHVCQUF1QixDQUFDLElBQUksRUFBRSxDQUFDO0lBQ3ZGLENBQUM7SUFFRCxnQkFBZ0I7UUFDWixJQUFJLENBQUMsbUJBQW1CLENBQUMsYUFBYSxDQUFDLEtBQUssRUFBRSxDQUFBO0lBQ2xELENBQUM7eUZBcFFRLHdCQUF3QjtvRUFBeEIsd0JBQXdCOzs7Ozs7OzhDQUZ0QixDQUFDLGFBQWEsQ0FBQztZQ3RCOUIsOEJBQXVDO1lBQ25DLDRGQWlEZTtZQUNuQixpQkFBTTs7WUFsRGEsZUFBZ0I7WUFBaEIscUNBQWdCOzs7U0R1QnRCLHdCQUF3Qjt1RkFBeEIsd0JBQXdCO2NBTnBDLFNBQVM7MkJBQ0ksa0JBQWtCLGFBR2pCLENBQUMsYUFBYSxDQUFDO3lQQUdSLEdBQUc7a0JBQXBCLFNBQVM7bUJBQUMsS0FBSztZQUNrQixtQkFBbUI7a0JBQXBELFNBQVM7bUJBQUMscUJBQXFCIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuXG5pbXBvcnQge0NvbXBvbmVudCwgRWxlbWVudFJlZiwgVmlld0NoaWxkfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7QXR0cmlidXRlTWFwLCBCdXR0b25JbnRlcmZhY2UsIGVtcHR5T2JqZWN0LCBGaWVsZCwgUmVjb3JkfSBmcm9tICdjb21tb24nO1xuaW1wb3J0IHtOZ2JNb2RhbH0gZnJvbSAnQG5nLWJvb3RzdHJhcC9uZy1ib290c3RyYXAnO1xuaW1wb3J0IHtNb2R1bGVOYW1lTWFwcGVyfSBmcm9tICcuLi8uLi8uLi8uLi9zZXJ2aWNlcy9uYXZpZ2F0aW9uL21vZHVsZS1uYW1lLW1hcHBlci9tb2R1bGUtbmFtZS1tYXBwZXIuc2VydmljZSc7XG5pbXBvcnQge0RhdGFUeXBlRm9ybWF0dGVyfSBmcm9tICcuLi8uLi8uLi8uLi9zZXJ2aWNlcy9mb3JtYXR0ZXJzL2RhdGEtdHlwZS5mb3JtYXR0ZXIuc2VydmljZSc7XG5pbXBvcnQge1xuICAgIFJlY29yZExpc3RNb2RhbENvbXBvbmVudFxufSBmcm9tICcuLi8uLi8uLi8uLi9jb250YWluZXJzL3JlY29yZC1saXN0LW1vZGFsL2NvbXBvbmVudHMvcmVjb3JkLWxpc3QtbW9kYWwvcmVjb3JkLWxpc3QtbW9kYWwuY29tcG9uZW50JztcbmltcG9ydCB7QmFzZVJlbGF0ZUNvbXBvbmVudH0gZnJvbSAnLi4vLi4vLi4vYmFzZS9iYXNlLXJlbGF0ZS5jb21wb25lbnQnO1xuaW1wb3J0IHtMYW5ndWFnZVN0b3JlfSBmcm9tICcuLi8uLi8uLi8uLi9zdG9yZS9sYW5ndWFnZS9sYW5ndWFnZS5zdG9yZSc7XG5pbXBvcnQge1JlbGF0ZVNlcnZpY2V9IGZyb20gJy4uLy4uLy4uLy4uL3NlcnZpY2VzL3JlY29yZC9yZWxhdGUvcmVsYXRlLnNlcnZpY2UnO1xuaW1wb3J0IHtcbiAgICBSZWNvcmRMaXN0TW9kYWxSZXN1bHRcbn0gZnJvbSAnLi4vLi4vLi4vLi4vY29udGFpbmVycy9yZWNvcmQtbGlzdC1tb2RhbC9jb21wb25lbnRzL3JlY29yZC1saXN0LW1vZGFsL3JlY29yZC1saXN0LW1vZGFsLm1vZGVsJztcbmltcG9ydCB7RmllbGRMb2dpY01hbmFnZXJ9IGZyb20gJy4uLy4uLy4uL2ZpZWxkLWxvZ2ljL2ZpZWxkLWxvZ2ljLm1hbmFnZXInO1xuaW1wb3J0IHtGaWVsZExvZ2ljRGlzcGxheU1hbmFnZXJ9IGZyb20gJy4uLy4uLy4uL2ZpZWxkLWxvZ2ljLWRpc3BsYXkvZmllbGQtbG9naWMtZGlzcGxheS5tYW5hZ2VyJztcbmltcG9ydCB7bWFwLCB0YWtlfSBmcm9tIFwicnhqcy9vcGVyYXRvcnNcIjtcbmltcG9ydCB7RHJvcGRvd24sIERyb3Bkb3duRmlsdGVyT3B0aW9uc30gZnJvbSBcInByaW1lbmcvZHJvcGRvd25cIjtcblxuQENvbXBvbmVudCh7XG4gICAgc2VsZWN0b3I6ICdzY3JtLXJlbGF0ZS1lZGl0JyxcbiAgICB0ZW1wbGF0ZVVybDogJy4vcmVsYXRlLmNvbXBvbmVudC5odG1sJyxcbiAgICBzdHlsZVVybHM6IFtdLFxuICAgIHByb3ZpZGVyczogW1JlbGF0ZVNlcnZpY2VdXG59KVxuZXhwb3J0IGNsYXNzIFJlbGF0ZUVkaXRGaWVsZENvbXBvbmVudCBleHRlbmRzIEJhc2VSZWxhdGVDb21wb25lbnQge1xuICAgIEBWaWV3Q2hpbGQoJ3RhZycpIHRhZzogRHJvcGRvd247XG4gICAgQFZpZXdDaGlsZCgnZHJvcGRvd25GaWx0ZXJJbnB1dCcpIGRyb3Bkb3duRmlsdGVySW5wdXQ6IEVsZW1lbnRSZWY7XG4gICAgc2VsZWN0QnV0dG9uOiBCdXR0b25JbnRlcmZhY2U7XG4gICAgaWRGaWVsZDogRmllbGQ7XG4gICAgc2VsZWN0ZWRWYWx1ZTogQXR0cmlidXRlTWFwID0ge307XG5cbiAgICBwbGFjZWhvbGRlckxhYmVsOiBzdHJpbmcgPSAnJztcbiAgICBlbXB0eUZpbHRlckxhYmVsOiBzdHJpbmcgPSAnJztcbiAgICBmaWx0ZXJWYWx1ZTogc3RyaW5nIHwgdW5kZWZpbmVkID0gJyc7XG5cbiAgICAvKipcbiAgICAgKiBDb25zdHJ1Y3RvclxuICAgICAqXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGxhbmd1YWdlcyBzZXJ2aWNlXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHR5cGVGb3JtYXR0ZXIgc2VydmljZVxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSByZWxhdGVTZXJ2aWNlIHNlcnZpY2VcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gbW9kdWxlTmFtZU1hcHBlciBzZXJ2aWNlXG4gICAgICogQHBhcmFtIHtvYmplY3R9IG1vZGFsU2VydmljZSBzZXJ2aWNlXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGxvZ2ljXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGxvZ2ljRGlzcGxheVxuICAgICAqL1xuICAgIGNvbnN0cnVjdG9yKFxuICAgICAgICBwcm90ZWN0ZWQgbGFuZ3VhZ2VzOiBMYW5ndWFnZVN0b3JlLFxuICAgICAgICBwcm90ZWN0ZWQgdHlwZUZvcm1hdHRlcjogRGF0YVR5cGVGb3JtYXR0ZXIsXG4gICAgICAgIHByb3RlY3RlZCByZWxhdGVTZXJ2aWNlOiBSZWxhdGVTZXJ2aWNlLFxuICAgICAgICBwcm90ZWN0ZWQgbW9kdWxlTmFtZU1hcHBlcjogTW9kdWxlTmFtZU1hcHBlcixcbiAgICAgICAgcHJvdGVjdGVkIG1vZGFsU2VydmljZTogTmdiTW9kYWwsXG4gICAgICAgIHByb3RlY3RlZCBsb2dpYzogRmllbGRMb2dpY01hbmFnZXIsXG4gICAgICAgIHByb3RlY3RlZCBsb2dpY0Rpc3BsYXk6IEZpZWxkTG9naWNEaXNwbGF5TWFuYWdlclxuICAgICkge1xuICAgICAgICBzdXBlcihsYW5ndWFnZXMsIHR5cGVGb3JtYXR0ZXIsIHJlbGF0ZVNlcnZpY2UsIG1vZHVsZU5hbWVNYXBwZXIsIGxvZ2ljLCBsb2dpY0Rpc3BsYXkpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIE9uIGluaXQgaGFuZGxlclxuICAgICAqL1xuICAgIG5nT25Jbml0KCk6IHZvaWQge1xuXG4gICAgICAgIHN1cGVyLm5nT25Jbml0KCk7XG4gICAgICAgIHRoaXMuaW5pdCgpO1xuICAgICAgICB0aGlzLmdldFRyYW5zbGF0ZWRMYWJlbHMoKTtcblxuICAgICAgICB0aGlzLnNlbGVjdEJ1dHRvbiA9IHtcbiAgICAgICAgICAgIGtsYXNzOiBbJ2J0bicsICdidG4tc20nLCAnYnRuLW91dGxpbmUtc2Vjb25kYXJ5JywgJ20tMCcsICdib3JkZXItMCddLFxuICAgICAgICAgICAgb25DbGljazogKCk6IHZvaWQgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMuc2hvd1NlbGVjdE1vZGFsKCk7XG4gICAgICAgICAgICB9LFxuICAgICAgICAgICAgaWNvbjogJ2N1cnNvcidcbiAgICAgICAgfSBhcyBCdXR0b25JbnRlcmZhY2U7XG5cbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgaW5pdCgpOiB2b2lkIHtcblxuICAgICAgICBzdXBlci5pbml0KCk7XG5cbiAgICAgICAgdGhpcy5pbml0VmFsdWUoKTtcblxuICAgICAgICBjb25zdCBpZEZpZWxkTmFtZSA9IHRoaXMuZ2V0UmVsYXRlSWRGaWVsZCgpO1xuICAgICAgICBpZiAoaWRGaWVsZE5hbWUgJiYgdGhpcy5yZWNvcmQgJiYgdGhpcy5yZWNvcmQuZmllbGRzICYmIHRoaXMucmVjb3JkLmZpZWxkc1tpZEZpZWxkTmFtZV0pIHtcbiAgICAgICAgICAgIHRoaXMuaWRGaWVsZCA9IHRoaXMucmVjb3JkLmZpZWxkc1tpZEZpZWxkTmFtZV07XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgaW5pdFZhbHVlKCk6IHZvaWQge1xuICAgICAgICBpZiAoIXRoaXMuZmllbGQudmFsdWVPYmplY3QpIHtcbiAgICAgICAgICAgIHRoaXMuc2VsZWN0ZWRWYWx1ZSA9IHt9O1xuICAgICAgICAgICAgdGhpcy5maWVsZC5mb3JtQ29udHJvbC5zZXRWYWx1ZSgnJyk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIXRoaXMuZmllbGQudmFsdWVPYmplY3QuaWQpIHtcbiAgICAgICAgICAgIHRoaXMuc2VsZWN0ZWRWYWx1ZSA9IHt9O1xuICAgICAgICAgICAgdGhpcy5maWVsZC5mb3JtQ29udHJvbC5zZXRWYWx1ZSgnJyk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5maWVsZD8ubWV0YWRhdGE/LnJlbGF0ZVNlYXJjaEZpZWxkKSB7XG4gICAgICAgICAgICBjb25zdCBybmFtZSA9IHRoaXMuZmllbGQ/LmRlZmluaXRpb24/LnJuYW1lID8/ICduYW1lJztcbiAgICAgICAgICAgIHRoaXMuZmllbGQudmFsdWVPYmplY3RbdGhpcy5maWVsZC5tZXRhZGF0YS5yZWxhdGVTZWFyY2hGaWVsZF0gPSB0aGlzLmZpZWxkLnZhbHVlT2JqZWN0W3JuYW1lXTtcbiAgICAgICAgfVxuXG4gICAgICAgIHRoaXMuc2VsZWN0ZWRWYWx1ZSA9IHRoaXMuZmllbGQudmFsdWVPYmplY3Q7XG4gICAgICAgIHRoaXMub3B0aW9ucyA9IFt0aGlzLmZpZWxkLnZhbHVlT2JqZWN0XTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBIYW5kbGUgbmV3bHkgYWRkZWQgaXRlbVxuICAgICAqXG4gICAgICogQHBhcmFtIHtvYmplY3R9IGl0ZW0gYWRkZWRcbiAgICAgKi9cbiAgICBvbkFkZChpdGVtKTogdm9pZCB7XG4gICAgICAgIGlmIChpdGVtKSB7XG4gICAgICAgICAgICBjb25zdCByZWxhdGVOYW1lID0gdGhpcy5nZXRSZWxhdGVGaWVsZE5hbWUoKTtcbiAgICAgICAgICAgIHRoaXMuc2V0VmFsdWUoaXRlbS5pZCwgaXRlbVtyZWxhdGVOYW1lXSk7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnNldFZhbHVlKCcnLCAnJyk7XG4gICAgICAgIHRoaXMuc2VsZWN0ZWRWYWx1ZSA9IHt9O1xuXG4gICAgICAgIHJldHVybjtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBIYW5kbGUgaXRlbSByZW1vdmFsXG4gICAgICovXG4gICAgb25SZW1vdmUoKTogdm9pZCB7XG4gICAgICAgIHRoaXMuc2V0VmFsdWUoJycsICcnKTtcbiAgICAgICAgdGhpcy5zZWxlY3RlZFZhbHVlID0ge307XG4gICAgICAgIHRoaXMub3B0aW9ucyA9IFtdO1xuICAgIH1cblxuICAgIG9uQ2xlYXIoZXZlbnQpOiB2b2lkIHtcbiAgICAgICAgdGhpcy5zZWxlY3RlZFZhbHVlID0ge307XG4gICAgICAgIHRoaXMuZmlsdGVyVmFsdWUgPSAnJztcbiAgICAgICAgdGhpcy5vcHRpb25zID0gW107XG4gICAgICAgIHRoaXMub25SZW1vdmUoKTtcbiAgICB9XG5cbiAgICBvbkZpbHRlcigpOiB2b2lkIHtcbiAgICAgICAgY29uc3QgcmVsYXRlTmFtZSA9IHRoaXMuZ2V0UmVsYXRlRmllbGROYW1lKCk7XG4gICAgICAgIHRoaXMuZmlsdGVyVmFsdWUgPSB0aGlzLmZpbHRlclZhbHVlID8/ICcnO1xuICAgICAgICBjb25zdCBtYXRjaGVzID0gdGhpcy5maWx0ZXJWYWx1ZS5tYXRjaCgvXlxccyokL2cpO1xuICAgICAgICBpZiAobWF0Y2hlcyAmJiBtYXRjaGVzLmxlbmd0aCkge1xuICAgICAgICAgICAgdGhpcy5maWx0ZXJWYWx1ZSA9ICcnO1xuICAgICAgICB9XG4gICAgICAgIGxldCB0ZXJtID0gdGhpcy5maWx0ZXJWYWx1ZTtcbiAgICAgICAgdGhpcy5zZWFyY2godGVybSkucGlwZShcbiAgICAgICAgICAgIHRha2UoMSksXG4gICAgICAgICAgICBtYXAoZGF0YSA9PiBkYXRhLmZpbHRlcihpdGVtID0+IGl0ZW1bcmVsYXRlTmFtZV0gIT09ICcnKSksXG4gICAgICAgICAgICBtYXAoZmlsdGVyZWREYXRhID0+IGZpbHRlcmVkRGF0YS5tYXAoaXRlbSA9PiAoe1xuICAgICAgICAgICAgICAgIGlkOiBpdGVtLmlkLFxuICAgICAgICAgICAgICAgIFtyZWxhdGVOYW1lXTogaXRlbVtyZWxhdGVOYW1lXVxuICAgICAgICAgICAgfSkpKVxuICAgICAgICApLnN1YnNjcmliZShmaWx0ZXJlZE9wdGlvbnMgPT4ge1xuICAgICAgICAgICAgdGhpcy5vcHRpb25zID0gZmlsdGVyZWRPcHRpb25zO1xuXG4gICAgICAgICAgICBpZiAoIXRoaXM/LnNlbGVjdGVkVmFsdWUgfHwgIXRoaXM/LnNlbGVjdGVkVmFsdWU/LmlkKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBsZXQgZm91bmQgPSBmYWxzZTtcbiAgICAgICAgICAgIGZpbHRlcmVkT3B0aW9ucy5zb21lKHZhbHVlID0+IHtcbiAgICAgICAgICAgICAgICBpZiAodmFsdWU/LmlkID09PSB0aGlzLnNlbGVjdGVkVmFsdWUuaWQpIHtcbiAgICAgICAgICAgICAgICAgICAgZm91bmQgPSB0cnVlO1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gdHJ1ZTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgaWYgKGZvdW5kID09PSBmYWxzZSAmJiB0aGlzLnNlbGVjdGVkVmFsdWUpIHtcbiAgICAgICAgICAgICAgICB0aGlzLm9wdGlvbnMucHVzaCh0aGlzLnNlbGVjdGVkVmFsdWUpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KVxuICAgIH1cblxuICAgIHJlc2V0RnVuY3Rpb24ob3B0aW9uczogRHJvcGRvd25GaWx0ZXJPcHRpb25zKSB7XG4gICAgICAgIHRoaXMuZmlsdGVyVmFsdWUgPSAnJztcbiAgICAgICAgdGhpcy5vcHRpb25zID0gW107XG4gICAgICAgIGlmICghZW1wdHlPYmplY3QodGhpcy5zZWxlY3RlZFZhbHVlKSkge1xuICAgICAgICAgICAgdGhpcy5vcHRpb25zID0gW3RoaXMuc2VsZWN0ZWRWYWx1ZV07XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBvbkZpbHRlcklucHV0KGV2ZW50OiBLZXlib2FyZEV2ZW50KSB7XG4gICAgICAgIGV2ZW50LnN0b3BQcm9wYWdhdGlvbigpXG4gICAgICAgIHRoaXMudGFnLm9uTGF6eUxvYWQuZW1pdCgpXG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogU2V0IHZhbHVlIG9uIGZpZWxkXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gaWQgdG8gc2V0XG4gICAgICogQHBhcmFtIHtzdHJpbmd9IHJlbGF0ZVZhbHVlIHRvIHNldFxuICAgICAqL1xuICAgIHByb3RlY3RlZCBzZXRWYWx1ZShpZDogc3RyaW5nLCByZWxhdGVWYWx1ZTogc3RyaW5nKTogdm9pZCB7XG4gICAgICAgIGNvbnN0IHJlbGF0ZSA9IHRoaXMuYnVpbGRSZWxhdGUoaWQsIHJlbGF0ZVZhbHVlKTtcbiAgICAgICAgdGhpcy5maWVsZC52YWx1ZSA9IHJlbGF0ZVZhbHVlO1xuICAgICAgICB0aGlzLmZpZWxkLnZhbHVlT2JqZWN0ID0gcmVsYXRlO1xuICAgICAgICB0aGlzLmZpZWxkLmZvcm1Db250cm9sLnNldFZhbHVlKHJlbGF0ZVZhbHVlKTtcbiAgICAgICAgdGhpcy5maWVsZC5mb3JtQ29udHJvbC5tYXJrQXNEaXJ0eSgpO1xuXG4gICAgICAgIGlmICh0aGlzLmlkRmllbGQpIHtcbiAgICAgICAgICAgIHRoaXMuaWRGaWVsZC52YWx1ZSA9IGlkO1xuICAgICAgICAgICAgdGhpcy5pZEZpZWxkLmZvcm1Db250cm9sLnNldFZhbHVlKGlkKTtcbiAgICAgICAgICAgIHRoaXMuaWRGaWVsZC5mb3JtQ29udHJvbC5tYXJrQXNEaXJ0eSgpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKHJlbGF0ZVZhbHVlKSB7XG4gICAgICAgICAgICBjb25zdCByZWxhdGVOYW1lID0gdGhpcy5nZXRSZWxhdGVGaWVsZE5hbWUoKTtcbiAgICAgICAgICAgIHRoaXMuc2VsZWN0ZWRWYWx1ZSA9IHtpZDogaWQsIFtyZWxhdGVOYW1lXTogcmVsYXRlVmFsdWV9O1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5vcHRpb25zID0gW3RoaXMuc2VsZWN0ZWRWYWx1ZV07XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogU2hvdyByZWNvcmQgc2VsZWN0aW9uIG1vZGFsXG4gICAgICovXG4gICAgcHJvdGVjdGVkIHNob3dTZWxlY3RNb2RhbCgpOiB2b2lkIHtcbiAgICAgICAgY29uc3QgbW9kYWwgPSB0aGlzLm1vZGFsU2VydmljZS5vcGVuKFJlY29yZExpc3RNb2RhbENvbXBvbmVudCwge3NpemU6ICd4bCcsIHNjcm9sbGFibGU6IHRydWV9KTtcblxuICAgICAgICBtb2RhbC5jb21wb25lbnRJbnN0YW5jZS5tb2R1bGUgPSB0aGlzLmdldFJlbGF0ZWRNb2R1bGUoKTtcblxuICAgICAgICBtb2RhbC5yZXN1bHQudGhlbigoZGF0YTogUmVjb3JkTGlzdE1vZGFsUmVzdWx0KSA9PiB7XG5cbiAgICAgICAgICAgIGlmICghZGF0YSB8fCAhZGF0YS5zZWxlY3Rpb24gfHwgIWRhdGEuc2VsZWN0aW9uLnNlbGVjdGVkKSB7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBjb25zdCByZWNvcmQgPSB0aGlzLmdldFNlbGVjdGVkUmVjb3JkKGRhdGEpO1xuICAgICAgICAgICAgdGhpcy5zZXRJdGVtKHJlY29yZCk7XG4gICAgICAgIH0pO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldCBTZWxlY3RlZCBSZWNvcmRcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBkYXRhIFJlY29yZExpc3RNb2RhbFJlc3VsdFxuICAgICAqIEByZXR1cm5zIHtvYmplY3R9IFJlY29yZFxuICAgICAqL1xuICAgIHByb3RlY3RlZCBnZXRTZWxlY3RlZFJlY29yZChkYXRhOiBSZWNvcmRMaXN0TW9kYWxSZXN1bHQpOiBSZWNvcmQge1xuICAgICAgICBsZXQgaWQgPSAnJztcbiAgICAgICAgT2JqZWN0LmtleXMoZGF0YS5zZWxlY3Rpb24uc2VsZWN0ZWQpLnNvbWUoc2VsZWN0ZWQgPT4ge1xuICAgICAgICAgICAgaWQgPSBzZWxlY3RlZDtcbiAgICAgICAgICAgIHJldHVybiB0cnVlO1xuICAgICAgICB9KTtcblxuICAgICAgICBsZXQgcmVjb3JkOiBSZWNvcmQgPSBudWxsO1xuXG4gICAgICAgIGRhdGEucmVjb3Jkcy5zb21lKHJlYyA9PiB7XG4gICAgICAgICAgICBpZiAocmVjICYmIHJlYy5pZCA9PT0gaWQpIHtcbiAgICAgICAgICAgICAgICByZWNvcmQgPSByZWM7XG4gICAgICAgICAgICAgICAgcmV0dXJuIHRydWU7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0pO1xuXG4gICAgICAgIHJldHVybiByZWNvcmQ7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogU2V0IHRoZSByZWNvcmQgYXMgdGhlIHNlbGVjdGVkIGl0ZW1cbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSByZWNvcmQgdG8gc2V0XG4gICAgICovXG4gICAgcHJvdGVjdGVkIHNldEl0ZW0ocmVjb3JkOiBSZWNvcmQpOiB2b2lkIHtcbiAgICAgICAgdGhpcy50YWcud3JpdGVWYWx1ZShyZWNvcmQuYXR0cmlidXRlcyk7XG4gICAgICAgIHRoaXMub25BZGQocmVjb3JkLmF0dHJpYnV0ZXMpO1xuICAgIH1cblxuICAgIHB1YmxpYyBnZXRUcmFuc2xhdGVkTGFiZWxzKCk6IHZvaWQge1xuICAgICAgICB0aGlzLnBsYWNlaG9sZGVyTGFiZWwgPSB0aGlzLmxhbmd1YWdlcy5nZXRBcHBTdHJpbmcoJ0xCTF9TRUxFQ1RfSVRFTScpIHx8ICcnO1xuICAgICAgICB0aGlzLmVtcHR5RmlsdGVyTGFiZWwgPSB0aGlzLmxhbmd1YWdlcy5nZXRBcHBTdHJpbmcoJ0VSUl9TRUFSQ0hfTk9fUkVTVUxUUycpIHx8ICcnO1xuICAgIH1cblxuICAgIGZvY3VzRmlsdGVySW5wdXQoKSB7XG4gICAgICAgIHRoaXMuZHJvcGRvd25GaWx0ZXJJbnB1dC5uYXRpdmVFbGVtZW50LmZvY3VzKClcbiAgICB9XG59XG4iLCI8ISAtLVxuLyoqXG4qIFN1aXRlQ1JNIGlzIGEgY3VzdG9tZXIgcmVsYXRpb25zaGlwIG1hbmFnZW1lbnQgcHJvZ3JhbSBkZXZlbG9wZWQgYnkgU2FsZXNBZ2lsaXR5IEx0ZC5cbiogQ29weXJpZ2h0IChDKSAyMDIxIFNhbGVzQWdpbGl0eSBMdGQuXG4qXG4qIFRoaXMgcHJvZ3JhbSBpcyBmcmVlIHNvZnR3YXJlOyB5b3UgY2FuIHJlZGlzdHJpYnV0ZSBpdCBhbmQvb3IgbW9kaWZ5IGl0IHVuZGVyXG4qIHRoZSB0ZXJtcyBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIHZlcnNpb24gMyBhcyBwdWJsaXNoZWQgYnkgdGhlXG4qIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiogdG8gU2VjdGlvbiAxNSBhcyBwZXJtaXR0ZWQgaW4gU2VjdGlvbiA3KGEpOiBGT1IgQU5ZIFBBUlQgT0YgVEhFIENPVkVSRUQgV09SS1xuKiBJTiBXSElDSCBUSEUgQ09QWVJJR0hUIElTIE9XTkVEIEJZIFNBTEVTQUdJTElUWSwgU0FMRVNBR0lMSVRZIERJU0NMQUlNUyBUSEVcbiogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4qXG4qIFRoaXMgcHJvZ3JhbSBpcyBkaXN0cmlidXRlZCBpbiB0aGUgaG9wZSB0aGF0IGl0IHdpbGwgYmUgdXNlZnVsLCBidXQgV0lUSE9VVFxuKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuKiBGT1IgQSBQQVJUSUNVTEFSIFBVUlBPU0UuIFNlZSB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIGZvciBtb3JlXG4qIGRldGFpbHMuXG4qXG4qIFlvdSBzaG91bGQgaGF2ZSByZWNlaXZlZCBhIGNvcHkgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuKiBhbG9uZyB3aXRoIHRoaXMgcHJvZ3JhbS4gIElmIG5vdCwgc2VlIGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy5cbipcbiogSW4gYWNjb3JkYW5jZSB3aXRoIFNlY3Rpb24gNyhiKSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4qIHZlcnNpb24gMywgdGhlc2UgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IHJldGFpbiB0aGUgZGlzcGxheSBvZiB0aGVcbiogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiogZmVhc2libGUgZm9yIHRlY2huaWNhbCByZWFzb25zLCB0aGUgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IGRpc3BsYXlcbiogdGhlIHdvcmRzIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIuXG4qL1xuLS0+XG48ZGl2IGNsYXNzPVwiZC1mbGV4IGFsaWduLWl0ZW1zLWNlbnRlclwiPlxuICAgIDxuZy1jb250YWluZXIgKm5nSWY9XCJpbml0TW9kdWxlXCI+XG4gICAgICAgIDxkaXYgY2xhc3M9XCJmbGV4LWdyb3ctMSB3LTEwMCBtci0xXCI+XG4gICAgICAgICAgICA8cC1kcm9wZG93blxuICAgICAgICAgICAgICAgICN0YWdcbiAgICAgICAgICAgICAgICBbb3B0aW9uc109XCJvcHRpb25zXCJcbiAgICAgICAgICAgICAgICBbKG5nTW9kZWwpXT1cInNlbGVjdGVkVmFsdWVcIlxuICAgICAgICAgICAgICAgIFtvcHRpb25MYWJlbF09XCJnZXRSZWxhdGVGaWVsZE5hbWUoKVwiXG4gICAgICAgICAgICAgICAgKG9uQ2hhbmdlKT1cIm9uQWRkKCRldmVudC52YWx1ZSlcIlxuICAgICAgICAgICAgICAgIChvbkxhenlMb2FkKT1cIm9uRmlsdGVyKClcIlxuICAgICAgICAgICAgICAgIChvblNob3cpPVwiZm9jdXNGaWx0ZXJJbnB1dCgpXCJcbiAgICAgICAgICAgICAgICAob25IaWRlKT1cInJlc2V0RnVuY3Rpb24odGFnLm9wdGlvbnMpXCJcbiAgICAgICAgICAgICAgICBbZW1wdHlNZXNzYWdlXT1cImVtcHR5RmlsdGVyTGFiZWxcIlxuICAgICAgICAgICAgICAgIFtlbXB0eUZpbHRlck1lc3NhZ2VdPVwiZW1wdHlGaWx0ZXJMYWJlbFwiXG4gICAgICAgICAgICAgICAgW3BsYWNlaG9sZGVyXT1cInBsYWNlaG9sZGVyTGFiZWxcIlxuICAgICAgICAgICAgICAgIFthdXRvT3B0aW9uRm9jdXNdPVwiZmFsc2VcIlxuICAgICAgICAgICAgICAgIFthdXRvZm9jdXNGaWx0ZXJdPVwiZmFsc2VcIlxuICAgICAgICAgICAgICAgIFtmb2N1c09uSG92ZXJdID0gXCJ0cnVlXCJcbiAgICAgICAgICAgICAgICBbZmlsdGVyXT1cInRydWVcIlxuICAgICAgICAgICAgICAgIFtsYXp5XT1cInRydWVcIlxuICAgICAgICAgICAgICAgIFtkYXRhS2V5XT1cIidpZCdcIlxuICAgICAgICAgICAgICAgIFtmaWx0ZXJCeV09XCJnZXRSZWxhdGVGaWVsZE5hbWUoKVwiXG4gICAgICAgICAgICAgICAgW3Nob3dDbGVhcl09XCJ0cnVlXCJcbiAgICAgICAgICAgICAgICAob25DbGVhcik9XCJvbkNsZWFyKCRldmVudClcIlxuICAgICAgICAgICAgICAgIFtzdHlsZUNsYXNzXT1cIid3LTEwMCAnICsgZ2V0SW52YWxpZENsYXNzKClcIlxuICAgICAgICAgICAgPlxuICAgICAgICAgICAgICAgIDxuZy10ZW1wbGF0ZSBwVGVtcGxhdGU9XCJkcm9wZG93bmljb25cIj5cbiAgICAgICAgICAgICAgICAgICAgPHNjcm0taW1hZ2UgaW1hZ2U9XCJkb3duX2NhcnJldFwiPjwvc2NybS1pbWFnZT5cbiAgICAgICAgICAgICAgICA8L25nLXRlbXBsYXRlPlxuICAgICAgICAgICAgICAgIDxuZy10ZW1wbGF0ZSBwVGVtcGxhdGU9XCJmaWx0ZXJcIiBsZXQtb3B0aW9ucz1cIm9wdGlvbnNcIj5cblxuICAgICAgICAgICAgICAgICAgICA8ZGl2IGNsYXNzPVwicC1kcm9wZG93bi1maWx0ZXItY29udGFpbmVyXCIgKGNsaWNrKT1cIiRldmVudC5zdG9wUHJvcGFnYXRpb24oKVwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgPGlucHV0ICNkcm9wZG93bkZpbHRlcklucHV0XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZT1cInRleHRcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHBJbnB1dFRleHRcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBhdXRvY29tcGxldGU9XCJvZmZcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNsYXNzPVwicC1kcm9wZG93bi1maWx0ZXIgcC1jb21wb25lbnRcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFsobmdNb2RlbCldPVwiZmlsdGVyVmFsdWVcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIChrZXl1cCk9XCJvbkZpbHRlcklucHV0KCRldmVudClcIlxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRhYmluZGV4PVwiMFwiPlxuICAgICAgICAgICAgICAgICAgICAgICAgPHNjcm0taW1hZ2UgaW1hZ2U9XCJzZWFyY2hcIiBjbGFzcz1cInAtZWxlbWVudCBwLWRyb3Bkb3duLWZpbHRlci1zZWFyY2gtaWNvblwiPjwvc2NybS1pbWFnZT5cbiAgICAgICAgICAgICAgICAgICAgPC9kaXY+XG5cbiAgICAgICAgICAgICAgICA8L25nLXRlbXBsYXRlPlxuICAgICAgICAgICAgPC9wLWRyb3Bkb3duPlxuICAgICAgICA8L2Rpdj5cbiAgICAgICAgPGRpdj5cbiAgICAgICAgICAgIDxzY3JtLWJ1dHRvbiBbY29uZmlnXT1cInNlbGVjdEJ1dHRvblwiPlxuICAgICAgICAgICAgPC9zY3JtLWJ1dHRvbj5cbiAgICAgICAgPC9kaXY+XG4gICAgPC9uZy1jb250YWluZXI+XG48L2Rpdj5cblxuIl19