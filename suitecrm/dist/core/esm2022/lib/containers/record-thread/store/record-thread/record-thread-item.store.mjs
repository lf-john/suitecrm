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
import { map } from 'rxjs/operators';
import { BaseRecordContainerStore } from '../../../../store/record-container/base-record-container.store';
import { AppStateStore } from '../../../../store/app-state/app-state.store';
import { MetadataStore } from '../../../../store/metadata/metadata.store.service';
import { MessageService } from '../../../../services/message/message.service';
import { FieldManager } from '../../../../services/record/field/field.manager';
import { LanguageStore } from '../../../../store/language/language.store';
import { RecordStoreFactory } from '../../../../store/record/record.store.factory';
import * as i0 from "@angular/core";
import * as i1 from "../../../../store/app-state/app-state.store";
import * as i2 from "../../../../store/metadata/metadata.store.service";
import * as i3 from "../../../../services/message/message.service";
import * as i4 from "../../../../services/record/field/field.manager";
import * as i5 from "../../../../store/language/language.store";
import * as i6 from "../../../../store/record/record.store.factory";
class RecordThreadItemStore extends BaseRecordContainerStore {
    constructor(appStateStore, meta, message, fieldManager, language, storeFactory) {
        super(appStateStore, meta, message, fieldManager, language, storeFactory);
        this.appStateStore = appStateStore;
        this.meta = meta;
        this.message = message;
        this.fieldManager = fieldManager;
        this.language = language;
        this.storeFactory = storeFactory;
    }
    /**
     * Get view fields observable
     *
     * @returns {object} Observable<ViewFieldDefinition[]>
     */
    getViewFields$() {
        return this.meta$.pipe(map((meta) => {
            const fieldsMap = {};
            const fields = [];
            const fieldDefinitions = meta.fields ?? {};
            Object.keys(fieldDefinitions).forEach(fieldName => {
                if (fieldDefinitions[fieldName]) {
                    fieldsMap[fieldName] = fieldDefinitions[fieldName];
                }
            });
            meta.headerLayout && meta.headerLayout.rows && meta.headerLayout.rows.forEach(row => {
                row.cols.forEach(col => {
                    if (col.field) {
                        fieldsMap[col.field.name] = col.field;
                    }
                });
            });
            meta.bodyLayout && meta.bodyLayout.rows && meta.bodyLayout.rows.forEach(row => {
                row.cols.forEach(col => {
                    if (col.field) {
                        fieldsMap[col.field.name] = col.field;
                    }
                });
            });
            Object.keys(fieldsMap).forEach(fieldName => {
                fields.push(fieldsMap[fieldName]);
            });
            return fields;
        }));
    }
    /**
     * Init record
     *
     * @param {object} record to use
     * @param {string} mode to use
     * @param {boolean} loadMetadata to use
     * @param initDefaultValues
     * @returns {object} Observable<any>
     */
    initRecord(record, mode = 'detail', loadMetadata = true, initDefaultValues = false) {
        super.initRecord(record, mode, loadMetadata);
        this.setRecord(record, initDefaultValues);
    }
    static { this.ɵfac = function RecordThreadItemStore_Factory(t) { return new (t || RecordThreadItemStore)(i0.ɵɵinject(i1.AppStateStore), i0.ɵɵinject(i2.MetadataStore), i0.ɵɵinject(i3.MessageService), i0.ɵɵinject(i4.FieldManager), i0.ɵɵinject(i5.LanguageStore), i0.ɵɵinject(i6.RecordStoreFactory)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: RecordThreadItemStore, factory: RecordThreadItemStore.ɵfac }); }
}
export { RecordThreadItemStore };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(RecordThreadItemStore, [{
        type: Injectable
    }], function () { return [{ type: i1.AppStateStore }, { type: i2.MetadataStore }, { type: i3.MessageService }, { type: i4.FieldManager }, { type: i5.LanguageStore }, { type: i6.RecordStoreFactory }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicmVjb3JkLXRocmVhZC1pdGVtLnN0b3JlLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vY29yZS9hcHAvY29yZS9zcmMvbGliL2NvbnRhaW5lcnMvcmVjb3JkLXRocmVhZC9zdG9yZS9yZWNvcmQtdGhyZWFkL3JlY29yZC10aHJlYWQtaXRlbS5zdG9yZS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0dBd0JHO0FBQ0gsT0FBTyxFQUFDLFVBQVUsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUd6QyxPQUFPLEVBQUMsR0FBRyxFQUFDLE1BQU0sZ0JBQWdCLENBQUM7QUFFbkMsT0FBTyxFQUFDLHdCQUF3QixFQUFDLE1BQU0sZ0VBQWdFLENBQUM7QUFDeEcsT0FBTyxFQUFDLGFBQWEsRUFBQyxNQUFNLDZDQUE2QyxDQUFDO0FBQzFFLE9BQU8sRUFBQyxhQUFhLEVBQUMsTUFBTSxtREFBbUQsQ0FBQztBQUNoRixPQUFPLEVBQUMsY0FBYyxFQUFDLE1BQU0sOENBQThDLENBQUM7QUFDNUUsT0FBTyxFQUFDLFlBQVksRUFBQyxNQUFNLGlEQUFpRCxDQUFDO0FBQzdFLE9BQU8sRUFBQyxhQUFhLEVBQUMsTUFBTSwyQ0FBMkMsQ0FBQztBQUN4RSxPQUFPLEVBQUMsa0JBQWtCLEVBQUMsTUFBTSwrQ0FBK0MsQ0FBQzs7Ozs7Ozs7QUFFakYsTUFDYSxxQkFBc0IsU0FBUSx3QkFBa0Q7SUFFekYsWUFDYyxhQUE0QixFQUM1QixJQUFtQixFQUNuQixPQUF1QixFQUN2QixZQUEwQixFQUMxQixRQUF1QixFQUN2QixZQUFnQztRQUcxQyxLQUFLLENBQ0QsYUFBYSxFQUNiLElBQUksRUFDSixPQUFPLEVBQ1AsWUFBWSxFQUNaLFFBQVEsRUFDUixZQUFZLENBQ2YsQ0FBQztRQWZRLGtCQUFhLEdBQWIsYUFBYSxDQUFlO1FBQzVCLFNBQUksR0FBSixJQUFJLENBQWU7UUFDbkIsWUFBTyxHQUFQLE9BQU8sQ0FBZ0I7UUFDdkIsaUJBQVksR0FBWixZQUFZLENBQWM7UUFDMUIsYUFBUSxHQUFSLFFBQVEsQ0FBZTtRQUN2QixpQkFBWSxHQUFaLFlBQVksQ0FBb0I7SUFXOUMsQ0FBQztJQUVEOzs7O09BSUc7SUFDSSxjQUFjO1FBQ2pCLE9BQU8sSUFBSSxDQUFDLEtBQUssQ0FBQyxJQUFJLENBQUMsR0FBRyxDQUFDLENBQUMsSUFBOEIsRUFBRSxFQUFFO1lBQzFELE1BQU0sU0FBUyxHQUEyQixFQUE0QixDQUFDO1lBQ3ZFLE1BQU0sTUFBTSxHQUEwQixFQUFFLENBQUM7WUFFekMsTUFBTSxnQkFBZ0IsR0FBRyxJQUFJLENBQUMsTUFBTSxJQUFJLEVBQTRCLENBQUE7WUFDcEUsTUFBTSxDQUFDLElBQUksQ0FBQyxnQkFBZ0IsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxTQUFTLENBQUMsRUFBRTtnQkFDOUMsSUFBSSxnQkFBZ0IsQ0FBQyxTQUFTLENBQUMsRUFBRTtvQkFDN0IsU0FBUyxDQUFDLFNBQVMsQ0FBQyxHQUFHLGdCQUFnQixDQUFDLFNBQVMsQ0FBQyxDQUFDO2lCQUN0RDtZQUNMLENBQUMsQ0FBQyxDQUFDO1lBRUgsSUFBSSxDQUFDLFlBQVksSUFBSSxJQUFJLENBQUMsWUFBWSxDQUFDLElBQUksSUFBSSxJQUFJLENBQUMsWUFBWSxDQUFDLElBQUksQ0FBQyxPQUFPLENBQUMsR0FBRyxDQUFDLEVBQUU7Z0JBQ2hGLEdBQUcsQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFFO29CQUNuQixJQUFJLEdBQUcsQ0FBQyxLQUFLLEVBQUU7d0JBQ1gsU0FBUyxDQUFDLEdBQUcsQ0FBQyxLQUFLLENBQUMsSUFBSSxDQUFDLEdBQUcsR0FBRyxDQUFDLEtBQUssQ0FBQztxQkFDekM7Z0JBQ0wsQ0FBQyxDQUFDLENBQUM7WUFDUCxDQUFDLENBQUMsQ0FBQztZQUVILElBQUksQ0FBQyxVQUFVLElBQUksSUFBSSxDQUFDLFVBQVUsQ0FBQyxJQUFJLElBQUksSUFBSSxDQUFDLFVBQVUsQ0FBQyxJQUFJLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyxFQUFFO2dCQUMxRSxHQUFHLENBQUMsSUFBSSxDQUFDLE9BQU8sQ0FBQyxHQUFHLENBQUMsRUFBRTtvQkFDbkIsSUFBSSxHQUFHLENBQUMsS0FBSyxFQUFFO3dCQUNYLFNBQVMsQ0FBQyxHQUFHLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxHQUFHLEdBQUcsQ0FBQyxLQUFLLENBQUM7cUJBQ3pDO2dCQUNMLENBQUMsQ0FBQyxDQUFDO1lBQ1AsQ0FBQyxDQUFDLENBQUM7WUFFSCxNQUFNLENBQUMsSUFBSSxDQUFDLFNBQVMsQ0FBQyxDQUFDLE9BQU8sQ0FBQyxTQUFTLENBQUMsRUFBRTtnQkFDdkMsTUFBTSxDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsU0FBUyxDQUFDLENBQUMsQ0FBQztZQUN0QyxDQUFDLENBQUMsQ0FBQTtZQUVGLE9BQU8sTUFBTSxDQUFDO1FBQ2xCLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFDUixDQUFDO0lBRUQ7Ozs7Ozs7O09BUUc7SUFDSSxVQUFVLENBQ2IsTUFBYyxFQUNkLE9BQWlCLFFBQW9CLEVBQ3JDLFlBQVksR0FBRyxJQUFJLEVBQ25CLGlCQUFpQixHQUFHLEtBQUs7UUFHekIsS0FBSyxDQUFDLFVBQVUsQ0FBQyxNQUFNLEVBQUUsSUFBSSxFQUFFLFlBQVksQ0FBQyxDQUFDO1FBQzdDLElBQUksQ0FBQyxTQUFTLENBQUMsTUFBTSxFQUFFLGlCQUFpQixDQUFDLENBQUM7SUFDOUMsQ0FBQztzRkFoRlEscUJBQXFCO3VFQUFyQixxQkFBcUIsV0FBckIscUJBQXFCOztTQUFyQixxQkFBcUI7dUZBQXJCLHFCQUFxQjtjQURqQyxVQUFVIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuaW1wb3J0IHtJbmplY3RhYmxlfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7T2JzZXJ2YWJsZX0gZnJvbSAncnhqcyc7XG5pbXBvcnQge1JlY29yZCwgVmlld0ZpZWxkRGVmaW5pdGlvbiwgVmlld0ZpZWxkRGVmaW5pdGlvbk1hcCwgVmlld01vZGV9IGZyb20gJ2NvbW1vbic7XG5pbXBvcnQge21hcH0gZnJvbSAncnhqcy9vcGVyYXRvcnMnO1xuaW1wb3J0IHtSZWNvcmRUaHJlYWRJdGVtTWV0YWRhdGF9IGZyb20gJy4vcmVjb3JkLXRocmVhZC1pdGVtLnN0b3JlLm1vZGVsJztcbmltcG9ydCB7QmFzZVJlY29yZENvbnRhaW5lclN0b3JlfSBmcm9tICcuLi8uLi8uLi8uLi9zdG9yZS9yZWNvcmQtY29udGFpbmVyL2Jhc2UtcmVjb3JkLWNvbnRhaW5lci5zdG9yZSc7XG5pbXBvcnQge0FwcFN0YXRlU3RvcmV9IGZyb20gJy4uLy4uLy4uLy4uL3N0b3JlL2FwcC1zdGF0ZS9hcHAtc3RhdGUuc3RvcmUnO1xuaW1wb3J0IHtNZXRhZGF0YVN0b3JlfSBmcm9tICcuLi8uLi8uLi8uLi9zdG9yZS9tZXRhZGF0YS9tZXRhZGF0YS5zdG9yZS5zZXJ2aWNlJztcbmltcG9ydCB7TWVzc2FnZVNlcnZpY2V9IGZyb20gJy4uLy4uLy4uLy4uL3NlcnZpY2VzL21lc3NhZ2UvbWVzc2FnZS5zZXJ2aWNlJztcbmltcG9ydCB7RmllbGRNYW5hZ2VyfSBmcm9tICcuLi8uLi8uLi8uLi9zZXJ2aWNlcy9yZWNvcmQvZmllbGQvZmllbGQubWFuYWdlcic7XG5pbXBvcnQge0xhbmd1YWdlU3RvcmV9IGZyb20gJy4uLy4uLy4uLy4uL3N0b3JlL2xhbmd1YWdlL2xhbmd1YWdlLnN0b3JlJztcbmltcG9ydCB7UmVjb3JkU3RvcmVGYWN0b3J5fSBmcm9tICcuLi8uLi8uLi8uLi9zdG9yZS9yZWNvcmQvcmVjb3JkLnN0b3JlLmZhY3RvcnknO1xuXG5ASW5qZWN0YWJsZSgpXG5leHBvcnQgY2xhc3MgUmVjb3JkVGhyZWFkSXRlbVN0b3JlIGV4dGVuZHMgQmFzZVJlY29yZENvbnRhaW5lclN0b3JlPFJlY29yZFRocmVhZEl0ZW1NZXRhZGF0YT4ge1xuXG4gICAgY29uc3RydWN0b3IoXG4gICAgICAgIHByb3RlY3RlZCBhcHBTdGF0ZVN0b3JlOiBBcHBTdGF0ZVN0b3JlLFxuICAgICAgICBwcm90ZWN0ZWQgbWV0YTogTWV0YWRhdGFTdG9yZSxcbiAgICAgICAgcHJvdGVjdGVkIG1lc3NhZ2U6IE1lc3NhZ2VTZXJ2aWNlLFxuICAgICAgICBwcm90ZWN0ZWQgZmllbGRNYW5hZ2VyOiBGaWVsZE1hbmFnZXIsXG4gICAgICAgIHByb3RlY3RlZCBsYW5ndWFnZTogTGFuZ3VhZ2VTdG9yZSxcbiAgICAgICAgcHJvdGVjdGVkIHN0b3JlRmFjdG9yeTogUmVjb3JkU3RvcmVGYWN0b3J5XG4gICAgKSB7XG5cbiAgICAgICAgc3VwZXIoXG4gICAgICAgICAgICBhcHBTdGF0ZVN0b3JlLFxuICAgICAgICAgICAgbWV0YSxcbiAgICAgICAgICAgIG1lc3NhZ2UsXG4gICAgICAgICAgICBmaWVsZE1hbmFnZXIsXG4gICAgICAgICAgICBsYW5ndWFnZSxcbiAgICAgICAgICAgIHN0b3JlRmFjdG9yeVxuICAgICAgICApO1xuICAgIH1cblxuICAgIC8qKlxuICAgICAqIEdldCB2aWV3IGZpZWxkcyBvYnNlcnZhYmxlXG4gICAgICpcbiAgICAgKiBAcmV0dXJucyB7b2JqZWN0fSBPYnNlcnZhYmxlPFZpZXdGaWVsZERlZmluaXRpb25bXT5cbiAgICAgKi9cbiAgICBwdWJsaWMgZ2V0Vmlld0ZpZWxkcyQoKTogT2JzZXJ2YWJsZTxWaWV3RmllbGREZWZpbml0aW9uW10+IHtcbiAgICAgICAgcmV0dXJuIHRoaXMubWV0YSQucGlwZShtYXAoKG1ldGE6IFJlY29yZFRocmVhZEl0ZW1NZXRhZGF0YSkgPT4ge1xuICAgICAgICAgICAgY29uc3QgZmllbGRzTWFwOiBWaWV3RmllbGREZWZpbml0aW9uTWFwID0ge30gYXMgVmlld0ZpZWxkRGVmaW5pdGlvbk1hcDtcbiAgICAgICAgICAgIGNvbnN0IGZpZWxkczogVmlld0ZpZWxkRGVmaW5pdGlvbltdID0gW107XG5cbiAgICAgICAgICAgIGNvbnN0IGZpZWxkRGVmaW5pdGlvbnMgPSBtZXRhLmZpZWxkcyA/PyB7fSBhcyBWaWV3RmllbGREZWZpbml0aW9uTWFwXG4gICAgICAgICAgICBPYmplY3Qua2V5cyhmaWVsZERlZmluaXRpb25zKS5mb3JFYWNoKGZpZWxkTmFtZSA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKGZpZWxkRGVmaW5pdGlvbnNbZmllbGROYW1lXSkge1xuICAgICAgICAgICAgICAgICAgICBmaWVsZHNNYXBbZmllbGROYW1lXSA9IGZpZWxkRGVmaW5pdGlvbnNbZmllbGROYW1lXTtcbiAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KTtcblxuICAgICAgICAgICAgbWV0YS5oZWFkZXJMYXlvdXQgJiYgbWV0YS5oZWFkZXJMYXlvdXQucm93cyAmJiBtZXRhLmhlYWRlckxheW91dC5yb3dzLmZvckVhY2gocm93ID0+IHtcbiAgICAgICAgICAgICAgICByb3cuY29scy5mb3JFYWNoKGNvbCA9PiB7XG4gICAgICAgICAgICAgICAgICAgIGlmIChjb2wuZmllbGQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGZpZWxkc01hcFtjb2wuZmllbGQubmFtZV0gPSBjb2wuZmllbGQ7XG4gICAgICAgICAgICAgICAgICAgIH1cbiAgICAgICAgICAgICAgICB9KTtcbiAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICBtZXRhLmJvZHlMYXlvdXQgJiYgbWV0YS5ib2R5TGF5b3V0LnJvd3MgJiYgbWV0YS5ib2R5TGF5b3V0LnJvd3MuZm9yRWFjaChyb3cgPT4ge1xuICAgICAgICAgICAgICAgIHJvdy5jb2xzLmZvckVhY2goY29sID0+IHtcbiAgICAgICAgICAgICAgICAgICAgaWYgKGNvbC5maWVsZCkge1xuICAgICAgICAgICAgICAgICAgICAgICAgZmllbGRzTWFwW2NvbC5maWVsZC5uYW1lXSA9IGNvbC5maWVsZDtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH0pO1xuICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgIE9iamVjdC5rZXlzKGZpZWxkc01hcCkuZm9yRWFjaChmaWVsZE5hbWUgPT4ge1xuICAgICAgICAgICAgICAgIGZpZWxkcy5wdXNoKGZpZWxkc01hcFtmaWVsZE5hbWVdKTtcbiAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgIHJldHVybiBmaWVsZHM7XG4gICAgICAgIH0pKTtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBJbml0IHJlY29yZFxuICAgICAqXG4gICAgICogQHBhcmFtIHtvYmplY3R9IHJlY29yZCB0byB1c2VcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gbW9kZSB0byB1c2VcbiAgICAgKiBAcGFyYW0ge2Jvb2xlYW59IGxvYWRNZXRhZGF0YSB0byB1c2VcbiAgICAgKiBAcGFyYW0gaW5pdERlZmF1bHRWYWx1ZXNcbiAgICAgKiBAcmV0dXJucyB7b2JqZWN0fSBPYnNlcnZhYmxlPGFueT5cbiAgICAgKi9cbiAgICBwdWJsaWMgaW5pdFJlY29yZChcbiAgICAgICAgcmVjb3JkOiBSZWNvcmQsXG4gICAgICAgIG1vZGU6IFZpZXdNb2RlID0gJ2RldGFpbCcgYXMgVmlld01vZGUsXG4gICAgICAgIGxvYWRNZXRhZGF0YSA9IHRydWUsXG4gICAgICAgIGluaXREZWZhdWx0VmFsdWVzID0gZmFsc2VcbiAgICApOiB2b2lkIHtcblxuICAgICAgICBzdXBlci5pbml0UmVjb3JkKHJlY29yZCwgbW9kZSwgbG9hZE1ldGFkYXRhKTtcbiAgICAgICAgdGhpcy5zZXRSZWNvcmQocmVjb3JkLCBpbml0RGVmYXVsdFZhbHVlcyk7XG4gICAgfVxuXG59XG4iXX0=