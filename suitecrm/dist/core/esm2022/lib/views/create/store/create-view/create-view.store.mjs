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
import { of } from 'rxjs';
import { catchError, finalize, shareReplay, tap } from 'rxjs/operators';
import { StatisticsBatch } from '../../../../store/statistics/statistics-batch.service';
import { RecordViewStore } from '../../../record/store/record-view/record-view.store';
import { RecordFetchGQL } from '../../../../store/record/graphql/api.record.get';
import { RecordSaveGQL } from '../../../../store/record/graphql/api.record.save';
import { AppStateStore } from '../../../../store/app-state/app-state.store';
import { LanguageStore } from '../../../../store/language/language.store';
import { NavigationStore } from '../../../../store/navigation/navigation.store';
import { ModuleNavigation } from '../../../../services/navigation/module-navigation/module-navigation.service';
import { MetadataStore } from '../../../../store/metadata/metadata.store.service';
import { RecordManager } from '../../../../services/record/record.manager';
import { LocalStorageService } from '../../../../services/local-storage/local-storage.service';
import { SubpanelStoreFactory } from '../../../../containers/subpanel/store/subpanel/subpanel.store.factory';
import { AuthService } from '../../../../services/auth/auth.service';
import { MessageService } from '../../../../services/message/message.service';
import { RecordStoreFactory } from '../../../../store/record/record.store.factory';
import { UserPreferenceStore } from '../../../../store/user-preference/user-preference.store';
import { PanelLogicManager } from '../../../../components/panel-logic/panel-logic.manager';
import * as i0 from "@angular/core";
import * as i1 from "../../../../store/record/graphql/api.record.get";
import * as i2 from "../../../../store/record/graphql/api.record.save";
import * as i3 from "../../../../store/app-state/app-state.store";
import * as i4 from "../../../../store/language/language.store";
import * as i5 from "../../../../store/navigation/navigation.store";
import * as i6 from "../../../../services/navigation/module-navigation/module-navigation.service";
import * as i7 from "../../../../store/metadata/metadata.store.service";
import * as i8 from "../../../../services/local-storage/local-storage.service";
import * as i9 from "../../../../services/message/message.service";
import * as i10 from "../../../../containers/subpanel/store/subpanel/subpanel.store.factory";
import * as i11 from "../../../../services/record/record.manager";
import * as i12 from "../../../../store/statistics/statistics-batch.service";
import * as i13 from "../../../../services/auth/auth.service";
import * as i14 from "../../../../store/record/record.store.factory";
import * as i15 from "../../../../store/user-preference/user-preference.store";
import * as i16 from "../../../../components/panel-logic/panel-logic.manager";
class CreateViewStore extends RecordViewStore {
    constructor(recordFetchGQL, recordSaveGQL, appStateStore, languageStore, navigationStore, moduleNavigation, metadataStore, localStorage, message, subpanelFactory, recordManager, statisticsBatch, auth, recordStoreFactory, preferences, panelLogicManager) {
        super(recordFetchGQL, recordSaveGQL, appStateStore, languageStore, navigationStore, moduleNavigation, metadataStore, localStorage, message, subpanelFactory, recordManager, statisticsBatch, recordStoreFactory, preferences, panelLogicManager);
        this.recordFetchGQL = recordFetchGQL;
        this.recordSaveGQL = recordSaveGQL;
        this.appStateStore = appStateStore;
        this.languageStore = languageStore;
        this.navigationStore = navigationStore;
        this.moduleNavigation = moduleNavigation;
        this.metadataStore = metadataStore;
        this.localStorage = localStorage;
        this.message = message;
        this.subpanelFactory = subpanelFactory;
        this.recordManager = recordManager;
        this.statisticsBatch = statisticsBatch;
        this.auth = auth;
        this.recordStoreFactory = recordStoreFactory;
        this.preferences = preferences;
        this.panelLogicManager = panelLogicManager;
    }
    /**
     * Initial record load if not cached and update state.
     * Returns observable to be used in resolver if needed
     *
     * @param {string} module to use
     * @param {string} recordID to use
     * @param {string} mode to use
     * @param {object} params to set
     * @returns {object} Observable<any>
     */
    init(module, recordID, mode = 'detail', params = {}) {
        this.internalState.module = module;
        this.internalState.recordID = recordID;
        this.setMode(mode);
        this.parseParams(params);
        this.calculateShowWidgets();
        this.showTopWidget = false;
        this.showSubpanels = false;
        const isDuplicate = this.params.isDuplicate ?? false;
        const isOriginalDuplicate = this.params.originalDuplicateId ?? false;
        if (!isDuplicate && !isOriginalDuplicate) {
            this.initRecord(params);
        }
        return this.load();
    }
    save() {
        this.appStateStore.updateLoading(`${this.internalState.module}-record-save-new`, true);
        return this.recordStore.save().pipe(catchError(() => {
            this.message.addDangerMessageByKey('LBL_ERROR_SAVING');
            return of({});
        }), finalize(() => {
            this.setMode('detail');
            this.appStateStore.updateLoading(`${this.internalState.module}-record-save-new`, false);
        }));
    }
    /**
     * Init record using params
     *
     * @param {object} params queryParams
     */
    initRecord(params = {}) {
        const user = this.auth.getCurrentUser();
        const blankRecord = {
            id: '',
            type: '',
            module: this.internalState.module,
            /* eslint-disable camelcase,@typescript-eslint/camelcase */
            attributes: {
                assigned_user_id: user.id,
                assigned_user_name: {
                    id: user.id,
                    user_name: user.userName
                },
                relate_to: params?.return_relationship,
                relate_id: params?.parent_id
            }
            /* eslint-enable camelcase,@typescript-eslint/camelcase */
        };
        this.recordManager.injectParamFields(params, blankRecord, this.getVardefs());
        this.recordStore.init(blankRecord, true);
    }
    /**
     * Load / reload record using current pagination and criteria
     *
     * @returns {object} Observable<RecordViewState>
     */
    load() {
        if ((this.params.isDuplicate ?? false) && (this.params.originalDuplicateId ?? false)) {
            this.updateState({
                ...this.internalState,
                loading: true
            });
            return this.recordStore.retrieveRecord(this.internalState.module, this.params.originalDuplicateId, false).pipe(tap((data) => {
                data.id = '';
                data.attributes.id = '';
                // eslint-disable-next-line camelcase,@typescript-eslint/camelcase
                data.attributes.date_entered = '';
                this.recordManager.injectParamFields(this.params, data, this.getVardefs());
                this.recordStore.setRecord(data);
                this.updateState({
                    ...this.internalState,
                    module: data.module,
                    loading: false
                });
            }));
        }
        return of(this.recordStore.getBaseRecord()).pipe(shareReplay());
    }
    /**
     * Calculate if widgets are to display
     */
    calculateShowWidgets() {
        const show = false;
        this.showSidebarWidgets = show;
        this.widgets = show;
    }
    static { this.ɵfac = function CreateViewStore_Factory(t) { return new (t || CreateViewStore)(i0.ɵɵinject(i1.RecordFetchGQL), i0.ɵɵinject(i2.RecordSaveGQL), i0.ɵɵinject(i3.AppStateStore), i0.ɵɵinject(i4.LanguageStore), i0.ɵɵinject(i5.NavigationStore), i0.ɵɵinject(i6.ModuleNavigation), i0.ɵɵinject(i7.MetadataStore), i0.ɵɵinject(i8.LocalStorageService), i0.ɵɵinject(i9.MessageService), i0.ɵɵinject(i10.SubpanelStoreFactory), i0.ɵɵinject(i11.RecordManager), i0.ɵɵinject(i12.StatisticsBatch), i0.ɵɵinject(i13.AuthService), i0.ɵɵinject(i14.RecordStoreFactory), i0.ɵɵinject(i15.UserPreferenceStore), i0.ɵɵinject(i16.PanelLogicManager)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: CreateViewStore, factory: CreateViewStore.ɵfac }); }
}
export { CreateViewStore };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(CreateViewStore, [{
        type: Injectable
    }], function () { return [{ type: i1.RecordFetchGQL }, { type: i2.RecordSaveGQL }, { type: i3.AppStateStore }, { type: i4.LanguageStore }, { type: i5.NavigationStore }, { type: i6.ModuleNavigation }, { type: i7.MetadataStore }, { type: i8.LocalStorageService }, { type: i9.MessageService }, { type: i10.SubpanelStoreFactory }, { type: i11.RecordManager }, { type: i12.StatisticsBatch }, { type: i13.AuthService }, { type: i14.RecordStoreFactory }, { type: i15.UserPreferenceStore }, { type: i16.PanelLogicManager }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiY3JlYXRlLXZpZXcuc3RvcmUuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi8uLi8uLi8uLi8uLi9jb3JlL2FwcC9jb3JlL3NyYy9saWIvdmlld3MvY3JlYXRlL3N0b3JlL2NyZWF0ZS12aWV3L2NyZWF0ZS12aWV3LnN0b3JlLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0F3Qkc7QUFFSCxPQUFPLEVBQUMsVUFBVSxFQUFDLE1BQU0sZUFBZSxDQUFDO0FBQ3pDLE9BQU8sRUFBYSxFQUFFLEVBQUMsTUFBTSxNQUFNLENBQUM7QUFDcEMsT0FBTyxFQUFDLFVBQVUsRUFBRSxRQUFRLEVBQUUsV0FBVyxFQUFFLEdBQUcsRUFBQyxNQUFNLGdCQUFnQixDQUFDO0FBRXRFLE9BQU8sRUFBQyxlQUFlLEVBQUMsTUFBTSx1REFBdUQsQ0FBQztBQUN0RixPQUFPLEVBQUMsZUFBZSxFQUFDLE1BQU0scURBQXFELENBQUM7QUFDcEYsT0FBTyxFQUFDLGNBQWMsRUFBQyxNQUFNLGlEQUFpRCxDQUFDO0FBQy9FLE9BQU8sRUFBQyxhQUFhLEVBQUMsTUFBTSxrREFBa0QsQ0FBQztBQUMvRSxPQUFPLEVBQUMsYUFBYSxFQUFDLE1BQU0sNkNBQTZDLENBQUM7QUFDMUUsT0FBTyxFQUFDLGFBQWEsRUFBQyxNQUFNLDJDQUEyQyxDQUFDO0FBQ3hFLE9BQU8sRUFBQyxlQUFlLEVBQUMsTUFBTSwrQ0FBK0MsQ0FBQztBQUM5RSxPQUFPLEVBQUMsZ0JBQWdCLEVBQUMsTUFBTSw2RUFBNkUsQ0FBQztBQUM3RyxPQUFPLEVBQUMsYUFBYSxFQUFDLE1BQU0sbURBQW1ELENBQUM7QUFDaEYsT0FBTyxFQUFDLGFBQWEsRUFBQyxNQUFNLDRDQUE0QyxDQUFDO0FBQ3pFLE9BQU8sRUFBQyxtQkFBbUIsRUFBQyxNQUFNLDBEQUEwRCxDQUFDO0FBQzdGLE9BQU8sRUFBQyxvQkFBb0IsRUFBQyxNQUFNLHVFQUF1RSxD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QUs7aUJBQ2pCLENBQUMsQ0FBQztZQUNQLENBQUMsQ0FBQyxDQUNMLENBQUM7U0FDTDtRQUNELE9BQU8sRUFBRSxDQUFDLElBQUksQ0FBQyxXQUFXLENBQUMsYUFBYSxFQUFFLENBQUMsQ0FBQyxJQUFJLENBQUMsV0FBVyxFQUFFLENBQUMsQ0FBQztJQUNwRSxDQUFDO0lBRUQ7O09BRUc7SUFDTyxvQkFBb0I7UUFDMUIsTUFBTSxJQUFJLEdBQUcsS0FBSyxDQUFDO1FBQ25CLElBQUksQ0FBQyxrQkFBa0IsR0FBRyxJQUFJLENBQUM7UUFDL0IsSUFBSSxDQUFDLE9BQU8sR0FBRyxJQUFJLENBQUM7SUFDeEIsQ0FBQztnRkExSlEsZUFBZTt1RUFBZixlQUFlLFdBQWYsZUFBZTs7U0FBZixlQUFlO3VGQUFmLGVBQWU7Y0FEM0IsVUFBVSIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogU3VpdGVDUk0gaXMgYSBjdXN0b21lciByZWxhdGlvbnNoaXAgbWFuYWdlbWVudCBwcm9ncmFtIGRldmVsb3BlZCBieSBTYWxlc0FnaWxpdHkgTHRkLlxuICogQ29weXJpZ2h0IChDKSAyMDIxIFNhbGVzQWdpbGl0eSBMdGQuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGZyZWUgc29mdHdhcmU7IHlvdSBjYW4gcmVkaXN0cmlidXRlIGl0IGFuZC9vciBtb2RpZnkgaXQgdW5kZXJcbiAqIHRoZSB0ZXJtcyBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIHZlcnNpb24gMyBhcyBwdWJsaXNoZWQgYnkgdGhlXG4gKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4gKiB0byBTZWN0aW9uIDE1IGFzIHBlcm1pdHRlZCBpbiBTZWN0aW9uIDcoYSk6IEZPUiBBTlkgUEFSVCBPRiBUSEUgQ09WRVJFRCBXT1JLXG4gKiBJTiBXSElDSCBUSEUgQ09QWVJJR0hUIElTIE9XTkVEIEJZIFNBTEVTQUdJTElUWSwgU0FMRVNBR0lMSVRZIERJU0NMQUlNUyBUSEVcbiAqIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBkaXN0cmlidXRlZCBpbiB0aGUgaG9wZSB0aGF0IGl0IHdpbGwgYmUgdXNlZnVsLCBidXQgV0lUSE9VVFxuICogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiAqIEZPUiBBIFBBUlRJQ1VMQVIgUFVSUE9TRS4gU2VlIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgZm9yIG1vcmVcbiAqIGRldGFpbHMuXG4gKlxuICogWW91IHNob3VsZCBoYXZlIHJlY2VpdmVkIGEgY29weSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiBhbG9uZyB3aXRoIHRoaXMgcHJvZ3JhbS4gIElmIG5vdCwgc2VlIDxodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvPi5cbiAqXG4gKiBJbiBhY2NvcmRhbmNlIHdpdGggU2VjdGlvbiA3KGIpIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIHZlcnNpb24gMywgdGhlc2UgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IHJldGFpbiB0aGUgZGlzcGxheSBvZiB0aGVcbiAqIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4gKiBmZWFzaWJsZSBmb3IgdGVjaG5pY2FsIHJlYXNvbnMsIHRoZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgZGlzcGxheVxuICogdGhlIHdvcmRzIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIuXG4gKi9cblxuaW1wb3J0IHtJbmplY3RhYmxlfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7T2JzZXJ2YWJsZSwgb2Z9IGZyb20gJ3J4anMnO1xuaW1wb3J0IHtjYXRjaEVycm9yLCBmaW5hbGl6ZSwgc2hhcmVSZXBsYXksIHRhcH0gZnJvbSAncnhqcy9vcGVyYXRvcnMnO1xuaW1wb3J0IHtQYXJhbXN9IGZyb20gJ0Bhbmd1bGFyL3JvdXRlcic7XG5pbXBvcnQge1N0YXRpc3RpY3NCYXRjaH0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvc3RhdGlzdGljcy9zdGF0aXN0aWNzLWJhdGNoLnNlcnZpY2UnO1xuaW1wb3J0IHtSZWNvcmRWaWV3U3RvcmV9IGZyb20gJy4uLy4uLy4uL3JlY29yZC9zdG9yZS9yZWNvcmQtdmlldy9yZWNvcmQtdmlldy5zdG9yZSc7XG5pbXBvcnQge1JlY29yZEZldGNoR1FMfSBmcm9tICcuLi8uLi8uLi8uLi9zdG9yZS9yZWNvcmQvZ3JhcGhxbC9hcGkucmVjb3JkLmdldCc7XG5pbXBvcnQge1JlY29yZFNhdmVHUUx9IGZyb20gJy4uLy4uLy4uLy4uL3N0b3JlL3JlY29yZC9ncmFwaHFsL2FwaS5yZWNvcmQuc2F2ZSc7XG5pbXBvcnQge0FwcFN0YXRlU3RvcmV9IGZyb20gJy4uLy4uLy4uLy4uL3N0b3JlL2FwcC1zdGF0ZS9hcHAtc3RhdGUuc3RvcmUnO1xuaW1wb3J0IHtMYW5ndWFnZVN0b3JlfSBmcm9tICcuLi8uLi8uLi8uLi9zdG9yZS9sYW5ndWFnZS9sYW5ndWFnZS5zdG9yZSc7XG5pbXBvcnQge05hdmlnYXRpb25TdG9yZX0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvbmF2aWdhdGlvbi9uYXZpZ2F0aW9uLnN0b3JlJztcbmltcG9ydCB7TW9kdWxlTmF2aWdhdGlvbn0gZnJvbSAnLi4vLi4vLi4vLi4vc2VydmljZXMvbmF2aWdhdGlvbi9tb2R1bGUtbmF2aWdhdGlvbi9tb2R1bGUtbmF2aWdhdGlvbi5zZXJ2aWNlJztcbmltcG9ydCB7TWV0YWRhdGFTdG9yZX0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvbWV0YWRhdGEvbWV0YWRhdGEuc3RvcmUuc2VydmljZSc7XG5pbXBvcnQge1JlY29yZE1hbmFnZXJ9IGZyb20gJy4uLy4uLy4uLy4uL3NlcnZpY2VzL3JlY29yZC9yZWNvcmQubWFuYWdlcic7XG5pbXBvcnQge0xvY2FsU3RvcmFnZVNlcnZpY2V9IGZyb20gJy4uLy4uLy4uLy4uL3NlcnZpY2VzL2xvY2FsLXN0b3JhZ2UvbG9jYWwtc3RvcmFnZS5zZXJ2aWNlJztcbmltcG9ydCB7U3VicGFuZWxTdG9yZUZhY3Rvcnl9IGZyb20gJy4uLy4uLy4uLy4uL2NvbnRhaW5lcnMvc3VicGFuZWwvc3RvcmUvc3VicGFuZWwvc3VicGFuZWwuc3RvcmUuZmFjdG9yeSc7XG5pbXBvcnQge0F1dGhTZXJ2aWNlfSBmcm9tICcuLi8uLi8uLi8uLi9zZXJ2aWNlcy9hdXRoL2F1dGguc2VydmljZSc7XG5pbXBvcnQge01lc3NhZ2VTZXJ2aWNlfSBmcm9tICcuLi8uLi8uLi8uLi9zZXJ2aWNlcy9tZXNzYWdlL21lc3NhZ2Uuc2VydmljZSc7XG5pbXBvcnQge1JlY29yZCwgVmlld01vZGV9IGZyb20gJ2NvbW1vbic7XG5pbXBvcnQge1JlY29yZFN0b3JlRmFjdG9yeX0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvcmVjb3JkL3JlY29yZC5zdG9yZS5mYWN0b3J5JztcbmltcG9ydCB7VXNlclByZWZlcmVuY2VTdG9yZX0gZnJvbSAnLi4vLi4vLi4vLi4vc3RvcmUvdXNlci1wcmVmZXJlbmNlL3VzZXItcHJlZmVyZW5jZS5zdG9yZSc7XG5pbXBvcnQge1BhbmVsTG9naWNNYW5hZ2VyfSBmcm9tICcuLi8uLi8uLi8uLi9jb21wb25lbnRzL3BhbmVsLWxvZ2ljL3BhbmVsLWxvZ2ljLm1hbmFnZXInO1xuXG5ASW5qZWN0YWJsZSgpXG5leHBvcnQgY2xhc3MgQ3JlYXRlVmlld1N0b3JlIGV4dGVuZHMgUmVjb3JkVmlld1N0b3JlIHtcblxuICAgIGNvbnN0cnVjdG9yKFxuICAgICAgICBwcm90ZWN0ZWQgcmVjb3JkRmV0Y2hHUUw6IFJlY29yZEZldGNoR1FMLFxuICAgICAgICBwcm90ZWN0ZWQgcmVjb3JkU2F2ZUdRTDogUmVjb3JkU2F2ZUdRTCxcbiAgICAgICAgcHJvdGVjdGVkIGFwcFN0YXRlU3RvcmU6IEFwcFN0YXRlU3RvcmUsXG4gICAgICAgIHByb3RlY3RlZCBsYW5ndWFnZVN0b3JlOiBMYW5ndWFnZVN0b3JlLFxuICAgICAgICBwcm90ZWN0ZWQgbmF2aWdhdGlvblN0b3JlOiBOYXZpZ2F0aW9uU3RvcmUsXG4gICAgICAgIHByb3RlY3RlZCBtb2R1bGVOYXZpZ2F0aW9uOiBNb2R1bGVOYXZpZ2F0aW9uLFxuICAgICAgICBwcm90ZWN0ZWQgbWV0YWRhdGFTdG9yZTogTWV0YWRhdGFTdG9yZSxcbiAgICAgICAgcHJvdGVjdGVkIGxvY2FsU3RvcmFnZTogTG9jYWxTdG9yYWdlU2VydmljZSxcbiAgICAgICAgcHJvdGVjdGVkIG1lc3NhZ2U6IE1lc3NhZ2VTZXJ2aWNlLFxuICAgICAgICBwcm90ZWN0ZWQgc3VicGFuZWxGYWN0b3J5OiBTdWJwYW5lbFN0b3JlRmFjdG9yeSxcbiAgICAgICAgcHJvdGVjdGVkIHJlY29yZE1hbmFnZXI6IFJlY29yZE1hbmFnZXIsXG4gICAgICAgIHByb3RlY3RlZCBzdGF0aXN0aWNzQmF0Y2g6IFN0YXRpc3RpY3NCYXRjaCxcbiAgICAgICAgcHJvdGVjdGVkIGF1dGg6IEF1dGhTZXJ2aWNlLFxuICAgICAgICBwcm90ZWN0ZWQgcmVjb3JkU3RvcmVGYWN0b3J5OiBSZWNvcmRTdG9yZUZhY3RvcnksXG4gICAgICAgIHByb3RlY3RlZCBwcmVmZXJlbmNlczogVXNlclByZWZlcmVuY2VTdG9yZSxcbiAgICAgICAgcHJvdGVjdGVkIHBhbmVsTG9naWNNYW5hZ2VyOiBQYW5lbExvZ2ljTWFuYWdlclxuICAgICkge1xuICAgICAgICBzdXBlcihcbiAgICAgICAgICAgIHJlY29yZEZldGNoR1FMLFxuICAgICAgICAgICAgcmVjb3JkU2F2ZUdRTCxcbiAgICAgICAgICAgIGFwcFN0YXRlU3RvcmUsXG4gICAgICAgICAgICBsYW5ndWFnZVN0b3JlLFxuICAgICAgICAgICAgbmF2aWdhdGlvblN0b3JlLFxuICAgICAgICAgICAgbW9kdWxlTmF2aWdhdGlvbixcbiAgICAgICAgICAgIG1ldGFkYXRhU3RvcmUsXG4gICAgICAgICAgICBsb2NhbFN0b3JhZ2UsXG4gICAgICAgICAgICBtZXNzYWdlLFxuICAgICAgICAgICAgc3VicGFuZWxGYWN0b3J5LFxuICAgICAgICAgICAgcmVjb3JkTWFuYWdlcixcbiAgICAgICAgICAgIHN0YXRpc3RpY3NCYXRjaCxcbiAgICAgICAgICAgIHJlY29yZFN0b3JlRmFjdG9yeSxcbiAgICAgICAgICAgIHByZWZlcmVuY2VzLFxuICAgICAgICAgICAgcGFuZWxMb2dpY01hbmFnZXJcbiAgICAgICAgKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBJbml0aWFsIHJlY29yZCBsb2FkIGlmIG5vdCBjYWNoZWQgYW5kIHVwZGF0ZSBzdGF0ZS5cbiAgICAgKiBSZXR1cm5zIG9ic2VydmFibGUgdG8gYmUgdXNlZCBpbiByZXNvbHZlciBpZiBuZWVkZWRcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBtb2R1bGUgdG8gdXNlXG4gICAgICogQHBhcmFtIHtzdHJpbmd9IHJlY29yZElEIHRvIHVzZVxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBtb2RlIHRvIHVzZVxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBwYXJhbXMgdG8gc2V0XG4gICAgICogQHJldHVybnMge29iamVjdH0gT2JzZXJ2YWJsZTxhbnk+XG4gICAgICovXG4gICAgcHVibGljIGluaXQobW9kdWxlOiBzdHJpbmcsIHJlY29yZElEOiBzdHJpbmcsIG1vZGUgPSAnZGV0YWlsJyBhcyBWaWV3TW9kZSwgcGFyYW1zOiBQYXJhbXMgPSB7fSk6IE9ic2VydmFibGU8UmVjb3JkPiB7XG4gICAgICAgIHRoaXMuaW50ZXJuYWxTdGF0ZS5tb2R1bGUgPSBtb2R1bGU7XG4gICAgICAgIHRoaXMuaW50ZXJuYWxTdGF0ZS5yZWNvcmRJRCA9IHJlY29yZElEO1xuICAgICAgICB0aGlzLnNldE1vZGUobW9kZSk7XG4gICAgICAgIHRoaXMucGFyc2VQYXJhbXMocGFyYW1zKTtcbiAgICAgICAgdGhpcy5jYWxjdWxhdGVTaG93V2lkZ2V0cygpO1xuICAgICAgICB0aGlzLnNob3dUb3BXaWRnZXQgPSBmYWxzZTtcbiAgICAgICAgdGhpcy5zaG93U3VicGFuZWxzID0gZmFsc2U7XG5cbiAgICAgICAgY29uc3QgaXNEdXBsaWNhdGUgPSB0aGlzLnBhcmFtcy5pc0R1cGxpY2F0ZSA/PyBmYWxzZTtcbiAgICAgICAgY29uc3QgaXNPcmlnaW5hbER1cGxpY2F0ZSA9IHRoaXMucGFyYW1zLm9yaWdpbmFsRHVwbGljYXRlSWQgPz8gZmFsc2U7XG5cbiAgICAgICAgaWYgKCFpc0R1cGxpY2F0ZSAmJiAhaXNPcmlnaW5hbER1cGxpY2F0ZSkge1xuICAgICAgICAgICAgdGhpcy5pbml0UmVjb3JkKHBhcmFtcyk7XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gdGhpcy5sb2FkKCk7XG4gICAgfVxuXG4gICAgc2F2ZSgpOiBPYnNlcnZhYmxlPFJlY29yZD4ge1xuICAgICAgICB0aGlzLmFwcFN0YXRlU3RvcmUudXBkYXRlTG9hZGluZyhgJHt0aGlzLmludGVybmFsU3RhdGUubW9kdWxlfS1yZWNvcmQtc2F2ZS1uZXdgLCB0cnVlKTtcblxuICAgICAgICByZXR1cm4gdGhpcy5yZWNvcmRTdG9yZS5zYXZlKCkucGlwZShcbiAgICAgICAgICAgIGNhdGNoRXJyb3IoKCkgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMubWVzc2FnZS5hZGREYW5nZXJNZXNzYWdlQnlLZXkoJ0xCTF9FUlJPUl9TQVZJTkcnKTtcbiAgICAgICAgICAgICAgICByZXR1cm4gb2Yoe30gYXMgUmVjb3JkKTtcbiAgICAgICAgICAgIH0pLFxuICAgICAgICAgICAgZmluYWxpemUoKCkgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMuc2V0TW9kZSgnZGV0YWlsJyBhcyBWaWV3TW9kZSk7XG4gICAgICAgICAgICAgICAgdGhpcy5hcHBTdGF0ZVN0b3JlLnVwZGF0ZUxvYWRpbmcoYCR7dGhpcy5pbnRlcm5hbFN0YXRlLm1vZHVsZX0tcmVjb3JkLXNhdmUtbmV3YCwgZmFsc2UpO1xuICAgICAgICAgICAgfSlcbiAgICAgICAgKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBJbml0IHJlY29yZCB1c2luZyBwYXJhbXNcbiAgICAgKlxuICAgICAqIEBwYXJhbSB7b2JqZWN0fSBwYXJhbXMgcXVlcnlQYXJhbXNcbiAgICAgKi9cbiAgICBwdWJsaWMgaW5pdFJlY29yZChwYXJhbXM6IFBhcmFtcyA9IHt9KTogdm9pZCB7XG4gICAgICAgIGNvbnN0IHVzZXIgPSB0aGlzLmF1dGguZ2V0Q3VycmVudFVzZXIoKTtcbiAgICAgICAgY29uc3QgYmxhbmtSZWNvcmQgPSB7XG4gICAgICAgICAgICBpZDogJycsXG4gICAgICAgICAgICB0eXBlOiAnJyxcbiAgICAgICAgICAgIG1vZHVsZTogdGhpcy5pbnRlcm5hbFN0YXRlLm1vZHVsZSxcbiAgICAgICAgICAgIC8qIGVzbGludC1kaXNhYmxlIGNhbWVsY2FzZSxAdHlwZXNjcmlwdC1lc2xpbnQvY2FtZWxjYXNlICovXG4gICAgICAgICAgICBhdHRyaWJ1dGVzOiB7XG4gICAgICAgICAgICAgICAgYXNzaWduZWRfdXNlcl9pZDogdXNlci5pZCxcbiAgICAgICAgICAgICAgICBhc3NpZ25lZF91c2VyX25hbWU6IHtcbiAgICAgICAgICAgICAgICAgICAgaWQ6IHVzZXIuaWQsXG4gICAgICAgICAgICAgICAgICAgIHVzZXJfbmFtZTogdXNlci51c2VyTmFtZVxuICAgICAgICAgICAgICAgIH0sXG4gICAgICAgICAgICAgICAgcmVsYXRlX3RvOiBwYXJhbXM/LnJldHVybl9yZWxhdGlvbnNoaXAsXG4gICAgICAgICAgICAgICAgcmVsYXRlX2lkOiBwYXJhbXM/LnBhcmVudF9pZFxuICAgICAgICAgICAgfVxuICAgICAgICAgICAgLyogZXNsaW50LWVuYWJsZSBjYW1lbGNhc2UsQHR5cGVzY3JpcHQtZXNsaW50L2NhbWVsY2FzZSAqL1xuICAgICAgICB9IGFzIFJlY29yZDtcblxuICAgICAgICB0aGlzLnJlY29yZE1hbmFnZXIuaW5qZWN0UGFyYW1GaWVsZHMocGFyYW1zLCBibGFua1JlY29yZCwgdGhpcy5nZXRWYXJkZWZzKCkpO1xuXG4gICAgICAgIHRoaXMucmVjb3JkU3RvcmUuaW5pdChibGFua1JlY29yZCwgdHJ1ZSk7XG4gICAgfVxuXG4gICAgLyoqXG4gICAgICogTG9hZCAvIHJlbG9hZCByZWNvcmQgdXNpbmcgY3VycmVudCBwYWdpbmF0aW9uIGFuZCBjcml0ZXJpYVxuICAgICAqXG4gICAgICogQHJldHVybnMge29iamVjdH0gT2JzZXJ2YWJsZTxSZWNvcmRWaWV3U3RhdGU+XG4gICAgICovXG4gICAgcHVibGljIGxvYWQoKTogT2JzZXJ2YWJsZTxSZWNvcmQ+IHtcbiAgICAgICAgaWYgKCh0aGlzLnBhcmFtcy5pc0R1cGxpY2F0ZSA/PyBmYWxzZSkgJiYgKHRoaXMucGFyYW1zLm9yaWdpbmFsRHVwbGljYXRlSWQgPz8gZmFsc2UpKSB7XG4gICAgICAgICAgICB0aGlzLnVwZGF0ZVN0YXRlKHtcbiAgICAgICAgICAgICAgICAuLi50aGlzLmludGVybmFsU3RhdGUsXG4gICAgICAgICAgICAgICAgbG9hZGluZzogdHJ1ZVxuICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgIHJldHVybiB0aGlzLnJlY29yZFN0b3JlLnJldHJpZXZlUmVjb3JkKFxuICAgICAgICAgICAgICAgIHRoaXMuaW50ZXJuYWxTdGF0ZS5tb2R1bGUsXG4gICAgICAgICAgICAgICAgdGhpcy5wYXJhbXMub3JpZ2luYWxEdXBsaWNhdGVJZCxcbiAgICAgICAgICAgICAgICBmYWxzZVxuICAgICAgICAgICAgKS5waXBlKFxuICAgICAgICAgICAgICAgIHRhcCgoZGF0YTogUmVjb3JkKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIGRhdGEuaWQgPSAnJztcbiAgICAgICAgICAgICAgICAgICAgZGF0YS5hdHRyaWJ1dGVzLmlkID0gJyc7XG4gICAgICAgICAgICAgICAgICAgIC8vIGVzbGludC1kaXNhYmxlLW5leHQtbGluZSBjYW1lbGNhc2UsQHR5cGVzY3JpcHQtZXNsaW50L2NhbWVsY2FzZVxuICAgICAgICAgICAgICAgICAgICBkYXRhLmF0dHJpYnV0ZXMuZGF0ZV9lbnRlcmVkID0gJyc7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMucmVjb3JkTWFuYWdlci5pbmplY3RQYXJhbUZpZWxkcyh0aGlzLnBhcmFtcywgZGF0YSwgdGhpcy5nZXRWYXJkZWZzKCkpO1xuICAgICAgICAgICAgICAgICAgICB0aGlzLnJlY29yZFN0b3JlLnNldFJlY29yZChkYXRhKTtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy51cGRhdGVTdGF0ZSh7XG4gICAgICAgICAgICAgICAgICAgICAgICAuLi50aGlzLmludGVybmFsU3RhdGUsXG4gICAgICAgICAgICAgICAgICAgICAgICBtb2R1bGU6IGRhdGEubW9kdWxlLFxuICAgICAgICAgICAgICAgICAgICAgICAgbG9hZGluZzogZmFsc2VcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfSlcbiAgICAgICAgICAgICk7XG4gICAgICAgIH1cbiAgICAgICAgcmV0dXJuIG9mKHRoaXMucmVjb3JkU3RvcmUuZ2V0QmFzZVJlY29yZCgpKS5waXBlKHNoYXJlUmVwbGF5KCkpO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIENhbGN1bGF0ZSBpZiB3aWRnZXRzIGFyZSB0byBkaXNwbGF5XG4gICAgICovXG4gICAgcHJvdGVjdGVkIGNhbGN1bGF0ZVNob3dXaWRnZXRzKCk6IHZvaWQge1xuICAgICAgICBjb25zdCBzaG93ID0gZmFsc2U7XG4gICAgICAgIHRoaXMuc2hvd1NpZGViYXJXaWRnZXRzID0gc2hvdztcbiAgICAgICAgdGhpcy53aWRnZXRzID0gc2hvdztcbiAgICB9XG59XG4iXX0=