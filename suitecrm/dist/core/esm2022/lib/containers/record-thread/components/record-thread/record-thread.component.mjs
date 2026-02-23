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
import { Component, ElementRef, Input, ViewChild } from '@angular/core';
import { combineLatestWith } from 'rxjs';
import { RecordThreadStoreFactory } from '../../store/record-thread/record-thread.store.factory';
import { map, take, tap } from 'rxjs/operators';
import { isVoid } from 'common';
import { RecordThreadItemStoreFactory } from '../../store/record-thread/record-thread-item.store.factory';
import { RecordManager } from '../../../../services/record/record.manager';
import { MessageService } from '../../../../services/message/message.service';
import { RecordThreadListActionsAdapterFactory } from "../../adapters/record-thread-list-actions.adapter.factory";
import * as i0 from "@angular/core";
import * as i1 from "../../store/record-thread/record-thread.store.factory";
import * as i2 from "../../store/record-thread/record-thread-item.store.factory";
import * as i3 from "../../../../services/record/record.manager";
import * as i4 from "../../../../services/message/message.service";
import * as i5 from "../../adapters/record-thread-list-actions.adapter.factory";
import * as i6 from "@angular/common";
import * as i7 from "../../../../components/button/button.component";
import * as i8 from "../../../../components/label/label.component";
import * as i9 from "../record-thread-item/record-thread-item.component";
import * as i10 from "../../../../components/loading-spinner/loading-spinner.component";
import * as i11 from "../../../../components/action-group-menu/action-group-menu.component";
const _c0 = ["list"];
function RecordThreadComponent_div_1_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", 4);
    i0.ɵɵelement(1, "scrm-label", 5);
    i0.ɵɵelementEnd();
} }
function RecordThreadComponent_div_2_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", 6);
    i0.ɵɵelement(1, "scrm-loading-spinner", 7);
    i0.ɵɵelementEnd();
} if (rf & 2) {
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("overlay", true);
} }
function RecordThreadComponent_div_3_div_2_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", 13);
    i0.ɵɵelement(1, "scrm-button", 14);
    i0.ɵɵelementEnd();
} if (rf & 2) {
    const ctx_r6 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("config", ctx_r6.getLoadMoreButton());
} }
function RecordThreadComponent_div_3_div_3_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", null, 15);
    i0.ɵɵelement(2, "scrm-record-thread-item", 14);
    i0.ɵɵelementEnd();
} if (rf & 2) {
    const record_r10 = ctx.$implicit;
    const _r11 = i0.ɵɵreference(1);
    const ctx_r7 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("config", ctx_r7.buildItem(record_r10, _r11));
} }
function RecordThreadComponent_div_3_div_5_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", 16);
    i0.ɵɵelement(1, "scrm-button", 14);
    i0.ɵɵelementEnd();
} if (rf & 2) {
    const ctx_r8 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("config", ctx_r8.getLoadMoreButton());
} }
function RecordThreadComponent_div_3_ng_container_6_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵelement(1, "scrm-action-group-menu", 17);
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r9 = i0.ɵɵnextContext(2);
    let tmp_0_0;
    let tmp_1_0;
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("buttonClass", (tmp_0_0 = ctx_r9.config.listActionsButtonClass) !== null && tmp_0_0 !== undefined ? tmp_0_0 : "")("buttonGroupClass", (tmp_1_0 = ctx_r9.config.listActionsButtonGroupClass) !== null && tmp_1_0 !== undefined ? tmp_1_0 : "")("config", ctx_r9.listActionAdapter);
} }
function RecordThreadComponent_div_3_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", 8, 9);
    i0.ɵɵtemplate(2, RecordThreadComponent_div_3_div_2_Template, 2, 1, "div", 10);
    i0.ɵɵtemplate(3, RecordThreadComponent_div_3_div_3_Template, 3, 1, "div", 11);
    i0.ɵɵelementStart(4, "div");
    i0.ɵɵtemplate(5, RecordThreadComponent_div_3_div_5_Template, 2, 1, "div", 12);
    i0.ɵɵtemplate(6, RecordThreadComponent_div_3_ng_container_6_Template, 2, 3, "ng-container", 3);
    i0.ɵɵelementEnd()();
} if (rf & 2) {
    const ctx_r2 = i0.ɵɵnextContext();
    let tmp_3_0;
    i0.ɵɵproperty("ngStyle", ctx_r2.getMaxHeight());
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("ngIf", ctx_r2.loadMorePosition === "top" && !ctx_r2.allLoaded());
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngForOf", ctx_r2.records);
    i0.ɵɵadvance(1);
    i0.ɵɵclassMap((tmp_3_0 = ctx_r2.config.listActionsClass) !== null && tmp_3_0 !== undefined ? tmp_3_0 : "");
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", ctx_r2.loadMorePosition === "bottom" && !ctx_r2.allLoaded());
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", ctx_r2.config.listActions);
} }
function RecordThreadComponent_div_4_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div")(1, "h6", 18);
    i0.ɵɵelement(2, "scrm-label", 19);
    i0.ɵɵelementEnd()();
} if (rf & 2) {
    const ctx_r3 = i0.ɵɵnextContext();
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("labelKey", ctx_r3.config.noDataLabel || "LBL_NO_DATA");
} }
function RecordThreadComponent_ng_container_5_div_1_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementStart(0, "div", 21)(1, "div", 22);
    i0.ɵɵelement(2, "scrm-record-thread-item", 14);
    i0.ɵɵelementEnd();
    i0.ɵɵelementStart(3, "div", 23);
    i0.ɵɵelement(4, "scrm-button", 14);
    i0.ɵɵelementEnd()();
} if (rf & 2) {
    const ctx_r12 = i0.ɵɵnextContext(2);
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("config", ctx_r12.buildCreateItem());
    i0.ɵɵadvance(2);
    i0.ɵɵproperty("config", ctx_r12.getCreateButton());
} }
function RecordThreadComponent_ng_container_5_Template(rf, ctx) { if (rf & 1) {
    i0.ɵɵelementContainerStart(0);
    i0.ɵɵtemplate(1, RecordThreadComponent_ng_container_5_div_1_Template, 5, 2, "div", 20);
    i0.ɵɵelementContainerEnd();
} if (rf & 2) {
    const ctx_r4 = i0.ɵɵnextContext();
    i0.ɵɵadvance(1);
    i0.ɵɵproperty("ngIf", !ctx_r4.loading);
} }
class RecordThreadComponent {
    constructor(storeFactory, itemFactory, recordManager, message, actionAdapterFactory) {
        this.storeFactory = storeFactory;
        this.itemFactory = itemFactory;
        this.recordManager = recordManager;
        this.message = message;
        this.actionAdapterFactory = actionAdapterFactory;
        this.loading = false;
        this.maxHeight = 400;
        this.direction = 'asc';
        this.loadMorePosition = 'top';
        this.shouldResetScroll = false;
        this.subs = [];
    }
    ngOnInit() {
        if (!isVoid(this.config.maxListHeight)) {
            this.maxHeight = this.config.maxListHeight;
        }
        if (!this.config.module) {
            return;
        }
        if (!this.config.store) {
            this.store = this.storeFactory.create();
            this.store.setItemMetadata(this.config.itemConfig.metadata);
            this.store.setListMetadata({ actions: this.config.listActions });
            this.store.init(this.config.module, false, this?.config?.pageSize ?? null);
        }
        else {
            this.store = this.config.store;
        }
        this.direction = this.config.direction || this.direction;
        this.setLoadMorePosition();
        this.initCreate();
        this.initDataSubscription();
        if (this.config.filters$) {
            this.subs.push(this.config.filters$.subscribe(filters => {
                this.store.setFilters(filters).pipe(take(1)).subscribe(() => {
                    if (this.config.onRefresh) {
                        this.config.onRefresh();
                    }
                });
            }));
        }
        else {
            this.store.load(false).subscribe(() => {
                if (this.config.onRefresh) {
                    this.config.onRefresh();
                }
            });
        }
        const autoRefreshFrequency = this?.config?.autoRefreshFrequency ?? 0;
        if (autoRefreshFrequency && this.store) {
            const min = this.config.autoRefreshDeviationMin ?? -15;
            const max = this.config.autoRefreshDeviationMax ?? 15;
            this.subs.push(this.store.initAutoRefresh(autoRefreshFrequency, min, max, this.config.onRefresh).subscribe());
        }
        this.initLoading();
        this.listActionAdapter = this.actionAdapterFactory.create(this.store, this.config);
    }
    setLoadMorePosition() {
        this.loadMorePosition = this.direction === 'asc' ? 'top' : 'bottom';
        if (this.config.loadMorePosition) {
            this.loadMorePosition = this.config.loadMorePosition;
        }
    }
    ngAfterViewInit() {
        this.shouldResetScroll = true;
        this.resetScroll();
    }
    ngOnDestroy() {
        if (!(this?.config?.store ?? null)) {
            this.store.clear();
        }
        this.store = null;
        this.subs.forEach(sub => sub.unsubscribe());
    }
    buildItem(item, itemRef) {
        let klass = 'record-thread-list-item';
        if (this.config.itemConfig.klass) {
            klass += ' ' + this.config.itemConfig.klass;
        }
        return {
            ...this.config.itemConfig,
            store: item,
            threadStore: this.store,
            klass: klass,
            containerClass: this.config.itemConfig.containerClass,
            flexDirection: this.config?.itemConfig?.flexDirection ?? '',
            expanded: () => {
                this.scrollToItem(itemRef);
            },
            collapsed: () => {
                this.scrollToItem(itemRef);
            }
        };
    }
    getLoadMoreButton() {
        return {
            klass: 'load-more-button btn btn-link btn-sm',
            labelKey: 'LBL_LOAD_MORE',
            onClick: () => {
                if (this?.config?.onLoadMore) {
                    this.store.getRecordList().records$.pipe(take(1), tap(() => this.config.onLoadMore())).subscribe();
                }
                this.store.loadMore();
            }
        };
    }
    buildCreateItem() {
        return {
            ...this.config.createConfig,
            store: this.createStore,
            rowClass: { 'pt-1': true },
            klass: 'record-thread-create-item',
        };
    }
    getCreateButton() {
        return {
            klass: 'create-thread-item-button btn btn-main btn-sm',
            labelKey: 'LBL_SUBMIT_BUTTON_LABEL',
            onClick: () => {
                this.createStore.validate().pipe(take(1)).subscribe(valid => {
                    if (valid) {
                        this.createStore.save().pipe(take(1)).subscribe(() => {
                            this.store.reload();
                            this.initRecord();
                            this.shouldResetScroll = true;
                            this.message.addSuccessMessageByKey('LBL_ACTION_SUCCESS');
                        });
                        return;
                    }
                    this.message.addWarningMessageByKey('LBL_VALIDATION_ERRORS');
                });
            }
        };
    }
    allLoaded() {
        return !!(this.store && this.store.allLoaded());
    }
    getMaxHeight() {
        if (this.maxHeight == 0) {
            return null;
        }
        return { 'max-height.px': this.maxHeight, 'overflow-y': 'auto' };
    }
    initRecord() {
        const emptyRecord = this.recordManager.buildEmptyRecord(this.config.module);
        this.addPresetFields(emptyRecord);
        let mode = 'edit';
        if (this.config.createConfig && this.config.createConfig.initialMode) {
            mode = this.config.createConfig.initialMode;
        }
        this.createStore.initRecord(emptyRecord, mode, false, true);
    }
    scrollToEnd() {
        if (!this.listContainer || !this.listContainer.nativeElement) {
            return;
        }
        this.scrollTo(this.listContainer.nativeElement.scrollHeight);
    }
    scrollToTop() {
        this.scrollTo(0);
    }
    scrollTo(position) {
        try {
            this.listContainer.nativeElement.scrollTop = position;
        }
        catch (err) {
        }
    }
    scrollToItem(item) {
        if (!item || !this.listContainer || !this.listContainer.nativeElement) {
            return;
        }
        const elementTop = item.offsetTop;
        const parentTop = this.listContainer.nativeElement.offsetTop;
        const relativeTop = elementTop - parentTop;
        this.scrollTo(relativeTop);
    }
    resetScroll() {
        if (this.shouldResetScroll === false) {
            return;
        }
        if (this.direction === 'asc') {
            this.scrollToEnd();
        }
        else {
            this.scrollToTop();
        }
        this.shouldResetScroll = false;
    }
    scheduleScrollReset() {
        setTimeout(() => {
            this.resetScroll();
        }, 500);
    }
    initCreate() {
        if (!this.config.create) {
            return;
        }
        this.createStore = this.itemFactory.create();
        this.createStore.setMetadata(this.config.createConfig.metadata);
        this.initRecord();
        this.initPresetFieldsMapping();
    }
    initPresetFieldsMapping() {
        if (!this.config.presetFields$) {
            return;
        }
        this.subs.push(this.config.presetFields$.subscribe(presetFieldValues => {
            if (!presetFieldValues || !Object.keys(presetFieldValues).length) {
                return;
            }
            this.presetFieldValues = presetFieldValues;
            const record = this.createStore.recordStore.getBaseRecord();
            this.addPresetFields(record);
            this.createStore.recordStore.setRecord(record);
        }));
    }
    addPresetFields(record) {
        if (!this.presetFieldValues) {
            return;
        }
        record.attributes = {
            ...this.presetFieldValues,
            ...(record.attributes || {})
        };
    }
    initDataSubscription() {
        this.subs.push(this.store.stores$.subscribe(records => {
            if (!this.records || !this.records.length) {
                this.shouldResetScroll = true;
            }
            if (this.direction === 'asc') {
                this.records = records.reverse();
                this.scheduleScrollReset();
                return;
            }
            this.records = records;
            this.scheduleScrollReset();
        }));
    }
    initLoading() {
        let loading$;
        if (this.createStore && this.createStore.loading$) {
            loading$ = this.store.$loading.pipe(combineLatestWith(this.createStore.loading$));
        }
        else {
            loading$ = this.store.$loading.pipe(map(value => [value]));
        }
        this.subs.push(loading$.subscribe((loadings) => {
            if (!loadings || !loadings.length) {
                this.loading = false;
                return;
            }
            let loading = false;
            loadings.forEach(value => {
                loading = loading || value;
            });
            this.loading = loading;
        }));
    }
    static { this.ɵfac = function RecordThreadComponent_Factory(t) { return new (t || RecordThreadComponent)(i0.ɵɵdirectiveInject(i1.RecordThreadStoreFactory), i0.ɵɵdirectiveInject(i2.RecordThreadItemStoreFactory), i0.ɵɵdirectiveInject(i3.RecordManager), i0.ɵɵdirectiveInject(i4.MessageService), i0.ɵɵdirectiveInject(i5.RecordThreadListActionsAdapterFactory)); }; }
    static { this.ɵcmp = /*@__PURE__*/ i0.ɵɵdefineComponent({ type: RecordThreadComponent, selectors: [["scrm-record-thread"]], viewQuery: function RecordThreadComponent_Query(rf, ctx) { if (rf & 1) {
            i0.ɵɵviewQuery(_c0, 5);
        } if (rf & 2) {
            let _t;
            i0.ɵɵqueryRefresh(_t = i0.ɵɵloadQuery()) && (ctx.listContainer = _t.first);
        } }, inputs: { config: "config" }, decls: 6, vars: 8, consts: [["class", "d-flex record-thread-no-data justify-content-center h3", 4, "ngIf"], ["class", "d-flex record-thread-loading justify-content-center", 4, "ngIf"], ["class", "record-thread-list scrollbar-thick", 3, "ngStyle", 4, "ngIf"], [4, "ngIf"], [1, "d-flex", "record-thread-no-data", "justify-content-center", "h3"], ["labelKey", "LBL_NO_DATA"], [1, "d-flex", "record-thread-loading", "justify-content-center"], [3, "overlay"], [1, "record-thread-list", "scrollbar-thick", 3, "ngStyle"], ["list", ""], ["class", "record-thread-load-more d-flex justify-content-center flex-grow-1", 4, "ngIf"], [4, "ngFor", "ngForOf"], ["class", "record-thread-load-more d-flex justify-content-center", 4, "ngIf"], [1, "record-thread-load-more", "d-flex", "justify-content-center", "flex-grow-1"], [3, "config"], ["item", ""], [1, "record-thread-load-more", "d-flex", "justify-content-center"], [3, "buttonClass", "buttonGroupClass", "config"], [1, "pt-3", "pl-3", "pr-3", "pb-2"], [3, "labelKey"], ["class", "d-flex flex-column record-thread-create-container", 4, "ngIf"], [1, "d-flex", "flex-column", "record-thread-create-container"], [1, "flex-grow-1"], [1, "flex-grow-1", "d-flex", "justify-content-start", "pt-1", "record-thread-create-buttons"]], template: function RecordThreadComponent_Template(rf, ctx) { if (rf & 1) {
            i0.ɵɵelementStart(0, "div");
            i0.ɵɵtemplate(1, RecordThreadComponent_div_1_Template, 2, 0, "div", 0);
            i0.ɵɵtemplate(2, RecordThreadComponent_div_2_Template, 2, 1, "div", 1);
            i0.ɵɵtemplate(3, RecordThreadComponent_div_3_Template, 7, 7, "div", 2);
            i0.ɵɵtemplate(4, RecordThreadComponent_div_4_Template, 3, 1, "div", 3);
            i0.ɵɵtemplate(5, RecordThreadComponent_ng_container_5_Template, 2, 1, "ng-container", 3);
            i0.ɵɵelementEnd();
        } if (rf & 2) {
            i0.ɵɵclassMapInterpolate1("record-thread ", ctx.config && ctx.config.klass || "", "");
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", !ctx.loading && !ctx.records && !ctx.records.length);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx.loading);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx.records && ctx.records.length);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", (!ctx.records || !ctx.records.length) && !ctx.loading && ctx.config.showNoDataMessage);
            i0.ɵɵadvance(1);
            i0.ɵɵproperty("ngIf", ctx.config.create && ctx.createStore);
        } }, dependencies: [i6.NgForOf, i6.NgIf, i6.NgStyle, i7.ButtonComponent, i8.LabelComponent, i9.RecordThreadItemComponent, i10.LoadingSpinnerComponent, i11.ActionGroupMenuComponent], encapsulation: 2 }); }
}
export { RecordThreadComponent };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(RecordThreadComponent, [{
        type: Component,
        args: [{ selector: 'scrm-record-thread', template: "<! --\n/**\n* SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.\n* Copyright (C) 2021 SalesAgility Ltd.\n*\n* This program is free software; you can redistribute it and/or modify it under\n* the terms of the GNU Affero General Public License version 3 as published by the\n* Free Software Foundation with the addition of the following permission added\n* to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK\n* IN WHICH THE COPYRIGHT IS OWNED BY SALESAGILITY, SALESAGILITY DISCLAIMS THE\n* WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.\n*\n* This program is distributed in the hope that it will be useful, but WITHOUT\n* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS\n* FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more\n* details.\n*\n* You should have received a copy of the GNU Affero General Public License\n* along with this program.  If not, see http://www.gnu.org/licenses.\n*\n* In accordance with Section 7(b) of the GNU Affero General Public License\n* version 3, these Appropriate Legal Notices must retain the display of the\n* \"Supercharged by SuiteCRM\" logo. If the display of the logos is not reasonably\n* feasible for technical reasons, the Appropriate Legal Notices must display\n* the words \"Supercharged by SuiteCRM\".\n*/\n-->\n\n<div class=\"record-thread {{(config && config.klass) || ''}}\">\n    <div *ngIf=\"!loading && !records && !records.length\"\n         class=\"d-flex record-thread-no-data justify-content-center h3\">\n        <scrm-label labelKey=\"LBL_NO_DATA\"></scrm-label>\n    </div>\n\n    <div *ngIf=\"loading\" class=\"d-flex record-thread-loading justify-content-center\">\n        <scrm-loading-spinner [overlay]=\"true\"></scrm-loading-spinner>\n    </div>\n\n    <div #list\n         *ngIf=\"records && records.length\"\n         [ngStyle]=\"getMaxHeight()\"\n         class=\"record-thread-list scrollbar-thick\">\n\n        <div *ngIf=\"loadMorePosition === 'top' && !allLoaded()\"\n             class=\"record-thread-load-more d-flex justify-content-center flex-grow-1\">\n            <scrm-button [config]=\"getLoadMoreButton()\"></scrm-button>\n        </div>\n\n        <div #item *ngFor=\"let record of records\">\n            <scrm-record-thread-item [config]=\"buildItem(record, item)\"></scrm-record-thread-item>\n        </div>\n\n        <div [class]=\"config.listActionsClass ?? ''\">\n\n            <div *ngIf=\"loadMorePosition === 'bottom' && !allLoaded()\"\n                 class=\"record-thread-load-more d-flex justify-content-center\">\n                <scrm-button [config]=\"getLoadMoreButton()\"></scrm-button>\n            </div>\n\n            <ng-container *ngIf=\"config.listActions\">\n                <scrm-action-group-menu [buttonClass]=\"config.listActionsButtonClass ?? ''\"\n                                        [buttonGroupClass]=\"config.listActionsButtonGroupClass ?? ''\"\n                                        [config]=\"listActionAdapter\">\n                </scrm-action-group-menu>\n            </ng-container>\n\n        </div>\n\n    </div>\n\n    <div *ngIf=\"(!records || !records.length) && !loading && config.showNoDataMessage\">\n        <h6 class=\"pt-3 pl-3 pr-3 pb-2\">\n            <scrm-label [labelKey]=\"config.noDataLabel || 'LBL_NO_DATA'\"></scrm-label>\n        </h6>\n\n    </div>\n\n    <ng-container *ngIf=\"config.create && createStore\">\n\n        <div *ngIf=\"!loading\"\n             class=\"d-flex flex-column record-thread-create-container\">\n\n            <div class=\"flex-grow-1\">\n                <scrm-record-thread-item [config]=\"buildCreateItem()\"></scrm-record-thread-item>\n            </div>\n\n            <div class=\"flex-grow-1 d-flex justify-content-start pt-1 record-thread-create-buttons\">\n                <scrm-button [config]=\"getCreateButton()\"></scrm-button>\n            </div>\n\n        </div>\n\n    </ng-container>\n\n</div>\n" }]
    }], function () { return [{ type: i1.RecordThreadStoreFactory }, { type: i2.RecordThreadItemStoreFactory }, { type: i3.RecordManager }, { type: i4.MessageService }, { type: i5.RecordThreadListActionsAdapterFactory }]; }, { config: [{
            type: Input
        }], listContainer: [{
            type: ViewChild,
            args: ['list']
        }] }); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicmVjb3JkLXRocmVhZC5jb21wb25lbnQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvY29udGFpbmVycy9yZWNvcmQtdGhyZWFkL2NvbXBvbmVudHMvcmVjb3JkLXRocmVhZC9yZWNvcmQtdGhyZWFkLmNvbXBvbmVudC50cyIsIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL2NvcmUvYXBwL2NvcmUvc3JjL2xpYi9jb250YWluZXJzL3JlY29yZC10aHJlYWQvY29tcG9uZW50cy9yZWNvcmQtdGhyZWFkL3JlY29yZC10aHJlYWQuY29tcG9uZW50Lmh0bWwiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQXdCRztBQUVILE9BQU8sRUFBZ0IsU0FBUyxFQUFFLFVBQVUsRUFBRSxLQUFLLEVBQXFCLFNBQVMsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUN4RyxPQUFPLEVBQUMsaUJBQWlCLEVBQTJCLE1BQU0sTUFBTSxDQUFDO0FBRWpFLE9BQU8sRUFBQyx3QkFBd0IsRUFBQyxNQUFNLHVEQUF1RCxDQUFDO0FBRS9GLE9BQU8sRUFBQyxHQUFHLEVBQUUsSUFBSSxFQUFFLEdBQUcsRUFBQyxNQUFNLGdCQUFnQixDQUFDO0FBRzlDLE9BQU8sRUFBZ0MsTUFBTSxFQUFtQixNQUFNLFFBQVEsQ0FBQztBQUMvRSxPQUFPLEVBQUMsNEJBQTRCLEVBQUMsTUFBTSw0REFBNEQsQ0FBQztBQUN4RyxPQUFPLEVBQUMsYUFBYSxFQUFDLE1BQU0sNENBQTRDLENBQUM7QUFDekUsT0FBTyxFQUFDLGNBQWMsRUFBQyxNQUFNLDhDQUE4QyxDQUFDO0FBRTVFLE9BQU8sRUFBQyxxQ0FBcUMsRUFBQyxNQUFNLDJEQUEyRCxDQUFDOzs7Ozs7Ozs7Ozs7Ozs7SUNWNUcsOEJBQ29FO0lBQ2hFLGdDQUFnRDtJQUNwRCxpQkFBTTs7O0lBRU4sOEJBQWlGO0lBQzdF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Q0FBQyxLQUFLLENBQUMsQ0FBQyxDQUN4QixDQUFBO1NBQ0o7UUFFRCxJQUFJLENBQUMsSUFBSSxDQUFDLElBQUksQ0FBQyxRQUFRLENBQUMsU0FBUyxDQUFDLENBQUMsUUFBUSxFQUFFLEVBQUU7WUFDM0MsSUFBSSxDQUFDLFFBQVEsSUFBSSxDQUFDLFFBQVEsQ0FBQyxNQUFNLEVBQUU7Z0JBQy9CLElBQUksQ0FBQyxPQUFPLEdBQUcsS0FBSyxDQUFDO2dCQUNyQixPQUFPO2FBQ1Y7WUFFRCxJQUFJLE9BQU8sR0FBRyxLQUFLLENBQUM7WUFFcEIsUUFBUSxDQUFDLE9BQU8sQ0FBQyxLQUFLLENBQUMsRUFBRTtnQkFDckIsT0FBTyxHQUFHLE9BQU8sSUFBSSxLQUFLLENBQUM7WUFDL0IsQ0FBQyxDQUFDLENBQUM7WUFDSCxJQUFJLENBQUMsT0FBTyxHQUFHLE9BQU8sQ0FBQztRQUMzQixDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQ1IsQ0FBQztzRkFyVlEscUJBQXFCO29FQUFyQixxQkFBcUI7Ozs7OztZQ25CbEMsMkJBQThEO1lBQzFELHNFQUdNO1lBRU4sc0VBRU07WUFFTixzRUE4Qk07WUFFTixzRUFLTTtZQUVOLHdGQWVlO1lBRW5CLGlCQUFNOztZQWxFRCxxRkFBd0Q7WUFDbkQsZUFBNkM7WUFBN0MsMEVBQTZDO1lBSzdDLGVBQWE7WUFBYixrQ0FBYTtZQUtiLGVBQStCO1lBQS9CLHdEQUErQjtZQStCL0IsZUFBMkU7WUFBM0UsNEdBQTJFO1lBT2xFLGVBQWtDO1lBQWxDLDJEQUFrQzs7O1NEOUJ4QyxxQkFBcUI7dUZBQXJCLHFCQUFxQjtjQUxqQyxTQUFTOzJCQUNJLG9CQUFvQjttT0FNckIsTUFBTTtrQkFBZCxLQUFLO1lBQ2EsYUFBYTtrQkFBL0IsU0FBUzttQkFBQyxNQUFNIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuXG5pbXBvcnQge0FmdGVyVmlld0luaXQsIENvbXBvbmVudCwgRWxlbWVudFJlZiwgSW5wdXQsIE9uRGVzdHJveSwgT25Jbml0LCBWaWV3Q2hpbGR9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHtjb21iaW5lTGF0ZXN0V2l0aCwgT2JzZXJ2YWJsZSwgU3Vic2NyaXB0aW9ufSBmcm9tICdyeGpzJztcbmltcG9ydCB7UmVjb3JkVGhyZWFkU3RvcmV9IGZyb20gJy4uLy4uL3N0b3JlL3JlY29yZC10aHJlYWQvcmVjb3JkLXRocmVhZC5zdG9yZSc7XG5pbXBvcnQge1JlY29yZFRocmVhZFN0b3JlRmFjdG9yeX0gZnJvbSAnLi4vLi4vc3RvcmUvcmVjb3JkLXRocmVhZC9yZWNvcmQtdGhyZWFkLnN0b3JlLmZhY3RvcnknO1xuaW1wb3J0IHtSZWNvcmRUaHJlYWRDb25maWd9IGZyb20gJy4vcmVjb3JkLXRocmVhZC5tb2RlbCc7XG5pbXBvcnQge21hcCwgdGFrZSwgdGFwfSBmcm9tICdyeGpzL29wZXJhdG9ycyc7XG5pbXBvcnQge1JlY29yZFRocmVhZEl0ZW1Db25maWd9IGZyb20gJy4uL3JlY29yZC10aHJlYWQtaXRlbS9yZWNvcmQtdGhyZWFkLWl0ZW0ubW9kZWwnO1xuaW1wb3J0IHtSZWNvcmRUaHJlYWRJdGVtU3RvcmV9IGZyb20gJy4uLy4uL3N0b3JlL3JlY29yZC10aHJlYWQvcmVjb3JkLXRocmVhZC1pdGVtLnN0b3JlJztcbmltcG9ydCB7QXR0cmlidXRlTWFwLCBCdXR0b25JbnRlcmZhY2UsIGlzVm9pZCwgUmVjb3JkLCBWaWV3TW9kZX0gZnJvbSAnY29tbW9uJztcbmltcG9ydCB7UmVjb3JkVGhyZWFkSXRlbVN0b3JlRmFjdG9yeX0gZnJvbSAnLi4vLi4vc3RvcmUvcmVjb3JkLXRocmVhZC9yZWNvcmQtdGhyZWFkLWl0ZW0uc3RvcmUuZmFjdG9yeSc7XG5pbXBvcnQge1JlY29yZE1hbmFnZXJ9IGZyb20gJy4uLy4uLy4uLy4uL3NlcnZpY2VzL3JlY29yZC9yZWNvcmQubWFuYWdlcic7XG5pbXBvcnQge01lc3NhZ2VTZXJ2aWNlfSBmcm9tICcuLi8uLi8uLi8uLi9zZXJ2aWNlcy9tZXNzYWdlL21lc3NhZ2Uuc2VydmljZSc7XG5pbXBvcnQge1JlY29yZFRocmVhZExpc3RBY3Rpb25zQWRhcHRlcn0gZnJvbSBcIi4uLy4uL2FkYXB0ZXJzL3JlY29yZC10aHJlYWQtbGlzdC1hY3Rpb25zLmFkYXB0ZXJcIjtcbmltcG9ydCB7UmVjb3JkVGhyZWFkTGlzdEFjdGlvbnNBZGFwdGVyRmFjdG9yeX0gZnJvbSBcIi4uLy4uL2FkYXB0ZXJzL3JlY29yZC10aHJlYWQtbGlzdC1hY3Rpb25zLmFkYXB0ZXIuZmFjdG9yeVwiO1xuXG5cbkBDb21wb25lbnQoe1xuICAgIHNlbGVjdG9yOiAnc2NybS1yZWNvcmQtdGhyZWFkJyxcbiAgICB0ZW1wbGF0ZVVybDogJy4vcmVjb3JkLXRocmVhZC5jb21wb25lbnQuaHRtbCcsXG4gICAgc3R5bGVVcmxzOiBbXSxcbn0pXG5leHBvcnQgY2xhc3MgUmVjb3JkVGhyZWFkQ29tcG9uZW50IGltcGxlbWVudHMgT25Jbml0LCBPbkRlc3Ryb3ksIEFmdGVyVmlld0luaXQge1xuXG4gICAgQElucHV0KCkgY29uZmlnOiBSZWNvcmRUaHJlYWRDb25maWc7XG4gICAgQFZpZXdDaGlsZCgnbGlzdCcpIGxpc3RDb250YWluZXI6IEVsZW1lbnRSZWY7XG5cbiAgICBzdG9yZTogUmVjb3JkVGhyZWFkU3RvcmU7XG4gICAgY3JlYXRlU3RvcmU6IFJlY29yZFRocmVhZEl0ZW1TdG9yZTtcbiAgICByZWNvcmRzOiBSZWNvcmRUaHJlYWRJdGVtU3RvcmVbXTtcbiAgICBsb2FkaW5nID0gZmFsc2U7XG4gICAgbWF4SGVpZ2h0ID0gNDAwO1xuICAgIGRpcmVjdGlvbjogJ2FzYycgfCAnZGVzYycgPSAnYXNjJztcbiAgICBsb2FkTW9yZVBvc2l0aW9uOiAnYm90dG9tJyB8ICd0b3AnIHwgc3RyaW5nID0gJ3RvcCc7XG4gICAgbGlzdEFjdGlvbkFkYXB0ZXI6IFJlY29yZFRocmVhZExpc3RBY3Rpb25zQWRhcHRlcjtcblxuICAgIHByb3RlY3RlZCBzaG91bGRSZXNldFNjcm9sbCA9IGZhbHNlO1xuXG4gICAgcHJvdGVjdGVkIHN1YnM6IFN1YnNjcmlwdGlvbltdID0gW107XG4gICAgcHJvdGVjdGVkIHByZXNldEZpZWxkVmFsdWVzOiBBdHRyaWJ1dGVNYXA7XG5cblxuXG4gICAgY29uc3RydWN0b3IoXG4gICAgICAgIHByb3RlY3RlZCBzdG9yZUZhY3Rvcnk6IFJlY29yZFRocmVhZFN0b3JlRmFjdG9yeSxcbiAgICAgICAgcHJvdGVjdGVkIGl0ZW1GYWN0b3J5OiBSZWNvcmRUaHJlYWRJdGVtU3RvcmVGYWN0b3J5LFxuICAgICAgICBwcm90ZWN0ZWQgcmVjb3JkTWFuYWdlcjogUmVjb3JkTWFuYWdlcixcbiAgICAgICAgcHJvdGVjdGVkIG1lc3NhZ2U6IE1lc3NhZ2VTZXJ2aWNlLFxuICAgICAgICBwcm90ZWN0ZWQgYWN0aW9uQWRhcHRlckZhY3Rvcnk6IFJlY29yZFRocmVhZExpc3RBY3Rpb25zQWRhcHRlckZhY3RvcnksXG4gICAgKSB7XG4gICAgfVxuXG4gICAgbmdPbkluaXQoKTogdm9pZCB7XG4gICAgICAgIGlmICghaXNWb2lkKHRoaXMuY29uZmlnLm1heExpc3RIZWlnaHQpKSB7XG4gICAgICAgICAgICB0aGlzLm1heEhlaWdodCA9IHRoaXMuY29uZmlnLm1heExpc3RIZWlnaHQ7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAoIXRoaXMuY29uZmlnLm1vZHVsZSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKCF0aGlzLmNvbmZpZy5zdG9yZSkge1xuICAgICAgICAgICAgdGhpcy5zdG9yZSA9IHRoaXMuc3RvcmVGYWN0b3J5LmNyZWF0ZSgpO1xuICAgICAgICAgICAgdGhpcy5zdG9yZS5zZXRJdGVtTWV0YWRhdGEodGhpcy5jb25maWcuaXRlbUNvbmZpZy5tZXRhZGF0YSk7XG4gICAgICAgICAgICB0aGlzLnN0b3JlLnNldExpc3RNZXRhZGF0YSh7YWN0aW9uczogdGhpcy5jb25maWcubGlzdEFjdGlvbnN9KTtcbiAgICAgICAgICAgIHRoaXMuc3RvcmUuaW5pdCh0aGlzLmNvbmZpZy5tb2R1bGUsIGZhbHNlLCB0aGlzPy5jb25maWc/LnBhZ2VTaXplID8/IG51bGwpO1xuICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgdGhpcy5zdG9yZSA9IHRoaXMuY29uZmlnLnN0b3JlO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5kaXJlY3Rpb24gPSB0aGlzLmNvbmZpZy5kaXJlY3Rpb24gfHwgdGhpcy5kaXJlY3Rpb247XG4gICAgICAgIHRoaXMuc2V0TG9hZE1vcmVQb3NpdGlvbigpO1xuXG4gICAgICAgIHRoaXMuaW5pdENyZWF0ZSgpO1xuICAgICAgICB0aGlzLmluaXREYXRhU3Vic2NyaXB0aW9uKCk7XG5cbiAgICAgICAgaWYgKHRoaXMuY29uZmlnLmZpbHRlcnMkKSB7XG5cbiAgICAgICAgICAgIHRoaXMuc3Vicy5wdXNoKHRoaXMuY29uZmlnLmZpbHRlcnMkLnN1YnNjcmliZShmaWx0ZXJzID0+IHtcbiAgICAgICAgICAgICAgICB0aGlzLnN0b3JlLnNldEZpbHRlcnMoZmlsdGVycykucGlwZSh0YWtlKDEpKS5zdWJzY3JpYmUoKCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICBpZiAodGhpcy5jb25maWcub25SZWZyZXNoKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLmNvbmZpZy5vblJlZnJlc2goKVxuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9KSk7XG5cbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIHRoaXMuc3RvcmUubG9hZChmYWxzZSkuc3Vic2NyaWJlKCgpID0+IHtcbiAgICAgICAgICAgICAgICBpZiAodGhpcy5jb25maWcub25SZWZyZXNoKSB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuY29uZmlnLm9uUmVmcmVzaCgpXG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSk7XG4gICAgICAgIH1cblxuICAgICAgICBjb25zdCBhdXRvUmVmcmVzaEZyZXF1ZW5jeSA9IHRoaXM/LmNvbmZpZz8uYXV0b1JlZnJlc2hGcmVxdWVuY3kgPz8gMDtcbiAgICAgICAgaWYgKGF1dG9SZWZyZXNoRnJlcXVlbmN5ICYmIHRoaXMuc3RvcmUpIHtcbiAgICAgICAgICAgIGNvbnN0IG1pbiA9IHRoaXMuY29uZmlnLmF1dG9SZWZyZXNoRGV2aWF0aW9uTWluID8/IC0xNTtcbiAgICAgICAgICAgIGNvbnN0IG1heCA9IHRoaXMuY29uZmlnLmF1dG9SZWZyZXNoRGV2aWF0aW9uTWF4ID8/IDE1O1xuXG4gICAgICAgICAgICB0aGlzLnN1YnMucHVzaCh0aGlzLnN0b3JlLmluaXRBdXRvUmVmcmVzaChhdXRvUmVmcmVzaEZyZXF1ZW5jeSwgbWluLCBtYXgsIHRoaXMuY29uZmlnLm9uUmVmcmVzaCkuc3Vic2NyaWJlKCkpO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5pbml0TG9hZGluZygpO1xuXG4gICAgICAgIHRoaXMubGlzdEFjdGlvbkFkYXB0ZXIgPSB0aGlzLmFjdGlvbkFkYXB0ZXJGYWN0b3J5LmNyZWF0ZSh0aGlzLnN0b3JlLCB0aGlzLmNvbmZpZyk7XG5cbiAgICB9XG5cbiAgICBwcml2YXRlIHNldExvYWRNb3JlUG9zaXRpb24oKSB7XG4gICAgICAgIHRoaXMubG9hZE1vcmVQb3NpdGlvbiA9IHRoaXMuZGlyZWN0aW9uID09PSAnYXNjJyA/ICd0b3AnIDogJ2JvdHRvbSc7XG4gICAgICAgIGlmICh0aGlzLmNvbmZpZy5sb2FkTW9yZVBvc2l0aW9uKSB7XG4gICAgICAgICAgICB0aGlzLmxvYWRNb3JlUG9zaXRpb24gPSB0aGlzLmNvbmZpZy5sb2FkTW9yZVBvc2l0aW9uO1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgbmdBZnRlclZpZXdJbml0KCkge1xuICAgICAgICB0aGlzLnNob3VsZFJlc2V0U2Nyb2xsID0gdHJ1ZTtcbiAgICAgICAgdGhpcy5yZXNldFNjcm9sbCgpO1xuICAgIH1cblxuICAgIG5nT25EZXN0cm95KCk6IHZvaWQge1xuICAgICAgICBpZiAoISh0aGlzPy5jb25maWc/LnN0b3JlID8/IG51bGwpKSB7XG4gICAgICAgICAgICB0aGlzLnN0b3JlLmNsZWFyKCk7XG4gICAgICAgIH1cbiAgICAgICAgdGhpcy5zdG9yZSA9IG51bGw7XG4gICAgICAgIHRoaXMuc3Vicy5mb3JFYWNoKHN1YiA9PiBzdWIudW5zdWJzY3JpYmUoKSlcbiAgICB9XG5cbiAgICBidWlsZEl0ZW0oaXRlbTogUmVjb3JkVGhyZWFkSXRlbVN0b3JlLCBpdGVtUmVmOiBhbnkpOiBSZWNvcmRUaHJlYWRJdGVtQ29uZmlnIHtcbiAgICAgICAgbGV0IGtsYXNzID0gJ3JlY29yZC10aHJlYWQtbGlzdC1pdGVtJztcblxuICAgICAgICBpZiAodGhpcy5jb25maWcuaXRlbUNvbmZpZy5rbGFzcykge1xuICAgICAgICAgICAga2xhc3MgKz0gJyAnICsgdGhpcy5jb25maWcuaXRlbUNvbmZpZy5rbGFzc1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICAuLi50aGlzLmNvbmZpZy5pdGVtQ29uZmlnLFxuICAgICAgICAgICAgc3RvcmU6IGl0ZW0sXG4gICAgICAgICAgICB0aHJlYWRTdG9yZTogdGhpcy5zdG9yZSxcbiAgICAgICAgICAgIGtsYXNzOiBrbGFzcyxcbiAgICAgICAgICAgIGNvbnRhaW5lckNsYXNzOiB0aGlzLmNvbmZpZy5pdGVtQ29uZmlnLmNvbnRhaW5lckNsYXNzLFxuICAgICAgICAgICAgZmxleERpcmVjdGlvbjogdGhpcy5jb25maWc/Lml0ZW1Db25maWc/LmZsZXhEaXJlY3Rpb24gPz8gJycsXG4gICAgICAgICAgICBleHBhbmRlZDogKCk6IHZvaWQgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMuc2Nyb2xsVG9JdGVtKGl0ZW1SZWYpO1xuICAgICAgICAgICAgfSxcbiAgICAgICAgICAgIGNvbGxhcHNlZDogKCk6IHZvaWQgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMuc2Nyb2xsVG9JdGVtKGl0ZW1SZWYpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGFzIFJlY29yZFRocmVhZEl0ZW1Db25maWc7XG4gICAgfVxuXG4gICAgZ2V0TG9hZE1vcmVCdXR0b24oKTogQnV0dG9uSW50ZXJmYWNlIHtcbiAgICAgICAgcmV0dXJuIHtcbiAgICAgICAgICAgIGtsYXNzOiAnbG9hZC1tb3JlLWJ1dHRvbiBidG4gYnRuLWxpbmsgYnRuLXNtJyxcbiAgICAgICAgICAgIGxhYmVsS2V5OiAnTEJMX0xPQURfTU9SRScsXG4gICAgICAgICAgICBvbkNsaWNrOiAoKSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKHRoaXM/LmNvbmZpZz8ub25Mb2FkTW9yZSkge1xuICAgICAgICAgICAgICAgICAgICB0aGlzLnN0b3JlLmdldFJlY29yZExpc3QoKS5yZWNvcmRzJC5waXBlKFxuICAgICAgICAgICAgICAgICAgICAgICAgdGFrZSgxKSxcbiAgICAgICAgICAgICAgICAgICAgICAgIHRhcCgoKSA9PiB0aGlzLmNvbmZpZy5vbkxvYWRNb3JlKCkpXG4gICAgICAgICAgICAgICAgICAgICkuc3Vic2NyaWJlKCk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIHRoaXMuc3RvcmUubG9hZE1vcmUoKTtcbiAgICAgICAgICAgIH1cbiAgICAgICAgfSBhcyBCdXR0b25JbnRlcmZhY2U7XG4gICAgfVxuXG4gICAgYnVpbGRDcmVhdGVJdGVtKCk6IFJlY29yZFRocmVhZEl0ZW1Db25maWcge1xuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgLi4udGhpcy5jb25maWcuY3JlYXRlQ29uZmlnLFxuICAgICAgICAgICAgc3RvcmU6IHRoaXMuY3JlYXRlU3RvcmUsXG4gICAgICAgICAgICByb3dDbGFzczogeydwdC0xJzogdHJ1ZX0sXG4gICAgICAgICAgICBrbGFzczogJ3JlY29yZC10aHJlYWQtY3JlYXRlLWl0ZW0nLFxuICAgICAgICB9IGFzIFJlY29yZFRocmVhZEl0ZW1Db25maWc7XG4gICAgfVxuXG4gICAgZ2V0Q3JlYXRlQnV0dG9uKCk6IEJ1dHRvbkludGVyZmFjZSB7XG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICBrbGFzczogJ2NyZWF0ZS10aHJlYWQtaXRlbS1idXR0b24gYnRuIGJ0bi1tYWluIGJ0bi1zbScsXG4gICAgICAgICAgICBsYWJlbEtleTogJ0xCTF9TVUJNSVRfQlVUVE9OX0xBQkVMJyxcbiAgICAgICAgICAgIG9uQ2xpY2s6ICgpID0+IHtcbiAgICAgICAgICAgICAgICB0aGlzLmNyZWF0ZVN0b3JlLnZhbGlkYXRlKCkucGlwZSh0YWtlKDEpKS5zdWJzY3JpYmUodmFsaWQgPT4ge1xuICAgICAgICAgICAgICAgICAgICBpZiAodmFsaWQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMuY3JlYXRlU3RvcmUuc2F2ZSgpLnBpcGUodGFrZSgxKSkuc3Vic2NyaWJlKCgpID0+IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGlzLnN0b3JlLnJlbG9hZCgpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHRoaXMuaW5pdFJlY29yZCgpO1xuXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgdGhpcy5zaG91bGRSZXNldFNjcm9sbCA9IHRydWU7XG5cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGlzLm1lc3NhZ2UuYWRkU3VjY2Vzc01lc3NhZ2VCeUtleSgnTEJMX0FDVElPTl9TVUNDRVNTJylcbiAgICAgICAgICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICAgICAgdGhpcy5tZXNzYWdlLmFkZFdhcm5pbmdNZXNzYWdlQnlLZXkoJ0xCTF9WQUxJREFUSU9OX0VSUk9SUycpO1xuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfVxuICAgICAgICB9IGFzIEJ1dHRvbkludGVyZmFjZTtcbiAgICB9XG5cbiAgICBhbGxMb2FkZWQoKTogYm9vbGVhbiB7XG4gICAgICAgIHJldHVybiAhISh0aGlzLnN0b3JlICYmIHRoaXMuc3RvcmUuYWxsTG9hZGVkKCkpO1xuICAgIH1cblxuICAgIGdldE1heEhlaWdodCgpOiB7IFtrbGFzczogc3RyaW5nXTogYW55OyB9IHwgbnVsbCB7XG4gICAgICAgIGlmICh0aGlzLm1heEhlaWdodCA9PSAwKSB7XG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiB7J21heC1oZWlnaHQucHgnOiB0aGlzLm1heEhlaWdodCwgJ292ZXJmbG93LXknOiAnYXV0byd9XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGluaXRSZWNvcmQoKSB7XG4gICAgICAgIGNvbnN0IGVtcHR5UmVjb3JkID0gdGhpcy5yZWNvcmRNYW5hZ2VyLmJ1aWxkRW1wdHlSZWNvcmQodGhpcy5jb25maWcubW9kdWxlKTtcbiAgICAgICAgdGhpcy5hZGRQcmVzZXRGaWVsZHMoZW1wdHlSZWNvcmQpO1xuICAgICAgICBsZXQgbW9kZSA9ICdlZGl0JyBhcyBWaWV3TW9kZTtcbiAgICAgICAgaWYgKHRoaXMuY29uZmlnLmNyZWF0ZUNvbmZpZyAmJiB0aGlzLmNvbmZpZy5jcmVhdGVDb25maWcuaW5pdGlhbE1vZGUpIHtcbiAgICAgICAgICAgIG1vZGUgPSB0aGlzLmNvbmZpZy5jcmVhdGVDb25maWcuaW5pdGlhbE1vZGU7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLmNyZWF0ZVN0b3JlLmluaXRSZWNvcmQoZW1wdHlSZWNvcmQsIG1vZGUsIGZhbHNlLCB0cnVlKTtcbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgc2Nyb2xsVG9FbmQoKTogdm9pZCB7XG4gICAgICAgIGlmICghdGhpcy5saXN0Q29udGFpbmVyIHx8ICF0aGlzLmxpc3RDb250YWluZXIubmF0aXZlRWxlbWVudCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5zY3JvbGxUbyh0aGlzLmxpc3RDb250YWluZXIubmF0aXZlRWxlbWVudC5zY3JvbGxIZWlnaHQpO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBzY3JvbGxUb1RvcCgpOiB2b2lkIHtcbiAgICAgICAgdGhpcy5zY3JvbGxUbygwKTtcbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgc2Nyb2xsVG8ocG9zaXRpb246IG51bWJlcik6IHZvaWQge1xuICAgICAgICB0cnkge1xuICAgICAgICAgICAgdGhpcy5saXN0Q29udGFpbmVyLm5hdGl2ZUVsZW1lbnQuc2Nyb2xsVG9wID0gcG9zaXRpb247XG4gICAgICAgIH0gY2F0Y2ggKGVycikge1xuICAgICAgICB9XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIHNjcm9sbFRvSXRlbShpdGVtOiBhbnkpIHtcbiAgICAgICAgaWYgKCFpdGVtIHx8ICF0aGlzLmxpc3RDb250YWluZXIgfHwgIXRoaXMubGlzdENvbnRhaW5lci5uYXRpdmVFbGVtZW50KSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBjb25zdCBlbGVtZW50VG9wID0gaXRlbS5vZmZzZXRUb3A7XG4gICAgICAgIGNvbnN0IHBhcmVudFRvcCA9IHRoaXMubGlzdENvbnRhaW5lci5uYXRpdmVFbGVtZW50Lm9mZnNldFRvcDtcbiAgICAgICAgY29uc3QgcmVsYXRpdmVUb3AgPSBlbGVtZW50VG9wIC0gcGFyZW50VG9wO1xuXG4gICAgICAgIHRoaXMuc2Nyb2xsVG8ocmVsYXRpdmVUb3ApO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCByZXNldFNjcm9sbCgpOiB2b2lkIHtcbiAgICAgICAgaWYgKHRoaXMuc2hvdWxkUmVzZXRTY3JvbGwgPT09IGZhbHNlKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5kaXJlY3Rpb24gPT09ICdhc2MnKSB7XG4gICAgICAgICAgICB0aGlzLnNjcm9sbFRvRW5kKCk7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICB0aGlzLnNjcm9sbFRvVG9wKCk7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnNob3VsZFJlc2V0U2Nyb2xsID0gZmFsc2U7XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIHNjaGVkdWxlU2Nyb2xsUmVzZXQoKTogdm9pZCB7XG4gICAgICAgIHNldFRpbWVvdXQoKCkgPT4ge1xuICAgICAgICAgICAgdGhpcy5yZXNldFNjcm9sbCgpO1xuICAgICAgICB9LCA1MDApO1xuICAgIH1cblxuICAgIHByb3RlY3RlZCBpbml0Q3JlYXRlKCkge1xuICAgICAgICBpZiAoIXRoaXMuY29uZmlnLmNyZWF0ZSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5jcmVhdGVTdG9yZSA9IHRoaXMuaXRlbUZhY3RvcnkuY3JlYXRlKCk7XG4gICAgICAgIHRoaXMuY3JlYXRlU3RvcmUuc2V0TWV0YWRhdGEodGhpcy5jb25maWcuY3JlYXRlQ29uZmlnLm1ldGFkYXRhKTtcbiAgICAgICAgdGhpcy5pbml0UmVjb3JkKCk7XG4gICAgICAgIHRoaXMuaW5pdFByZXNldEZpZWxkc01hcHBpbmcoKTtcbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgaW5pdFByZXNldEZpZWxkc01hcHBpbmcoKSB7XG5cbiAgICAgICAgaWYgKCF0aGlzLmNvbmZpZy5wcmVzZXRGaWVsZHMkKSB7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnN1YnMucHVzaCh0aGlzLmNvbmZpZy5wcmVzZXRGaWVsZHMkLnN1YnNjcmliZShwcmVzZXRGaWVsZFZhbHVlcyA9PiB7XG5cbiAgICAgICAgICAgIGlmICghcHJlc2V0RmllbGRWYWx1ZXMgfHwgIU9iamVjdC5rZXlzKHByZXNldEZpZWxkVmFsdWVzKS5sZW5ndGgpIHtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIHRoaXMucHJlc2V0RmllbGRWYWx1ZXMgPSBwcmVzZXRGaWVsZFZhbHVlcztcblxuICAgICAgICAgICAgY29uc3QgcmVjb3JkID0gdGhpcy5jcmVhdGVTdG9yZS5yZWNvcmRTdG9yZS5nZXRCYXNlUmVjb3JkKCk7XG4gICAgICAgICAgICB0aGlzLmFkZFByZXNldEZpZWxkcyhyZWNvcmQpO1xuXG4gICAgICAgICAgICB0aGlzLmNyZWF0ZVN0b3JlLnJlY29yZFN0b3JlLnNldFJlY29yZChyZWNvcmQpO1xuICAgICAgICB9KSk7XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGFkZFByZXNldEZpZWxkcyhyZWNvcmQ6IFJlY29yZCk6IHZvaWQge1xuICAgICAgICBpZiAoIXRoaXMucHJlc2V0RmllbGRWYWx1ZXMpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIHJlY29yZC5hdHRyaWJ1dGVzID0ge1xuICAgICAgICAgICAgLi4udGhpcy5wcmVzZXRGaWVsZFZhbHVlcyxcbiAgICAgICAgICAgIC4uLihyZWNvcmQuYXR0cmlidXRlcyB8fCB7fSlcbiAgICAgICAgfTtcbiAgICB9XG5cblxuICAgIHByb3RlY3RlZCBpbml0RGF0YVN1YnNjcmlwdGlvbigpOiB2b2lkIHtcblxuICAgICAgICB0aGlzLnN1YnMucHVzaCh0aGlzLnN0b3JlLnN0b3JlcyQuc3Vic2NyaWJlKHJlY29yZHMgPT4ge1xuXG4gICAgICAgICAgICBpZiAoIXRoaXMucmVjb3JkcyB8fCAhdGhpcy5yZWNvcmRzLmxlbmd0aCkge1xuICAgICAgICAgICAgICAgIHRoaXMuc2hvdWxkUmVzZXRTY3JvbGwgPSB0cnVlO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICBpZiAodGhpcy5kaXJlY3Rpb24gPT09ICdhc2MnKSB7XG4gICAgICAgICAgICAgICAgdGhpcy5yZWNvcmRzID0gcmVjb3Jkcy5yZXZlcnNlKCk7XG4gICAgICAgICAgICAgICAgdGhpcy5zY2hlZHVsZVNjcm9sbFJlc2V0KCk7XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICB0aGlzLnJlY29yZHMgPSByZWNvcmRzO1xuICAgICAgICAgICAgdGhpcy5zY2hlZHVsZVNjcm9sbFJlc2V0KCk7XG4gICAgICAgIH0pKTtcbiAgICB9XG5cblxuICAgIHByb3RlY3RlZCBpbml0TG9hZGluZygpOiB2b2lkIHtcbiAgICAgICAgbGV0IGxvYWRpbmckOiBPYnNlcnZhYmxlPEFycmF5PGJvb2xlYW4+PjtcblxuICAgICAgICBpZiAodGhpcy5jcmVhdGVTdG9yZSAmJiB0aGlzLmNyZWF0ZVN0b3JlLmxvYWRpbmckKSB7XG4gICAgICAgICAgICBsb2FkaW5nJCA9IHRoaXMuc3RvcmUuJGxvYWRpbmcucGlwZShcbiAgICAgICAgICAgICAgICBjb21iaW5lTGF0ZXN0V2l0aCh0aGlzLmNyZWF0ZVN0b3JlLmxvYWRpbmckKVxuICAgICAgICAgICAgKTtcbiAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgIGxvYWRpbmckPSB0aGlzLnN0b3JlLiRsb2FkaW5nLnBpcGUoXG4gICAgICAgICAgICAgICAgbWFwKHZhbHVlID0+IFt2YWx1ZV0pXG4gICAgICAgICAgICApXG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnN1YnMucHVzaChsb2FkaW5nJC5zdWJzY3JpYmUoKGxvYWRpbmdzKSA9PiB7XG4gICAgICAgICAgICBpZiAoIWxvYWRpbmdzIHx8ICFsb2FkaW5ncy5sZW5ndGgpIHtcbiAgICAgICAgICAgICAgICB0aGlzLmxvYWRpbmcgPSBmYWxzZTtcbiAgICAgICAgICAgICAgICByZXR1cm47XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGxldCBsb2FkaW5nID0gZmFsc2U7XG5cbiAgICAgICAgICAgIGxvYWRpbmdzLmZvckVhY2godmFsdWUgPT4ge1xuICAgICAgICAgICAgICAgIGxvYWRpbmcgPSBsb2FkaW5nIHx8IHZhbHVlO1xuICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB0aGlzLmxvYWRpbmcgPSBsb2FkaW5nO1xuICAgICAgICB9KSk7XG4gICAgfVxufVxuIiwiPCEgLS1cbi8qKlxuKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4qIENvcHlyaWdodCAoQykgMjAyMSBTYWxlc0FnaWxpdHkgTHRkLlxuKlxuKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuKiB0aGUgdGVybXMgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSB2ZXJzaW9uIDMgYXMgcHVibGlzaGVkIGJ5IHRoZVxuKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4qIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiogSU4gV0hJQ0ggVEhFIENPUFlSSUdIVCBJUyBPV05FRCBCWSBTQUxFU0FHSUxJVFksIFNBTEVTQUdJTElUWSBESVNDTEFJTVMgVEhFXG4qIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuKlxuKiBUaGlzIHByb2dyYW0gaXMgZGlzdHJpYnV0ZWQgaW4gdGhlIGhvcGUgdGhhdCBpdCB3aWxsIGJlIHVzZWZ1bCwgYnV0IFdJVEhPVVRcbiogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuKiBkZXRhaWxzLlxuKlxuKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiogYWxvbmcgd2l0aCB0aGlzIHByb2dyYW0uICBJZiBub3QsIHNlZSBodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMuXG4qXG4qIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuKiB2ZXJzaW9uIDMsIHRoZXNlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCByZXRhaW4gdGhlIGRpc3BsYXkgb2YgdGhlXG4qIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4qIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4qIHRoZSB3b3JkcyBcIlN1cGVyY2hhcmdlZCBieSBTdWl0ZUNSTVwiLlxuKi9cbi0tPlxuXG48ZGl2IGNsYXNzPVwicmVjb3JkLXRocmVhZCB7eyhjb25maWcgJiYgY29uZmlnLmtsYXNzKSB8fCAnJ319XCI+XG4gICAgPGRpdiAqbmdJZj1cIiFsb2FkaW5nICYmICFyZWNvcmRzICYmICFyZWNvcmRzLmxlbmd0aFwiXG4gICAgICAgICBjbGFzcz1cImQtZmxleCByZWNvcmQtdGhyZWFkLW5vLWRhdGEganVzdGlmeS1jb250ZW50LWNlbnRlciBoM1wiPlxuICAgICAgICA8c2NybS1sYWJlbCBsYWJlbEtleT1cIkxCTF9OT19EQVRBXCI+PC9zY3JtLWxhYmVsPlxuICAgIDwvZGl2PlxuXG4gICAgPGRpdiAqbmdJZj1cImxvYWRpbmdcIiBjbGFzcz1cImQtZmxleCByZWNvcmQtdGhyZWFkLWxvYWRpbmcganVzdGlmeS1jb250ZW50LWNlbnRlclwiPlxuICAgICAgICA8c2NybS1sb2FkaW5nLXNwaW5uZXIgW292ZXJsYXldPVwidHJ1ZVwiPjwvc2NybS1sb2FkaW5nLXNwaW5uZXI+XG4gICAgPC9kaXY+XG5cbiAgICA8ZGl2ICNsaXN0XG4gICAgICAgICAqbmdJZj1cInJlY29yZHMgJiYgcmVjb3Jkcy5sZW5ndGhcIlxuICAgICAgICAgW25nU3R5bGVdPVwiZ2V0TWF4SGVpZ2h0KClcIlxuICAgICAgICAgY2xhc3M9XCJyZWNvcmQtdGhyZWFkLWxpc3Qgc2Nyb2xsYmFyLXRoaWNrXCI+XG5cbiAgICAgICAgPGRpdiAqbmdJZj1cImxvYWRNb3JlUG9zaXRpb24gPT09ICd0b3AnICYmICFhbGxMb2FkZWQoKVwiXG4gICAgICAgICAgICAgY2xhc3M9XCJyZWNvcmQtdGhyZWFkLWxvYWQtbW9yZSBkLWZsZXgganVzdGlmeS1jb250ZW50LWNlbnRlciBmbGV4LWdyb3ctMVwiPlxuICAgICAgICAgICAgPHNjcm0tYnV0dG9uIFtjb25maWddPVwiZ2V0TG9hZE1vcmVCdXR0b24oKVwiPjwvc2NybS1idXR0b24+XG4gICAgICAgIDwvZGl2PlxuXG4gICAgICAgIDxkaXYgI2l0ZW0gKm5nRm9yPVwibGV0IHJlY29yZCBvZiByZWNvcmRzXCI+XG4gICAgICAgICAgICA8c2NybS1yZWNvcmQtdGhyZWFkLWl0ZW0gW2NvbmZpZ109XCJidWlsZEl0ZW0ocmVjb3JkLCBpdGVtKVwiPjwvc2NybS1yZWNvcmQtdGhyZWFkLWl0ZW0+XG4gICAgICAgIDwvZGl2PlxuXG4gICAgICAgIDxkaXYgW2NsYXNzXT1cImNvbmZpZy5saXN0QWN0aW9uc0NsYXNzID8/ICcnXCI+XG5cbiAgICAgICAgICAgIDxkaXYgKm5nSWY9XCJsb2FkTW9yZVBvc2l0aW9uID09PSAnYm90dG9tJyAmJiAhYWxsTG9hZGVkKClcIlxuICAgICAgICAgICAgICAgICBjbGFzcz1cInJlY29yZC10aHJlYWQtbG9hZC1tb3JlIGQtZmxleCBqdXN0aWZ5LWNvbnRlbnQtY2VudGVyXCI+XG4gICAgICAgICAgICAgICAgPHNjcm0tYnV0dG9uIFtjb25maWddPVwiZ2V0TG9hZE1vcmVCdXR0b24oKVwiPjwvc2NybS1idXR0b24+XG4gICAgICAgICAgICA8L2Rpdj5cblxuICAgICAgICAgICAgPG5nLWNvbnRhaW5lciAqbmdJZj1cImNvbmZpZy5saXN0QWN0aW9uc1wiPlxuICAgICAgICAgICAgICAgIDxzY3JtLWFjdGlvbi1ncm91cC1tZW51IFtidXR0b25DbGFzc109XCJjb25maWcubGlzdEFjdGlvbnNCdXR0b25DbGFzcyA/PyAnJ1wiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgW2J1dHRvbkdyb3VwQ2xhc3NdPVwiY29uZmlnLmxpc3RBY3Rpb25zQnV0dG9uR3JvdXBDbGFzcyA/PyAnJ1wiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgW2NvbmZpZ109XCJsaXN0QWN0aW9uQWRhcHRlclwiPlxuICAgICAgICAgICAgICAgIDwvc2NybS1hY3Rpb24tZ3JvdXAtbWVudT5cbiAgICAgICAgICAgIDwvbmctY29udGFpbmVyPlxuXG4gICAgICAgIDwvZGl2PlxuXG4gICAgPC9kaXY+XG5cbiAgICA8ZGl2ICpuZ0lmPVwiKCFyZWNvcmRzIHx8ICFyZWNvcmRzLmxlbmd0aCkgJiYgIWxvYWRpbmcgJiYgY29uZmlnLnNob3dOb0RhdGFNZXNzYWdlXCI+XG4gICAgICAgIDxoNiBjbGFzcz1cInB0LTMgcGwtMyBwci0zIHBiLTJcIj5cbiAgICAgICAgICAgIDxzY3JtLWxhYmVsIFtsYWJlbEtleV09XCJjb25maWcubm9EYXRhTGFiZWwgfHwgJ0xCTF9OT19EQVRBJ1wiPjwvc2NybS1sYWJlbD5cbiAgICAgICAgPC9oNj5cblxuICAgIDwvZGl2PlxuXG4gICAgPG5nLWNvbnRhaW5lciAqbmdJZj1cImNvbmZpZy5jcmVhdGUgJiYgY3JlYXRlU3RvcmVcIj5cblxuICAgICAgICA8ZGl2ICpuZ0lmPVwiIWxvYWRpbmdcIlxuICAgICAgICAgICAgIGNsYXNzPVwiZC1mbGV4IGZsZXgtY29sdW1uIHJlY29yZC10aHJlYWQtY3JlYXRlLWNvbnRhaW5lclwiPlxuXG4gICAgICAgICAgICA8ZGl2IGNsYXNzPVwiZmxleC1ncm93LTFcIj5cbiAgICAgICAgICAgICAgICA8c2NybS1yZWNvcmQtdGhyZWFkLWl0ZW0gW2NvbmZpZ109XCJidWlsZENyZWF0ZUl0ZW0oKVwiPjwvc2NybS1yZWNvcmQtdGhyZWFkLWl0ZW0+XG4gICAgICAgICAgICA8L2Rpdj5cblxuICAgICAgICAgICAgPGRpdiBjbGFzcz1cImZsZXgtZ3Jvdy0xIGQtZmxleCBqdXN0aWZ5LWNvbnRlbnQtc3RhcnQgcHQtMSByZWNvcmQtdGhyZWFkLWNyZWF0ZS1idXR0b25zXCI+XG4gICAgICAgICAgICAgICAgPHNjcm0tYnV0dG9uIFtjb25maWddPVwiZ2V0Q3JlYXRlQnV0dG9uKClcIj48L3Njcm0tYnV0dG9uPlxuICAgICAgICAgICAgPC9kaXY+XG5cbiAgICAgICAgPC9kaXY+XG5cbiAgICA8L25nLWNvbnRhaW5lcj5cblxuPC9kaXY+XG4iXX0=