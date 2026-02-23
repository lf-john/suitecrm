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
import { BehaviorSubject, combineLatestWith, of } from 'rxjs';
import { deepClone, isVoid } from 'common';
import { distinctUntilChanged, map, shareReplay } from 'rxjs/operators';
import { RecordSaveGQL } from '../../../../store/record/graphql/api.record.save';
import { MessageService } from '../../../../services/message/message.service';
import { RecordManager } from '../../../../services/record/record.manager';
import { RecordFetchGQL } from '../../../../store/record/graphql/api.record.get';
import { RecordStoreFactory } from '../../../../store/record/record.store.factory';
import { LanguageStore } from '../../../../store/language/language.store';
import { trimEnd } from 'lodash-es';
import * as i0 from "@angular/core";
import * as i1 from "../../../../store/record/graphql/api.record.get";
import * as i2 from "../../../../store/record/graphql/api.record.save";
import * as i3 from "../../../../services/message/message.service";
import * as i4 from "../../../../services/record/record.manager";
import * as i5 from "../../../../store/record/record.store.factory";
import * as i6 from "../../../../store/language/language.store";
const initialState = {
    loading: false,
    mode: 'detail',
    params: {
        returnModule: '',
        returnId: '',
        returnAction: ''
    }
};
class InstallViewStore {
    constructor(recordFetchGQL, recordSaveGQL, message, recordManager, recordStoreFactory, language) {
        this.recordFetchGQL = recordFetchGQL;
        this.recordSaveGQL = recordSaveGQL;
        this.message = message;
        this.recordManager = recordManager;
        this.recordStoreFactory = recordStoreFactory;
        this.language = language;
        /** Internal Properties */
        this.cache$ = null;
        this.internalState = deepClone(initialState);
        this.store = new BehaviorSubject(this.internalState);
        this.state$ = this.store.asObservable();
        this.subs = [];
        this.recordStore = recordStoreFactory.create(this.getViewFieldsObservable());
        this.record$ = this.recordStore.state$.pipe(distinctUntilChanged());
        this.stagingRecord$ = this.recordStore.staging$.pipe(distinctUntilChanged());
        this.loading$ = this.state$.pipe(map(state => state.loading));
        this.mode$ = this.state$.pipe(map(state => state.mode));
        this.vm$ = this.record$.pipe(combineLatestWith(this.loading$), map(([record, loading]) => {
            this.vm = { record, loading };
            return this.vm;
        }));
        this.viewContext$ = this.record$.pipe(map(() => {
            return this.getViewContext();
        }));
    }
    get params() {
        return this.internalState.params || {};
    }
    set params(params) {
        this.updateState({
            ...this.internalState,
            params
        });
    }
    getViewContext() {
        return {
            record: this.getBaseRecord()
        };
    }
    getActions() {
        return of([]);
    }
    /**
     * Initial install view
     *
     * @param {string} mode to use
     * @param {object} params to set
     */
    init(mode = 'edit', params = {}) {
        this.setMode(mode);
        this.recordStore.init({
            id: '',
            module: 'install',
            attributes: {}
        }, true);
    }
    /**
     * Clear observable cache
     */
    clear() {
        this.cache$ = null;
        this.updateState(deepClone(initialState));
    }
    /**
     * Clear
     */
    clearAuthBased() {
        this.clear();
    }
    /**
     * Get staging record
     *
     * @returns {string} ViewMode
     */
    getBaseRecord() {
        if (!this.internalState) {
            return null;
        }
        return this.recordStore.getBaseRecord();
    }
    /**
     * Get current view mode
     *
     * @returns {string} ViewMode
     */
    getMode() {
        if (!this.internalState) {
            return null;
        }
        return this.internalState.mode;
    }
    /**
     * Set new mode
     *
     * @param {string} mode ViewMode
     */
    setMode(mode) {
        this.updateState({ ...this.internalState, mode });
    }
    getLicenseText() {
        return this.language.getFieldLabel('SUITE8_LICENSE_CONTENT');
    }
    getMetadata() {
        this.url = window.location.origin + window.location.pathname;
        this.url = trimEnd(this.url, '/');
        return {
            actions: [],
            templateMeta: {
                maxColumns: 2,
                useTabs: true,
                tabDefs: {
                    LBL_CONFIG: {
                        newTab: true,
                        panelDefault: 'expanded'
                    }
                }
            },
            panels: [
                {
                    key: 'LBL_CONFIG',
                    display$: of(true).pipe(shareReplay(1)),
                    rows: [
                        {
                            cols: [
                                {
                                    name: 'site_host',
                                    label: 'LBL_SITECFG_URL',
                                    type: 'varchar',
                                    fieldDefinition: {
                                        "name": "site_host",
                                        "vname": "LBL_SITECFG_URL",
                                        "type": "varchar",
                                        "required": true,
                                        "default": this.url?.toString(),
                                        "defaultValueModes": ["create", "edit"]
                                    },
                                },
                                {
                                    name: 'demoData',
                                    label: 'LBL_DBCONF_DEMO_DATA',
                                    type: 'enum',
                                    fieldDefinition: {
                                        name: "demoData",
                                        vname: "LBL_DBCONF_DEMO_DATA",
                                        type: "enum",
                                        options: "__no_options__",
                                        required: true,
                                        "default": 'no',
                                        "defaultValueModes": ["create", "edit"],
                                        metadata: {
                                            extraOptions: [
                                                {
                                                    value: "yes",
                                                    labelKey: "LBL_YES",
                                                },
                                                {
                                                    value: "no",
                                                    labelKey: "LBL_NO",
                                                },
                                            ]
                                        }
                                    },
                                },
                            ]
                        },
                        {
                            cols: [
                                {
                                    name: 'db_config',
                                    label: 'LBL_DBCONF_TITLE',
                                    type: 'grouped-field',
                                    fieldDefinition: {
                                        name: "db_config",
                                        vname: "LBL_DBCONF_TITLE",
                                        type: "grouped-field",
                                        layout: [
                                            "db_username",
                                            "db_password",
                                            "db_host",
                                            "db_name",
                                            "db_port"
                                        ],
                                        display: "vertical",
                                        groupFields: {
                                            "db_username": {
                                                "name": "db_username",
                                                "type": "varchar",
                                                "vname": "LBL_DBCONF_SUITE_DB_USER",
                                                "labelKey": "LBL_DBCONF_SUITE_DB_USER",
                                                "label": "LBL_DBCONF_SUITE_DB_USER",
                                                "showLabel": ["*"],
                                                "required": true,
                                            },
                                            "db_password": {
                                                "name": "db_password",
                                                "type": "password",
                                                "vname": "LBL_DBCONF_DB_PASSWORD",
                                                "labelKey": "LBL_DBCONF_DB_PASSWORD",
                                                "showLabel": ["*"],
                                                "required": true,
                                            },
                                            "db_host": {
                                                "name": "db_host",
                                                "type": "varchar",
                                                "vname": "LBL_DBCONF_HOST_NAME",
                                                "labelKey": "LBL_DBCONF_HOST_NAME",
                                                "showLabel": ["*"],
                                                "required": true,
                                            },
                                            "db_name": {
                                                "name": "db_name",
                                                "type": "varchar",
                                                "vname": "LBL_DBCONF_DB_NAME",
                                                "labelKey": "LBL_DBCONF_DB_NAME",
                                                "showLabel": ["*"],
                                                "required": true,
                                            },
                                            "db_port": {
                                                "name": "db_port",
                                                "type": "varchar",
                                                "vname": "LBL_DBCONF_DB_PORT",
                                                "labelKey": "LBL_DBCONF_DB_PORT",
                                                "showLabel": ["*"],
                                                "required": false,
                                                "default": '3306',
                                                "defaultValueModes": ["create", "edit"]
                                            }
                                        },
                                        showLabel: {
                                            edit: ['*']
                                        }
                                    },
                                },
                                {
                                    name: 'admin_config',
                                    label: 'LBL_SITECFG_TITLE',
                                    type: 'grouped-field',
                                    fieldDefinition: {
                                        name: "admin_config",
                                        vname: "LBL_SITECFG_TITLE",
                                        type: "grouped-field",
                                        layout: [
                                            "site_username",
                                            "site_password",
                                        ],
                                        display: "vertical",
                                        groupFields: {
                                            "site_username": {
                                                "name": "site_username",
                                                "type": "varchar",
                                                "vname": "LBL_SITECFG_ADMIN_Name",
                                                "labelKey": "LBL_SITECFG_ADMIN_Name",
                                                "showLabel": ["edit"],
                                                "required": true,
                                                "default": "admin",
                                                "defaultValueModes": ["create", "edit"]
                                            },
                                            "site_password": {
                                                "name": "site_password",
                                                "type": "password",
                                                "vname": "LBL_SITECFG_ADMIN_PASS",
                                                "labelKey": "LBL_SITECFG_ADMIN_PASS",
                                                "showLabel": ["edit"],
                                                "required": true,
                                            },
                                        },
                                        showLabel: {
                                            edit: ['*']
                                        }
                                    },
                                }
                            ]
                        }
                    ]
                }
            ],
        };
    }
    getMetadata$() {
        return of(this.getMetadata());
    }
    getModuleName() {
        return 'install';
    }
    /**
     * Parse query params
     *
     * @param {object} params to set
     */
    parseParams(params = {}) {
        if (!params) {
            return;
        }
        const currentParams = { ...this.internalState.params };
        Object.keys(params).forEach(paramKey => {
            if (!isVoid(currentParams[paramKey])) {
                currentParams[paramKey] = params[paramKey];
                return;
            }
        });
        this.params = params;
    }
    /**
     * Update the state
     *
     * @param {object} state to set
     */
    updateState(state) {
        this.store.next(this.internalState = state);
    }
    getIgnoreSystemChecksField() {
        return this.recordStore.getStaging().fields['sys_check_option'];
    }
    /**
     * Get view fields observable
     *
     * @returns {object} Observable<ViewFieldDefinition[]>
     */
    getViewFieldsObservable() {
        return this.getMetadata$().pipe(map((meta) => {
            const fields = [];
            meta.panels.forEach(panel => {
                panel.rows.forEach(row => {
                    row.cols.forEach(col => {
                        fields.push(col);
                    });
                });
            });
            fields.push({
                "name": "sys_check_option",
                "type": "boolean",
                fieldDefinition: {
                    "name": "sys_check_option",
                    "type": "boolean",
                    "vname": "LBL_SYS_CHECK_WARNING",
                    "labelKey": "LBL_SYS_CHECK_WARNING",
                    "showLabel": ["edit"],
                    "required": false,
                    "value": 'false',
                    "default": 'false',
                    "defaultValueModes": ["create", "edit"]
                }
            });
            return fields;
        }));
    }
    static { this.ɵfac = function InstallViewStore_Factory(t) { return new (t || InstallViewStore)(i0.ɵɵinject(i1.RecordFetchGQL), i0.ɵɵinject(i2.RecordSaveGQL), i0.ɵɵinject(i3.MessageService), i0.ɵɵinject(i4.RecordManager), i0.ɵɵinject(i5.RecordStoreFactory), i0.ɵɵinject(i6.LanguageStore)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: InstallViewStore, factory: InstallViewStore.ɵfac }); }
}
export { InstallViewStore };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(InstallViewStore, [{
        type: Injectable
    }], function () { return [{ type: i1.RecordFetchGQL }, { type: i2.RecordSaveGQL }, { type: i3.MessageService }, { type: i4.RecordManager }, { type: i5.RecordStoreFactory }, { type: i6.LanguageStore }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaW5zdGFsbC12aWV3LnN0b3JlLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vY29yZS9hcHAvY29yZS9zcmMvbGliL3ZpZXdzL2luc3RhbGwvc3RvcmUvaW5zdGFsbC12aWV3L2luc3RhbGwtdmlldy5zdG9yZS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0dBd0JHO0FBRUgsT0FBTyxFQUFDLFVBQVUsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUN6QyxPQUFPLEVBQUMsZUFBZSxFQUFpQixpQkFBaUIsRUFBYyxFQUFFLEVBQWUsTUFBTSxNQUFNLENBQUM7QUFDckcsT0FBTyxFQUVILFNBQVMsRUFJVCxNQUFNLEVBV1QsTUFBTSxRQUFRLENBQUM7QUFDaEIsT0FBTyxFQUFDLG9CQUFvQixFQUFFLEdBQUcsRUFBRSxXQUFXLEVBQUMsTUFBTSxnQkFBZ0IsQ0FBQztBQUd0RSxPQUFPLEVBQUMsYUFBYSxFQUFDLE1BQU0sa0RBQWtELENBQUM7QUFFL0UsT0FBTyxFQUFDLGNBQWMsRUFBQyxNQUFNLDhDQUE4QyxDQUFDO0FBQzVFLE9BQU8sRUFBQyxhQUFhLEVBQUMsTUFBTSw0Q0FBNEMsQ0FBQztBQUV6RSxPQUFPLEVBQUMsY0FBYyxFQUFDLE1BQU0saURBQWlELENBQUM7QUFFL0UsT0FBTyxFQUFDLGtCQUFrQixFQUFDLE1BQU0sK0NBQStDLENBQUM7QUFDakYsT0FBTyxFQUFDLGFBQWEsRUFBQyxNQUFNLDJDQUEyQyxDQUFDO0FBQ3hFLE9BQU8sRUFBRSxPQUFPLEVBQUUsTUFBTSxXQUFXLENBQUM7Ozs7Ozs7O0FBRXBDLE1BQU0sWUFBWSxHQUFxQjtJQUNuQyxPQUFPLEVBQUUsS0FBSztJQUNkLElBQUksRUFBRSxRQUFRO0lBQ2QsTUFBTSxFQUFFO1FBQ0osWUFBWSxFQUFFLEVBQUU7UUFDaEIsUUFBUSxFQUFFLEVBQUU7UUFDWixZQUFZLEVBQUUsRUFBRTtL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V0FBVyxFQUFFLENBQUMsTUFBTSxDQUFDO29CQUNyQixVQUFVLEVBQUUsS0FBSztvQkFDakIsT0FBTyxFQUFFLE9BQU87b0JBQ2hCLFNBQVMsRUFBRSxPQUFPO29CQUNsQixtQkFBbUIsRUFBRSxDQUFDLFFBQVEsRUFBRSxNQUFNLENBQUM7aUJBQzFDO2FBQ21CLENBQzNCLENBQUM7WUFFRixPQUFPLE1BQU0sQ0FBQztRQUNsQixDQUFDLENBQUMsQ0FBQyxDQUFDO0lBQ1IsQ0FBQztpRkFoWlEsZ0JBQWdCO3VFQUFoQixnQkFBZ0IsV0FBaEIsZ0JBQWdCOztTQUFoQixnQkFBZ0I7dUZBQWhCLGdCQUFnQjtjQUQ1QixVQUFVIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuXG5pbXBvcnQge0luamVjdGFibGV9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHtCZWhhdmlvclN1YmplY3QsIGNvbWJpbmVMYXRlc3QsIGNvbWJpbmVMYXRlc3RXaXRoLCBPYnNlcnZhYmxlLCBvZiwgU3Vic2NyaXB0aW9ufSBmcm9tICdyeGpzJztcbmltcG9ydCB7XG4gICAgQWN0aW9uLFxuICAgIGRlZXBDbG9uZSxcbiAgICBGaWVsZCxcbiAgICBGaWVsZERlZmluaXRpb24sXG4gICAgRmllbGRNZXRhZGF0YSxcbiAgICBpc1ZvaWQsXG4gICAgT3B0aW9uLFxuICAgIFBhbmVsLFxuICAgIFBhbmVsQ2VsbCxcbiAgICBQYW5lbFJvdyxcbiAgICBSZWNvcmQsXG4gICAgVmlld0NvbnRleHQsXG4gICAgVmlld0ZpZWxkRGVmaW5pdGlvbixcbiAgICBWaWV3TW9kZSxcbiAgICBUYWJEZWZpbml0aW9uLFxuICAgIFRhYkRlZmluaXRpb25zXG59IGZyb20gJ2NvbW1vbic7XG5pbXBvcnQge2Rpc3RpbmN0VW50aWxDaGFuZ2VkLCBtYXAsIHNoYXJlUmVwbGF5fSBmcm9tICdyeGpzL29wZXJhdG9ycyc7XG5pbXBvcnQge0luc3RhbGxWaWV3TWV0YWRhdGEsIEluc3RhbGxWaWV3TW9kZWwsIEluc3RhbGxWaWV3U3RhdGV9IGZyb20gJy4vaW5zdGFsbC12aWV3LnN0b3JlLm1vZGVsJztcbmltcG9ydCB7U3RhdGVTdG9yZX0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvc3RhdGUnO1xuaW1wb3J0IHtSZWNvcmRTYXZlR1FMfSBmcm9tICcuLi8uLi8uLi8uLi9zdG9yZS9yZWNvcmQvZ3JhcGhxbC9hcGkucmVjb3JkLnNhdmUnO1xuaW1wb3J0IHtSZWNvcmRUZW1wbGF0ZU1ldGFkYXRhfSBmcm9tICcuLi8uLi8uLi8uLi9zdG9yZS9tZXRhZGF0YS9tZXRhZGF0YS5zdG9yZS5zZXJ2aWNlJztcbmltcG9ydCB7TWVzc2FnZVNlcnZpY2V9IGZyb20gJy4uLy4uLy4uLy4uL3NlcnZpY2VzL21lc3NhZ2UvbWVzc2FnZS5zZXJ2aWNlJztcbmltcG9ydCB7UmVjb3JkTWFuYWdlcn0gZnJvbSAnLi4vLi4vLi4vLi4vc2VydmljZXMvcmVjb3JkL3JlY29yZC5tYW5hZ2VyJztcbmltcG9ydCB7UmVjb3JkU3RvcmV9IGZyb20gJy4uLy4uLy4uLy4uL3N0b3JlL3JlY29yZC9yZWNvcmQuc3RvcmUnO1xuaW1wb3J0IHtSZWNvcmRGZXRjaEdRTH0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvcmVjb3JkL2dyYXBocWwvYXBpLnJlY29yZC5nZXQnO1xuaW1wb3J0IHtQYXJhbXN9IGZyb20gJ0Bhbmd1bGFyL3JvdXRlcic7XG5pbXBvcnQge1JlY29yZFN0b3JlRmFjdG9yeX0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvcmVjb3JkL3JlY29yZC5zdG9yZS5mYWN0b3J5JztcbmltcG9ydCB7TGFuZ3VhZ2VTdG9yZX0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvbGFuZ3VhZ2UvbGFuZ3VhZ2Uuc3RvcmUnO1xuaW1wb3J0IHsgdHJpbUVuZCB9IGZyb20gJ2xvZGFzaC1lcyc7XG5cbmNvbnN0IGluaXRpYWxTdGF0ZTogSW5zdGFsbFZpZXdTdGF0ZSA9IHtcbiAgICBsb2FkaW5nOiBmYWxzZSxcbiAgICBtb2RlOiAnZGV0YWlsJyxcbiAgICBwYXJhbXM6IHtcbiAgICAgICAgcmV0dXJuTW9kdWxlOiAnJyxcbiAgICAgICAgcmV0dXJuSWQ6ICcnLFxuICAgICAgICByZXR1cm5BY3Rpb246ICcnXG4gICAgfVxufTtcblxuQEluamVjdGFibGUoKVxuZXhwb3J0IGNsYXNzIEluc3RhbGxWaWV3U3RvcmUgaW1wbGVtZW50cyBTdGF0ZVN0b3JlIHtcblxuICAgIC8qKlxuICAgICAqIFB1YmxpYyBsb25nLWxpdmVkIG9ic2VydmFibGUgc3RyZWFtc1xuICAgICAqL1xuICAgIHJlY29yZCQ6IE9ic2VydmFibGU8UmVjb3JkPjtcbiAgICBzdGFnaW5nUmVjb3JkJDogT2JzZXJ2YWJsZTxSZWNvcmQ+O1xuICAgIGxvYWRpbmckOiBPYnNlcnZhYmxlPGJvb2xlYW4+O1xuICAgIG1vZGUkOiBPYnNlcnZhYmxlPFZpZXdNb2RlPjtcbiAgICB2aWV3Q29udGV4dCQ6IE9ic2VydmFibGU8Vmlld0NvbnRleHQ+O1xuXG4gICAgLyoqXG4gICAgICogVmlldy1tb2RlbCB0aGF0IHJlc29sdmVzIG9uY2UgYWxsIHRoZSBkYXRhIGlzIHJlYWR5IChvciB1cGRhdGVkKS5cbiAgICAgKi9cbiAgICB2bSQ6IE9ic2VydmFibGU8SW5zdGFsbFZpZXdNb2RlbD47XG4gICAgdm06IEluc3RhbGxWaWV3TW9kZWw7XG4gICAgcmVjb3JkU3RvcmU6IFJlY29yZFN0b3JlO1xuICAgIHVybDogc3RyaW5nO1xuXG4gICAgLyoqIEludGVybmFsIFByb3BlcnRpZXMgKi9cbiAgICBwcm90ZWN0ZWQgY2FjaGUkOiBPYnNlcnZhYmxlPGFueT4gPSBudWxsO1xuICAgIHByb3RlY3RlZCBpbnRlcm5hbFN0YXRlOiBJbnN0YWxsVmlld1N0YXRlID0gZGVlcENsb25lKGluaXRpYWxTdGF0ZSk7XG4gICAgcHJvdGVjdGVkIHN0b3JlID0gbmV3IEJlaGF2aW9yU3ViamVjdDxJbnN0YWxsVmlld1N0YXRlPih0aGlzLmludGVybmFsU3RhdGUpO1xuICAgIHByb3RlY3RlZCBzdGF0ZSQgPSB0aGlzLnN0b3JlLmFzT2JzZXJ2YWJsZSgpO1xuICAgIHByb3RlY3RlZCBzdWJzOiBTdWJzY3JpcHRpb25bXSA9IFtdO1xuXG4gICAgY29uc3RydWN0b3IoXG4gICAgICAgIHByb3RlY3RlZCByZWNvcmRGZXRjaEdRTDogUmVjb3JkRmV0Y2hHUUwsXG4gICAgICAgIHByb3RlY3RlZCByZWNvcmRTYXZlR1FMOiBSZWNvcmRTYXZlR1FMLFxuICAgICAgICBwcm90ZWN0ZWQgbWVzc2FnZTogTWVzc2FnZVNlcnZpY2UsXG4gICAgICAgIHByb3RlY3RlZCByZWNvcmRNYW5hZ2VyOiBSZWNvcmRNYW5hZ2VyLFxuICAgICAgICBwcm90ZWN0ZWQgcmVjb3JkU3RvcmVGYWN0b3J5OiBSZWNvcmRTdG9yZUZhY3RvcnksXG4gICAgICAgIHByb3RlY3RlZCBsYW5ndWFnZTogTGFuZ3VhZ2VTdG9yZVxuICAgICkge1xuXG4gICAgICAgIHRoaXMucmVjb3JkU3RvcmUgPSByZWNvcmRTdG9yZUZhY3RvcnkuY3JlYXRlKHRoaXMuZ2V0Vmlld0ZpZWxkc09ic2VydmFibGUoKSk7XG5cbiAgICAgICAgdGhpcy5yZWNvcmQkID0gdGhpcy5yZWNvcmRTdG9yZS5zdGF0ZSQucGlwZShkaXN0aW5jdFVudGlsQ2hhbmdlZCgpKTtcbiAgICAgICAgdGhpcy5zdGFnaW5nUmVjb3JkJCA9IHRoaXMucmVjb3JkU3RvcmUuc3RhZ2luZyQucGlwZShkaXN0aW5jdFVudGlsQ2hhbmdlZCgpKTtcbiAgICAgICAgdGhpcy5sb2FkaW5nJCA9IHRoaXMuc3RhdGUkLnBpcGUobWFwKHN0YXRlID0+IHN0YXRlLmxvYWRpbmcpKTtcbiAgICAgICAgdGhpcy5tb2RlJCA9IHRoaXMuc3RhdGUkLnBpcGUobWFwKHN0YXRlID0+IHN0YXRlLm1vZGUpKTtcblxuICAgICAgICB0aGlzLnZtJCA9IHRoaXMucmVjb3JkJC5waXBlKFxuICAgICAgICAgICAgY29tYmluZUxhdGVzdFdpdGgodGhpcy5sb2FkaW5nJCksXG4gICAgICAgICAgICBtYXAoKFtyZWNvcmQsIGxvYWRpbmddOiBbUmVjb3JkLCBib29sZWFuXSkgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMudm0gPSB7cmVjb3JkLCBsb2FkaW5nfSBhcyBJbnN0YWxsVmlld01vZGVsO1xuICAgICAgICAgICAgICAgIHJldHVybiB0aGlzLnZtO1xuICAgICAgICAgICAgfSkpO1xuXG4gICAgICAgIHRoaXMudmlld0NvbnRleHQkID0gdGhpcy5yZWNvcmQkLnBpcGUobWFwKCgpID0+IHtcbiAgICAgICAgICAgIHJldHVybiB0aGlzLmdldFZpZXdDb250ZXh0KCk7XG4gICAgICAgIH0pKTtcbiAgICB9XG5cbiAgICBnZXQgcGFyYW1zKCk6IHsgW2tleTogc3RyaW5nXTogc3RyaW5nIH0ge1xuICAgICAgICByZXR1cm4gdGhpcy5pbnRlcm5hbFN0YXRlLnBhcmFtcyB8fCB7fTtcbiAgICB9XG5cbiAgICBzZXQgcGFyYW1zKHBhcmFtczogeyBba2V5OiBzdHJpbmddOiBzdHJpbmcgfSkge1xuICAgICAgICB0aGlzLnVwZGF0ZVN0YXRlKHtcbiAgICAgICAgICAgIC4uLnRoaXMuaW50ZXJuYWxTdGF0ZSxcbiAgICAgICAgICAgIHBhcmFtc1xuICAgICAgICB9KTtcbiAgICB9XG5cbiAgICBnZXRWaWV3Q29udGV4dCgpOiBWaWV3Q29udGV4dCB7XG4gICAgICAgIHJldHVybiB7XG4gICAgICAgICAgICByZWNvcmQ6IHRoaXMuZ2V0QmFzZVJlY29yZCgpXG4gICAgICAgIH07XG4gICAgfVxuXG4gICAgZ2V0QWN0aW9ucygpOiBPYnNlcnZhYmxlPEFjdGlvbltdPiB7XG4gICAgICAgIHJldHVybiBvZihbXSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogSW5pdGlhbCBpbnN0YWxsIHZpZXdcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBtb2RlIHRvIHVzZVxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBwYXJhbXMgdG8gc2V0XG4gICAgICovXG4gICAgcHVibGljIGluaXQobW9kZSA9ICdlZGl0JyBhcyBWaWV3TW9kZSwgcGFyYW1zOiBQYXJhbXMgPSB7fSk6IHZvaWQge1xuICAgICAgICB0aGlzLnNldE1vZGUobW9kZSk7XG4gICAgICAgIHRoaXMucmVjb3JkU3RvcmUuaW5pdChcbiAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgaWQ6ICcnLFxuICAgICAgICAgICAgICAgIG1vZHVsZTogJ2luc3RhbGwnLFxuICAgICAgICAgICAgICAgIGF0dHJpYnV0ZXM6IHt9XG4gICAgICAgICAgICB9IGFzIFJlY29yZCxcbiAgICAgICAgICAgIHRydWVcbiAgICAgICAgKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBDbGVhciBvYnNlcnZhYmxlIGNhY2hlXG4gICAgICovXG4gICAgcHVibGljIGNsZWFyKCk6IHZvaWQge1xuICAgICAgICB0aGlzLmNhY2hlJCA9IG51bGw7XG4gICAgICAgIHRoaXMudXBkYXRlU3RhdGUoZGVlcENsb25lKGluaXRpYWxTdGF0ZSkpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENsZWFyXG4gICAgICovXG4gICAgY2xlYXJBdXRoQmFzZWQoKTogdm9pZCB7XG4gICAgICAgIHRoaXMuY2xlYXIoKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBHZXQgc3RhZ2luZyByZWNvcmRcbiAgICAgKlxuICAgICAqIEByZXR1cm5zIHtzdHJpbmd9IFZpZXdNb2RlXG4gICAgICovXG4gICAgZ2V0QmFzZVJlY29yZCgpOiBSZWNvcmQge1xuICAgICAgICBpZiAoIXRoaXMuaW50ZXJuYWxTdGF0ZSkge1xuICAgICAgICAgICAgcmV0dXJuIG51bGw7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIHRoaXMucmVjb3JkU3RvcmUuZ2V0QmFzZVJlY29yZCgpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldCBjdXJyZW50IHZpZXcgbW9kZVxuICAgICAqXG4gICAgICogQHJldHVybnMge3N0cmluZ30gVmlld01vZGVcbiAgICAgKi9cbiAgICBnZXRNb2RlKCk6IFZpZXdNb2RlIHtcbiAgICAgICAgaWYgKCF0aGlzLmludGVybmFsU3RhdGUpIHtcbiAgICAgICAgICAgIHJldHVybiBudWxsO1xuICAgICAgICB9XG4gICAgICAgIHJldHVybiB0aGlzLmludGVybmFsU3RhdGUubW9kZTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBTZXQgbmV3IG1vZGVcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBtb2RlIFZpZXdNb2RlXG4gICAgICovXG4gICAgc2V0TW9kZShtb2RlOiBWaWV3TW9kZSk6IHZvaWQge1xuICAgICAgICB0aGlzLnVwZGF0ZVN0YXRlKHsuLi50aGlzLmludGVybmFsU3RhdGUsIG1vZGV9KTtcbiAgICB9XG5cbiAgICBnZXRMaWNlbnNlVGV4dCgpOiBzdHJpbmcge1xuICAgICAgICByZXR1cm4gdGhpcy5sYW5ndWFnZS5nZXRGaWVsZExhYmVsKCdTVUlURThfTElDRU5TRV9DT05URU5UJyk7XG4gICAgfVxuXG4gICAgZ2V0TWV0YWRhdGEoKTogSW5zdGFsbFZpZXdNZXRhZGF0YSB7XG4gICAgICAgIHRoaXMudXJsID0gd2luZG93LmxvY2F0aW9uLm9yaWdpbiArIHdpbmRvdy5sb2NhdGlvbi5wYXRobmFtZTtcbiAgICAgICAgdGhpcy51cmwgPSB0cmltRW5kKHRoaXMudXJsLCAnLycpO1xuICAgICAgICByZXR1cm4ge1xuICAgICAgICAgICAgYWN0aW9uczogW10sXG4gICAgICAgICAgICB0ZW1wbGF0ZU1ldGE6IHtcbiAgICAgICAgICAgICAgICBtYXhDb2x1bW5zOiAyLFxuICAgICAgICAgICAgICAgIHVzZVRhYnM6IHRydWUsXG4gICAgICAgICAgICAgICAgdGFiRGVmczoge1xuICAgICAgICAgICAgICAgICAgICBMQkxfQ09ORklHOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICBuZXdUYWI6IHRydWUsXG4gICAgICAgICAgICAgICAgICAgICAgICBwYW5lbERlZmF1bHQ6ICdleHBhbmRlZCdcbiAgICAgICAgICAgICAgICAgICAgfSBhcyBUYWJEZWZpbml0aW9uXG4gICAgICAgICAgICAgICAgfSBhcyBUYWJEZWZpbml0aW9uc1xuICAgICAgICAgICAgfSBhcyBSZWNvcmRUZW1wbGF0ZU1ldGFkYXRhLFxuICAgICAgICAgICAgcGFuZWxzOiBbXG4gICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICBrZXk6ICdMQkxfQ09ORklHJyxcbiAgICAgICAgICAgICAgICAgICAgZGlzcGxheSQ6IG9mKHRydWUpLnBpcGUoc2hhcmVSZXBsYXkoMSkpLFxuICAgICAgICAgICAgICAgICAgICByb3dzOiBbXG4gICAgICAgICAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgY29sczogW1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBuYW1lOiAnc2l0ZV9ob3N0JyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsOiAnTEJMX1NJVEVDRkdfVVJMJyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHR5cGU6ICd2YXJjaGFyJyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGZpZWxkRGVmaW5pdGlvbjoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwibmFtZVwiOiBcInNpdGVfaG9zdFwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwidm5hbWVcIjogXCJMQkxfU0lURUNGR19VUkxcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcInR5cGVcIjogXCJ2YXJjaGFyXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJyZXF1aXJlZFwiOiB0cnVlLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwiZGVmYXVsdFwiOiB0aGlzLnVybD8udG9TdHJpbmcoKSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcImRlZmF1bHRWYWx1ZU1vZGVzXCI6IFtcImNyZWF0ZVwiLCBcImVkaXRcIl1cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gYXMgRmllbGREZWZpbml0aW9uLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGFzIFBhbmVsQ2VsbCxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmFtZTogJ2RlbW9EYXRhJyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsOiAnTEJMX0RCQ09ORl9ERU1PX0RBVEEnLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogJ2VudW0nLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZmllbGREZWZpbml0aW9uOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmFtZTogXCJkZW1vRGF0YVwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHZuYW1lOiBcIkxCTF9EQkNPTkZfREVNT19EQVRBXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJlbnVtXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgb3B0aW9uczogXCJfX25vX29wdGlvbnNfX1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJlcXVpcmVkOiB0cnVlLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwiZGVmYXVsdFwiOiAnbm8nLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwiZGVmYXVsdFZhbHVlTW9kZXNcIjogW1wiY3JlYXRlXCIsIFwiZWRpdFwiXSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBtZXRhZGF0YToge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBleHRyYU9wdGlvbnM6IFtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZTogXCJ5ZXNcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbEtleTogXCJMQkxfWUVTXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGFzIE9wdGlvbixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2YWx1ZTogXCJub1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGxhYmVsS2V5OiBcIkxCTF9OT1wiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSBhcyBPcHRpb24sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF0gYXMgT3B0aW9uW11cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGFzIEZpZWxkTWV0YWRhdGFcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gYXMgRmllbGREZWZpbml0aW9uLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGFzIFBhbmVsQ2VsbCxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICBdXG4gICAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIGNvbHM6IFtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmFtZTogJ2RiX2NvbmZpZycsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbDogJ0xCTF9EQkNPTkZfVElUTEUnLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogJ2dyb3VwZWQtZmllbGQnLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZmllbGREZWZpbml0aW9uOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmFtZTogXCJkYl9jb25maWdcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB2bmFtZTogXCJMQkxfREJDT05GX1RJVExFXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdHlwZTogXCJncm91cGVkLWZpZWxkXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbGF5b3V0OiBbXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwiZGJfdXNlcm5hbWVcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkYl9wYXNzd29yZFwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcImRiX2hvc3RcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkYl9uYW1lXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwiZGJfcG9ydFwiXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBkaXNwbGF5OiBcInZlcnRpY2FsXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZ3JvdXBGaWVsZHM6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkYl91c2VybmFtZVwiOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcIm5hbWVcIjogXCJkYl91c2VybmFtZVwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJ0eXBlXCI6IFwidmFyY2hhclwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJ2bmFtZVwiOiBcIkxCTF9EQkNPTkZfU1VJVEVfREJfVVNFUlwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJsYWJlbEtleVwiOiBcIkxCTF9EQkNPTkZfU1VJVEVfREJfVVNFUlwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJsYWJlbFwiOiBcIkxCTF9EQkNPTkZfU1VJVEVfREJfVVNFUlwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJzaG93TGFiZWxcIjogW1wiKlwiXSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwicmVxdWlyZWRcIjogdHJ1ZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkYl9wYXNzd29yZFwiOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcIm5hbWVcIjogXCJkYl9wYXNzd29yZFwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJ0eXBlXCI6IFwicGFzc3dvcmRcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwidm5hbWVcIjogXCJMQkxfREJDT05GX0RCX1BBU1NXT1JEXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcImxhYmVsS2V5XCI6IFwiTEJMX0RCQ09ORl9EQl9QQVNTV09SRFwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJzaG93TGFiZWxcIjogW1wiKlwiXSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwicmVxdWlyZWRcIjogdHJ1ZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkYl9ob3N0XCI6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwibmFtZVwiOiBcImRiX2hvc3RcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwidHlwZVwiOiBcInZhcmNoYXJcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwidm5hbWVcIjogXCJMQkxfREJDT05GX0hPU1RfTkFNRVwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJsYWJlbEtleVwiOiBcIkxCTF9EQkNPTkZfSE9TVF9OQU1FXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcInNob3dMYWJlbFwiOiBbXCIqXCJdLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJyZXF1aXJlZFwiOiB0cnVlLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcImRiX25hbWVcIjoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJuYW1lXCI6IFwiZGJfbmFtZVwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJ0eXBlXCI6IFwidmFyY2hhclwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJ2bmFtZVwiOiBcIkxCTF9EQkNPTkZfREJfTkFNRVwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJsYWJlbEtleVwiOiBcIkxCTF9EQkNPTkZfREJfTkFNRVwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJzaG93TGFiZWxcIjogW1wiKlwiXSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwicmVxdWlyZWRcIjogdHJ1ZSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkYl9wb3J0XCI6IHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwibmFtZVwiOiBcImRiX3BvcnRcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwidHlwZVwiOiBcInZhcmNoYXJcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwidm5hbWVcIjogXCJMQkxfREJDT05GX0RCX1BPUlRcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwibGFiZWxLZXlcIjogXCJMQkxfREJDT05GX0RCX1BPUlRcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwic2hvd0xhYmVsXCI6IFtcIipcIl0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcInJlcXVpcmVkXCI6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkZWZhdWx0XCI6JzMzMDYnLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkZWZhdWx0VmFsdWVNb2Rlc1wiOiBbXCJjcmVhdGVcIiwgXCJlZGl0XCJdXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHNob3dMYWJlbDoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBlZGl0OiBbJyonXVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gYXMgRmllbGREZWZpbml0aW9uLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9IGFzIFBhbmVsQ2VsbCxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgbmFtZTogJ2FkbWluX2NvbmZpZycsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYWJlbDogJ0xCTF9TSVRFQ0ZHX1RJVExFJyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIHR5cGU6ICdncm91cGVkLWZpZWxkJyxcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGZpZWxkRGVmaW5pdGlvbjoge1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIG5hbWU6IFwiYWRtaW5fY29uZmlnXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgdm5hbWU6IFwiTEJMX1NJVEVDRkdfVElUTEVcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB0eXBlOiBcImdyb3VwZWQtZmllbGRcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBsYXlvdXQ6IFtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJzaXRlX3VzZXJuYW1lXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwic2l0ZV9wYXNzd29yZFwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIF0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZGlzcGxheTogXCJ2ZXJ0aWNhbFwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGdyb3VwRmllbGRzOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwic2l0ZV91c2VybmFtZVwiOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcIm5hbWVcIjogXCJzaXRlX3VzZXJuYW1lXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcInR5cGVcIjogXCJ2YXJjaGFyXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcInZuYW1lXCI6IFwiTEJMX1NJVEVDRkdfQURNSU5fTmFtZVwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJsYWJlbEtleVwiOiBcIkxCTF9TSVRFQ0ZHX0FETUlOX05hbWVcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwic2hvd0xhYmVsXCI6IFtcImVkaXRcIl0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcInJlcXVpcmVkXCI6IHRydWUsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcImRlZmF1bHRcIjogXCJhZG1pblwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJkZWZhdWx0VmFsdWVNb2Rlc1wiOiBbXCJjcmVhdGVcIiwgXCJlZGl0XCJdXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwic2l0ZV9wYXNzd29yZFwiOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcIm5hbWVcIjogXCJzaXRlX3Bhc3N3b3JkXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcInR5cGVcIjogXCJwYXNzd29yZFwiLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJ2bmFtZVwiOiBcIkxCTF9TSVRFQ0ZHX0FETUlOX1BBU1NcIixcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIFwibGFiZWxLZXlcIjogXCJMQkxfU0lURUNGR19BRE1JTl9QQVNTXCIsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICBcInNob3dMYWJlbFwiOiBbXCJlZGl0XCJdLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgXCJyZXF1aXJlZFwiOiB0cnVlLFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICB9LFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgc2hvd0xhYmVsOiB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIGVkaXQ6IFsnKiddXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgfSBhcyBGaWVsZERlZmluaXRpb24sXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgIH0gYXMgUGFuZWxDZWxsXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgXSBhcyBQYW5lbENlbGxbXVxuICAgICAgICAgICAgICAgICAgICAgICAgfSBhcyBQYW5lbFJvd1xuICAgICAgICAgICAgICAgICAgICBdIGFzIFBhbmVsUm93W11cbiAgICAgICAgICAgICAgICB9IGFzIFBhbmVsXG4gICAgICAgICAgICBdIGFzIFBhbmVsW10sXG4gICAgICAgIH0gYXMgSW5zdGFsbFZpZXdNZXRhZGF0YTtcbiAgICB9XG5cbiAgICBnZXRNZXRhZGF0YSQoKTogT2JzZXJ2YWJsZTxJbnN0YWxsVmlld01ldGFkYXRhPiB7XG4gICAgICAgIHJldHVybiBvZih0aGlzLmdldE1ldGFkYXRhKCkpO1xuICAgIH1cblxuICAgIGdldE1vZHVsZU5hbWUoKTogc3RyaW5nIHtcbiAgICAgICAgcmV0dXJuICdpbnN0YWxsJztcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBQYXJzZSBxdWVyeSBwYXJhbXNcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBwYXJhbXMgdG8gc2V0XG4gICAgICovXG4gICAgcHJvdGVjdGVkIHBhcnNlUGFyYW1zKHBhcmFtczogUGFyYW1zID0ge30pOiB2b2lkIHtcbiAgICAgICAgaWYgKCFwYXJhbXMpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IGN1cnJlbnRQYXJhbXMgPSB7Li4udGhpcy5pbnRlcm5hbFN0YXRlLnBhcmFtc307XG4gICAgICAgIE9iamVjdC5rZXlzKHBhcmFtcykuZm9yRWFjaChwYXJhbUtleSA9PiB7XG4gICAgICAgICAgICBpZiAoIWlzVm9pZChjdXJyZW50UGFyYW1zW3BhcmFtS2V5XSkpIHtcbiAgICAgICAgICAgICAgICBjdXJyZW50UGFyYW1zW3BhcmFtS2V5XSA9IHBhcmFtc1twYXJhbUtleV07XG4gICAgICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcblxuICAgICAgICB0aGlzLnBhcmFtcyA9IHBhcmFtcztcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBVcGRhdGUgdGhlIHN0YXRlXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gc3RhdGUgdG8gc2V0XG4gICAgICovXG4gICAgcHJvdGVjdGVkIHVwZGF0ZVN0YXRlKHN0YXRlOiBJbnN0YWxsVmlld1N0YXRlKTogdm9pZCB7XG4gICAgICAgIHRoaXMuc3RvcmUubmV4dCh0aGlzLmludGVybmFsU3RhdGUgPSBzdGF0ZSk7XG4gICAgfVxuXG4gICAgZ2V0SWdub3JlU3lzdGVtQ2hlY2tzRmllbGQoKTogRmllbGQge1xuICAgICAgICByZXR1cm4gdGhpcy5yZWNvcmRTdG9yZS5nZXRTdGFnaW5nKCkuZmllbGRzWydzeXNfY2hlY2tfb3B0aW9uJ107XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogR2V0IHZpZXcgZmllbGRzIG9ic2VydmFibGVcbiAgICAgKlxuICAgICAqIEByZXR1cm5zIHtvYmplY3R9IE9ic2VydmFibGU8Vmlld0ZpZWxkRGVmaW5pdGlvbltdPlxuICAgICAqL1xuICAgIHByb3RlY3RlZCBnZXRWaWV3RmllbGRzT2JzZXJ2YWJsZSgpOiBPYnNlcnZhYmxlPFZpZXdGaWVsZERlZmluaXRpb25bXT4ge1xuICAgICAgICByZXR1cm4gdGhpcy5nZXRNZXRhZGF0YSQoKS5waXBlKG1hcCgobWV0YTogSW5zdGFsbFZpZXdNZXRhZGF0YSkgPT4ge1xuICAgICAgICAgICAgY29uc3QgZmllbGRzOiBWaWV3RmllbGREZWZpbml0aW9uW10gPSBbXTtcbiAgICAgICAgICAgIG1ldGEucGFuZWxzLmZvckVhY2gocGFuZWwgPT4ge1xuICAgICAgICAgICAgICAgIHBhbmVsLnJvd3MuZm9yRWFjaChyb3cgPT4ge1xuICAgICAgICAgICAgICAgICAgICByb3cuY29scy5mb3JFYWNoKGNvbCA9PiB7XG4gICAgICAgICAgICAgICAgICAgICAgICBmaWVsZHMucHVzaChjb2wpO1xuICAgICAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICBmaWVsZHMucHVzaChcbiAgICAgICAgICAgICAgICB7XG4gICAgICAgICAgICAgICAgICAgIFwibmFtZVwiOiBcInN5c19jaGVja19vcHRpb25cIixcbiAgICAgICAgICAgICAgICAgICAgXCJ0eXBlXCI6IFwiYm9vbGVhblwiLFxuICAgICAgICAgICAgICAgICAgICBmaWVsZERlZmluaXRpb246IHtcbiAgICAgICAgICAgICAgICAgICAgICAgIFwibmFtZVwiOiBcInN5c19jaGVja19vcHRpb25cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwidHlwZVwiOiBcImJvb2xlYW5cIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwidm5hbWVcIjogXCJMQkxfU1lTX0NIRUNLX1dBUk5JTkdcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwibGFiZWxLZXlcIjogXCJMQkxfU1lTX0NIRUNLX1dBUk5JTkdcIixcbiAgICAgICAgICAgICAgICAgICAgICAgIFwic2hvd0xhYmVsXCI6IFtcImVkaXRcIl0sXG4gICAgICAgICAgICAgICAgICAgICAgICBcInJlcXVpcmVkXCI6IGZhbHNlLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJ2YWx1ZVwiOiAnZmFsc2UnLFxuICAgICAgICAgICAgICAgICAgICAgICAgXCJkZWZhdWx0XCI6ICdmYWxzZScsXG4gICAgICAgICAgICAgICAgICAgICAgICBcImRlZmF1bHRWYWx1ZU1vZGVzXCI6IFtcImNyZWF0ZVwiLCBcImVkaXRcIl1cbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0gYXMgVmlld0ZpZWxkRGVmaW5pdGlvblxuICAgICAgICAgICAgKTtcblxuICAgICAgICAgICAgcmV0dXJuIGZpZWxkcztcbiAgICAgICAgfSkpO1xuICAgIH1cbn1cbiJdfQ==