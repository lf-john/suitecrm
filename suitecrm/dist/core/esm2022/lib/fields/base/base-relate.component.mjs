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
import { Component } from '@angular/core';
import { of } from 'rxjs';
import { catchError, map, tap } from 'rxjs/operators';
import { ModuleNameMapper } from '../../services/navigation/module-name-mapper/module-name-mapper.service';
import { BaseFieldComponent } from './base-field.component';
import { DataTypeFormatter } from '../../services/formatters/data-type.formatter.service';
import { LanguageStore } from '../../store/language/language.store';
import { RelateService } from '../../services/record/relate/relate.service';
import { FieldLogicManager } from '../field-logic/field-logic.manager';
import { FieldLogicDisplayManager } from '../field-logic-display/field-logic-display.manager';
import * as i0 from "@angular/core";
import * as i1 from "../../store/language/language.store";
import * as i2 from "../../services/formatters/data-type.formatter.service";
import * as i3 from "../../services/record/relate/relate.service";
import * as i4 from "../../services/navigation/module-name-mapper/module-name-mapper.service";
import * as i5 from "../field-logic/field-logic.manager";
import * as i6 from "../field-logic-display/field-logic-display.manager";
class BaseRelateComponent extends BaseFieldComponent {
    constructor(languages, typeFormatter, relateService, moduleNameMapper, logic, logicDisplay) {
        super(typeFormatter, logic, logicDisplay);
        this.languages = languages;
        this.typeFormatter = typeFormatter;
        this.relateService = relateService;
        this.moduleNameMapper = moduleNameMapper;
        this.logic = logic;
        this.logicDisplay = logicDisplay;
        this.selectedValues = [];
        this.options = [];
        this.status = '';
        this.initModule = '';
        this.search = (text) => {
            if (text === '') {
                return of([]);
            }
            this.status = 'searching';
            return this.relateService.search(text, this.getRelateFieldName()).pipe(tap(() => this.status = 'found'), catchError(() => {
                this.status = 'error';
                return of([]);
            }), map(records => {
                if (!records || records.length < 1) {
                    this.status = 'not-found';
                    return [];
                }
                const flatRecords = [];
                records.forEach((record) => {
                    if (record && record.attributes) {
                        flatRecords.push(record.attributes);
                    }
                });
                this.status = '';
                return flatRecords;
            }));
        };
    }
    get module() {
        if (!this.record || !this.record.module) {
            return null;
        }
        return this.record.module;
    }
    ngOnInit() {
        super.ngOnInit();
        this.init();
        this.subs.push(this.field.valueChanges$.subscribe(() => {
            this.onModuleChange();
        }));
    }
    ngOnDestroy() {
        this.subs.forEach(sub => sub.unsubscribe());
    }
    onModuleChange() {
        const currentModule = this.initModule;
        const newModule = this?.field?.definition?.module ?? '';
        if (currentModule === newModule) {
            return;
        }
        this.initModule = newModule;
        if (currentModule === '' && currentModule !== newModule) {
            this.init();
        }
        if (newModule === '') {
            this.status = 'no-module';
        }
        else {
            this.init();
            this.status = '';
            this.selectedValues = [];
            this.options = [];
        }
    }
    getRelateFieldName() {
        if (!this.field?.definition?.metadata?.relateSearchField) {
            return (this.field && this.field.definition && this.field.definition.rname) || 'name';
        }
        return this.field.definition.metadata.relateSearchField;
    }
    getRelateIdField() {
        return (this.field && this.field.definition && this.field.definition.id_name) || '';
    }
    getRelatedModule() {
        const legacyName = (this.field && this.field.definition && this.field.definition.module) || '';
        if (!legacyName) {
            return '';
        }
        return this.moduleNameMapper.toFrontend(legacyName);
    }
    getMessage() {
        const messages = {
            searching: 'LBL_SEARCHING',
            'not-found': 'LBL_NOT_FOUND',
            error: 'LBL_SEARCH_ERROR',
            found: 'LBL_FOUND',
            'no-module': 'LBL_NO_MODULE_SELECTED'
        };
        if (messages[this.status]) {
            return messages[this.status];
        }
        return '';
    }
    getInvalidClass() {
        if (this.field.formControl && this.field.formControl.invalid && this.field.formControl.touched) {
            return 'is-invalid';
        }
        if (this.hasSearchError()) {
            return 'is-invalid';
        }
        return '';
    }
    hasSearchError() {
        return this.status === 'error' || this.status === 'not-found';
    }
    resetStatus() {
        this.status = '';
    }
    getPlaceholderLabel() {
        return this.languages.getAppString('LBL_TYPE_TO_SEARCH') || '';
    }
    init() {
        this.initModule = this?.field?.definition?.module ?? '';
        if (this.relateService) {
            this.relateService.init(this.getRelatedModule());
        }
    }
    buildRelate(id, relateValue) {
        const relate = { id };
        if (this.getRelateFieldName()) {
            relate[this.getRelateFieldName()] = relateValue;
        }
        return relate;
    }
    static { this.ɵfac = function BaseRelateComponent_Factory(t) { return new (t || BaseRelateComponent)(i0.ɵɵdirectiveInject(i1.LanguageStore), i0.ɵɵdirectiveInject(i2.DataTypeFormatter), i0.ɵɵdirectiveInject(i3.RelateService), i0.ɵɵdirectiveInject(i4.ModuleNameMapper), i0.ɵɵdirectiveInject(i5.FieldLogicManager), i0.ɵɵdirectiveInject(i6.FieldLogicDisplayManager)); }; }
    static { this.ɵcmp = /*@__PURE__*/ i0.ɵɵdefineComponent({ type: BaseRelateComponent, selectors: [["ng-component"]], features: [i0.ɵɵInheritDefinitionFeature], decls: 0, vars: 0, template: function BaseRelateComponent_Template(rf, ctx) { }, encapsulation: 2 }); }
}
export { BaseRelateComponent };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(BaseRelateComponent, [{
        type: Component,
        args: [{ template: '' }]
    }], function () { return [{ type: i1.LanguageStore }, { type: i2.DataTypeFormatter }, { type: i3.RelateService }, { type: i4.ModuleNameMapper }, { type: i5.FieldLogicManager }, { type: i6.FieldLogicDisplayManager }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiYmFzZS1yZWxhdGUuY29tcG9uZW50LmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vY29yZS9hcHAvY29yZS9zcmMvbGliL2ZpZWxkcy9iYXNlL2Jhc2UtcmVsYXRlLmNvbXBvbmVudC50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0dBd0JHO0FBRUgsT0FBTyxFQUFDLFNBQVMsRUFBb0IsTUFBTSxlQUFlLENBQUM7QUFDM0QsT0FBTyxFQUFhLEVBQUUsRUFBQyxNQUFNLE1BQU0sQ0FBQztBQUNwQyxPQUFPLEVBQUMsVUFBVSxFQUFFLEdBQUcsRUFBRSxHQUFHLEVBQUMsTUFBTSxnQkFBZ0IsQ0FBQztBQUVwRCxPQUFPLEVBQUMsZ0JBQWdCLEVBQUMsTUFBTSx5RUFBeUUsQ0FBQztBQUN6RyxPQUFPLEVBQUMsa0JBQWtCLEVBQUMsTUFBTSx3QkFBd0IsQ0FBQztBQUMxRCxPQUFPLEVBQUMsaUJBQWlCLEVBQUMsTUFBTSx1REFBdUQsQ0FBQztBQUN4RixPQUFPLEVBQUMsYUFBYSxFQUFDLE1BQU0scUNBQXFDLENBQUM7QUFDbEUsT0FBTyxFQUFDLGFBQWEsRUFBQyxNQUFNLDZDQUE2QyxDQUFDO0FBQzFFLE9BQU8sRUFBQyxpQkFBaUIsRUFBQyxNQUFNLG9DQUFvQyxDQUFDO0FBQ3JFLE9BQU8sRUFBQyx3QkFBd0IsRUFBQyxNQUFNLG9EQUFvRCxDQUFDOzs7Ozs7OztBQUU1RixNQUNhLG1CQUFvQixTQUFRLGtCQUFrQjtJQU92RCxZQUNjLFNBQXdCLEVBQ3hCLGFBQWdDLEVBQ2hDLGFBQTRCLEVBQzVCLGdCQUFrQyxFQUNsQyxLQUF3QixFQUN4QixZQUFzQztRQUVoRCxLQUFLLENBQUMsYUFBYSxFQUFFLEtBQUssRUFBRSxZQUFZLENBQUMsQ0FBQztRQVBoQyxjQUFTLEdBQVQsU0FBUyxDQUFlO1FBQ3hCLGtCQUFhLEdBQWIsYUFBYSxDQUFtQjtRQUNoQyxrQkFBYSxH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QUUsTUFBTSxJQUFJLEVBQUUsQ0FBQztRQUV4RCxJQUFJLElBQUksQ0FBQyxhQUFhLEVBQUU7WUFDcEIsSUFBSSxDQUFDLGFBQWEsQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGdCQUFnQixFQUFFLENBQUMsQ0FBQztTQUNwRDtJQUNMLENBQUM7SUFFUyxXQUFXLENBQUMsRUFBVSxFQUFFLFdBQW1CO1FBQ2pELE1BQU0sTUFBTSxHQUFHLEVBQUMsRUFBRSxFQUFDLENBQUM7UUFFcEIsSUFBSSxJQUFJLENBQUMsa0JBQWtCLEVBQUUsRUFBRTtZQUMzQixNQUFNLENBQUMsSUFBSSxDQUFDLGtCQUFrQixFQUFFLENBQUMsR0FBRyxXQUFXLENBQUM7U0FDbkQ7UUFFRCxPQUFPLE1BQU0sQ0FBQztJQUNsQixDQUFDO29GQXBMUSxtQkFBbUI7b0VBQW5CLG1CQUFtQjs7U0FBbkIsbUJBQW1CO3VGQUFuQixtQkFBbUI7Y0FEL0IsU0FBUztlQUFDLEVBQUMsUUFBUSxFQUFFLEVBQUUsRUFBQyIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogU3VpdGVDUk0gaXMgYSBjdXN0b21lciByZWxhdGlvbnNoaXAgbWFuYWdlbWVudCBwcm9ncmFtIGRldmVsb3BlZCBieSBTYWxlc0FnaWxpdHkgTHRkLlxuICogQ29weXJpZ2h0IChDKSAyMDIxIFNhbGVzQWdpbGl0eSBMdGQuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGZyZWUgc29mdHdhcmU7IHlvdSBjYW4gcmVkaXN0cmlidXRlIGl0IGFuZC9vciBtb2RpZnkgaXQgdW5kZXJcbiAqIHRoZSB0ZXJtcyBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIHZlcnNpb24gMyBhcyBwdWJsaXNoZWQgYnkgdGhlXG4gKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4gKiB0byBTZWN0aW9uIDE1IGFzIHBlcm1pdHRlZCBpbiBTZWN0aW9uIDcoYSk6IEZPUiBBTlkgUEFSVCBPRiBUSEUgQ09WRVJFRCBXT1JLXG4gKiBJTiBXSElDSCBUSEUgQ09QWVJJR0hUIElTIE9XTkVEIEJZIFNBTEVTQUdJTElUWSwgU0FMRVNBR0lMSVRZIERJU0NMQUlNUyBUSEVcbiAqIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBkaXN0cmlidXRlZCBpbiB0aGUgaG9wZSB0aGF0IGl0IHdpbGwgYmUgdXNlZnVsLCBidXQgV0lUSE9VVFxuICogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiAqIEZPUiBBIFBBUlRJQ1VMQVIgUFVSUE9TRS4gU2VlIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgZm9yIG1vcmVcbiAqIGRldGFpbHMuXG4gKlxuICogWW91IHNob3VsZCBoYXZlIHJlY2VpdmVkIGEgY29weSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiBhbG9uZyB3aXRoIHRoaXMgcHJvZ3JhbS4gIElmIG5vdCwgc2VlIDxodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvPi5cbiAqXG4gKiBJbiBhY2NvcmRhbmNlIHdpdGggU2VjdGlvbiA3KGIpIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIHZlcnNpb24gMywgdGhlc2UgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IHJldGFpbiB0aGUgZGlzcGxheSBvZiB0aGVcbiAqIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4gKiBmZWFzaWJsZSBmb3IgdGVjaG5pY2FsIHJlYXNvbnMsIHRoZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgZGlzcGxheVxuICogdGhlIHdvcmRzIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIuXG4gKi9cblxuaW1wb3J0IHtDb21wb25lbnQsIE9uRGVzdHJveSwgT25Jbml0fSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7T2JzZXJ2YWJsZSwgb2Z9IGZyb20gJ3J4anMnO1xuaW1wb3J0IHtjYXRjaEVycm9yLCBtYXAsIHRhcH0gZnJvbSAncnhqcy9vcGVyYXRvcnMnO1xuaW1wb3J0IHtBdHRyaWJ1dGVNYXAsIFJlY29yZH0gZnJvbSAnY29tbW9uJztcbmltcG9ydCB7TW9kdWxlTmFtZU1hcHBlcn0gZnJvbSAnLi4vLi4vc2VydmljZXMvbmF2aWdhdGlvbi9tb2R1bGUtbmFtZS1tYXBwZXIvbW9kdWxlLW5hbWUtbWFwcGVyLnNlcnZpY2UnO1xuaW1wb3J0IHtCYXNlRmllbGRDb21wb25lbnR9IGZyb20gJy4vYmFzZS1maWVsZC5jb21wb25lbnQnO1xuaW1wb3J0IHtEYXRhVHlwZUZvcm1hdHRlcn0gZnJvbSAnLi4vLi4vc2VydmljZXMvZm9ybWF0dGVycy9kYXRhLXR5cGUuZm9ybWF0dGVyLnNlcnZpY2UnO1xuaW1wb3J0IHtMYW5ndWFnZVN0b3JlfSBmcm9tICcuLi8uLi9zdG9yZS9sYW5ndWFnZS9sYW5ndWFnZS5zdG9yZSc7XG5pbXBvcnQge1JlbGF0ZVNlcnZpY2V9IGZyb20gJy4uLy4uL3NlcnZpY2VzL3JlY29yZC9yZWxhdGUvcmVsYXRlLnNlcnZpY2UnO1xuaW1wb3J0IHtGaWVsZExvZ2ljTWFuYWdlcn0gZnJvbSAnLi4vZmllbGQtbG9naWMvZmllbGQtbG9naWMubWFuYWdlcic7XG5pbXBvcnQge0ZpZWxkTG9naWNEaXNwbGF5TWFuYWdlcn0gZnJvbSAnLi4vZmllbGQtbG9naWMtZGlzcGxheS9maWVsZC1sb2dpYy1kaXNwbGF5Lm1hbmFnZXInO1xuXG5AQ29tcG9uZW50KHt0ZW1wbGF0ZTogJyd9KVxuZXhwb3J0IGNsYXNzIEJhc2VSZWxhdGVDb21wb25lbnQgZXh0ZW5kcyBCYXNlRmllbGRDb21wb25lbnQgaW1wbGVtZW50cyBPbkluaXQsIE9uRGVzdHJveSB7XG4gICAgc2VsZWN0ZWRWYWx1ZXM6IEF0dHJpYnV0ZU1hcFtdID0gW107XG4gICAgb3B0aW9uczogQXR0cmlidXRlTWFwW10gPSBbXTtcblxuICAgIHN0YXR1czogJycgfCAnc2VhcmNoaW5nJyB8ICdub3QtZm91bmQnIHwgJ2Vycm9yJyB8ICdmb3VuZCcgfCAnbm8tbW9kdWxlJyA9ICcnO1xuICAgIGluaXRNb2R1bGUgPSAnJztcblxuICAgIGNvbnN0cnVjdG9yKFxuICAgICAgICBwcm90ZWN0ZWQgbGFuZ3VhZ2VzOiBMYW5ndWFnZVN0b3JlLFxuICAgICAgICBwcm90ZWN0ZWQgdHlwZUZvcm1hdHRlcjogRGF0YVR5cGVGb3JtYXR0ZXIsXG4gICAgICAgIHByb3RlY3RlZCByZWxhdGVTZXJ2aWNlOiBSZWxhdGVTZXJ2aWNlLFxuICAgICAgICBwcm90ZWN0ZWQgbW9kdWxlTmFtZU1hcHBlcjogTW9kdWxlTmFtZU1hcHBlcixcbiAgICAgICAgcHJvdGVjdGVkIGxvZ2ljOiBGaWVsZExvZ2ljTWFuYWdlcixcbiAgICAgICAgcHJvdGVjdGVkIGxvZ2ljRGlzcGxheTogRmllbGRMb2dpY0Rpc3BsYXlNYW5hZ2VyXG4gICAgKSB7XG4gICAgICAgIHN1cGVyKHR5cGVGb3JtYXR0ZXIsIGxvZ2ljLCBsb2dpY0Rpc3BsYXkpO1xuICAgIH1cblxuICAgIGdldCBtb2R1bGUoKTogc3RyaW5nIHtcbiAgICAgICAgaWYgKCF0aGlzLnJlY29yZCB8fCAhdGhpcy5yZWNvcmQubW9kdWxlKSB7XG4gICAgICAgICAgICByZXR1cm4gbnVsbDtcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiB0aGlzLnJlY29yZC5tb2R1bGU7XG4gICAgfVxuXG4gICAgbmdPbkluaXQoKTogdm9pZCB7XG5cbiAgICAgICAgc3VwZXIubmdPbkluaXQoKTtcblxuICAgICAgICB0aGlzLmluaXQoKTtcblxuICAgICAgICB0aGlzLnN1YnMucHVzaCh0aGlzLmZpZWxkLnZhbHVlQ2hhbmdlcyQuc3Vic2NyaWJlKCgpID0+IHtcbiAgICAgICAgICAgIHRoaXMub25Nb2R1bGVDaGFuZ2UoKTtcbiAgICAgICAgfSkpO1xuICAgIH1cblxuXG4gICAgbmdPbkRlc3Ryb3koKTogdm9pZCB7XG4gICAgICAgIHRoaXMuc3Vicy5mb3JFYWNoKHN1YiA9PiBzdWIudW5zdWJzY3JpYmUoKSk7XG4gICAgfVxuXG4gICAgb25Nb2R1bGVDaGFuZ2UoKTogdm9pZCB7XG4gICAgICAgIGNvbnN0IGN1cnJlbnRNb2R1bGUgPSB0aGlzLmluaXRNb2R1bGU7XG4gICAgICAgIGNvbnN0IG5ld01vZHVsZSA9IHRoaXM/LmZpZWxkPy5kZWZpbml0aW9uPy5tb2R1bGUgPz8gJyc7XG5cbiAgICAgICAgaWYgKGN1cnJlbnRNb2R1bGUgPT09IG5ld01vZHVsZSkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgdGhpcy5pbml0TW9kdWxlID0gbmV3TW9kdWxlO1xuXG4gICAgICAgIGlmIChjdXJyZW50TW9kdWxlID09PSAnJyAmJiBjdXJyZW50TW9kdWxlICE9PSBuZXdNb2R1bGUpIHtcbiAgICAgICAgICAgIHRoaXMuaW5pdCgpO1xuICAgICAgICB9XG5cbiAgICAgICAgaWYgKG5ld01vZHVsZSA9PT0gJycpIHtcbiAgICAgICAgICAgIHRoaXMuc3RhdHVzID0gJ25vLW1vZHVsZSc7XG4gICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICB0aGlzLmluaXQoKTtcbiAgICAgICAgICAgIHRoaXMuc3RhdHVzID0gJyc7XG4gICAgICAgICAgICB0aGlzLnNlbGVjdGVkVmFsdWVzID0gW107XG4gICAgICAgICAgICB0aGlzLm9wdGlvbnMgPSBbXTtcblxuICAgICAgICB9XG4gICAgfVxuXG4gICAgc2VhcmNoID0gKHRleHQ6IHN0cmluZyk6IE9ic2VydmFibGU8YW55PiA9PiB7XG5cbiAgICAgICAgaWYodGV4dCA9PT0gJycpIHtcbiAgICAgICAgICAgIHJldHVybiBvZihbXSk7XG4gICAgICAgIH1cblxuICAgICAgICB0aGlzLnN0YXR1cyA9ICdzZWFyY2hpbmcnO1xuXG4gICAgICAgIHJldHVybiB0aGlzLnJlbGF0ZVNlcnZpY2Uuc2VhcmNoKHRleHQsIHRoaXMuZ2V0UmVsYXRlRmllbGROYW1lKCkpLnBpcGUoXG4gICAgICAgICAgICB0YXAoKCkgPT4gdGhpcy5zdGF0dXMgPSAnZm91bmQnKSxcbiAgICAgICAgICAgIGNhdGNoRXJyb3IoKCkgPT4ge1xuICAgICAgICAgICAgICAgIHRoaXMuc3RhdHVzID0gJ2Vycm9yJztcbiAgICAgICAgICAgICAgICByZXR1cm4gb2YoW10pO1xuICAgICAgICAgICAgfSksXG4gICAgICAgICAgICBtYXAocmVjb3JkcyA9PiB7XG4gICAgICAgICAgICAgICAgaWYgKCFyZWNvcmRzIHx8IHJlY29yZHMubGVuZ3RoIDwgMSkge1xuICAgICAgICAgICAgICAgICAgICB0aGlzLnN0YXR1cyA9ICdub3QtZm91bmQnO1xuICAgICAgICAgICAgICAgICAgICByZXR1cm4gW107XG4gICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgY29uc3QgZmxhdFJlY29yZHM6IEF0dHJpYnV0ZU1hcFtdID0gW107XG5cbiAgICAgICAgICAgICAgICByZWNvcmRzLmZvckVhY2goKHJlY29yZDogUmVjb3JkKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIGlmIChyZWNvcmQgJiYgcmVjb3JkLmF0dHJpYnV0ZXMpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGZsYXRSZWNvcmRzLnB1c2gocmVjb3JkLmF0dHJpYnV0ZXMpO1xuICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgfSk7XG5cbiAgICAgICAgICAgICAgICB0aGlzLnN0YXR1cyA9ICcnO1xuXG4gICAgICAgICAgICAgICAgcmV0dXJuIGZsYXRSZWNvcmRzO1xuICAgICAgICAgICAgfSksXG4gICAgICAgICk7XG4gICAgfTtcblxuICAgIGdldFJlbGF0ZUZpZWxkTmFtZSgpOiBzdHJpbmcge1xuICAgICAgICBpZiAoIXRoaXMuZmllbGQ/LmRlZmluaXRpb24/Lm1ldGFkYXRhPy5yZWxhdGVTZWFyY2hGaWVsZCkge1xuICAgICAgICAgICAgcmV0dXJuICh0aGlzLmZpZWxkICYmIHRoaXMuZmllbGQuZGVmaW5pdGlvbiAmJiB0aGlzLmZpZWxkLmRlZmluaXRpb24ucm5hbWUpIHx8ICduYW1lJztcbiAgICAgICAgfVxuXG4gICAgICAgIHJldHVybiB0aGlzLmZpZWxkLmRlZmluaXRpb24ubWV0YWRhdGEucmVsYXRlU2VhcmNoRmllbGQ7XG4gICAgfVxuXG4gICAgZ2V0UmVsYXRlSWRGaWVsZCgpOiBzdHJpbmcge1xuICAgICAgICByZXR1cm4gKHRoaXMuZmllbGQgJiYgdGhpcy5maWVsZC5kZWZpbml0aW9uICYmIHRoaXMuZmllbGQuZGVmaW5pdGlvbi5pZF9uYW1lKSB8fCAnJztcbiAgICB9XG5cbiAgICBnZXRSZWxhdGVkTW9kdWxlKCk6IHN0cmluZyB7XG4gICAgICAgIGNvbnN0IGxlZ2FjeU5hbWUgPSAodGhpcy5maWVsZCAmJiB0aGlzLmZpZWxkLmRlZmluaXRpb24gJiYgdGhpcy5maWVsZC5kZWZpbml0aW9uLm1vZHVsZSkgfHwgJyc7XG4gICAgICAgIGlmICghbGVnYWN5TmFtZSkge1xuICAgICAgICAgICAgcmV0dXJuICcnO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHRoaXMubW9kdWxlTmFtZU1hcHBlci50b0Zyb250ZW5kKGxlZ2FjeU5hbWUpO1xuICAgIH1cblxuICAgIGdldE1lc3NhZ2UoKTogc3RyaW5nIHtcbiAgICAgICAgY29uc3QgbWVzc2FnZXMgPSB7XG4gICAgICAgICAgICBzZWFyY2hpbmc6ICdMQkxfU0VBUkNISU5HJyxcbiAgICAgICAgICAgICdub3QtZm91bmQnOiAnTEJMX05PVF9GT1VORCcsXG4gICAgICAgICAgICBlcnJvcjogJ0xCTF9TRUFSQ0hfRVJST1InLFxuICAgICAgICAgICAgZm91bmQ6ICdMQkxfRk9VTkQnLFxuICAgICAgICAgICAgJ25vLW1vZHVsZSc6ICdMQkxfTk9fTU9EVUxFX1NFTEVDVEVEJ1xuICAgICAgICB9O1xuXG4gICAgICAgIGlmIChtZXNzYWdlc1t0aGlzLnN0YXR1c10pIHtcbiAgICAgICAgICAgIHJldHVybiBtZXNzYWdlc1t0aGlzLnN0YXR1c107XG4gICAgICAgIH1cblxuICAgICAgICByZXR1cm4gJyc7XG4gICAgfVxuXG4gICAgZ2V0SW52YWxpZENsYXNzKCk6IHN0cmluZyB7XG4gICAgICAgIGlmICh0aGlzLmZpZWxkLmZvcm1Db250cm9sICYmIHRoaXMuZmllbGQuZm9ybUNvbnRyb2wuaW52YWxpZCAmJiB0aGlzLmZpZWxkLmZvcm1Db250cm9sLnRvdWNoZWQpIHtcbiAgICAgICAgICAgIHJldHVybiAnaXMtaW52YWxpZCc7XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodGhpcy5oYXNTZWFyY2hFcnJvcigpKSB7XG4gICAgICAgICAgICByZXR1cm4gJ2lzLWludmFsaWQnO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuICcnO1xuICAgIH1cblxuICAgIGhhc1NlYXJjaEVycm9yKCk6IGJvb2xlYW4ge1xuICAgICAgICByZXR1cm4gdGhpcy5zdGF0dXMgPT09ICdlcnJvcicgfHwgdGhpcy5zdGF0dXMgPT09ICdub3QtZm91bmQnO1xuICAgIH1cblxuICAgIHJlc2V0U3RhdHVzKCk6IHZvaWQge1xuICAgICAgICB0aGlzLnN0YXR1cyA9ICcnO1xuICAgIH1cblxuICAgIGdldFBsYWNlaG9sZGVyTGFiZWwoKTogc3RyaW5nIHtcbiAgICAgICAgcmV0dXJuIHRoaXMubGFuZ3VhZ2VzLmdldEFwcFN0cmluZygnTEJMX1RZUEVfVE9fU0VBUkNIJykgfHwgJyc7XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGluaXQoKTogdm9pZCB7XG5cbiAgICAgICAgdGhpcy5pbml0TW9kdWxlID0gdGhpcz8uZmllbGQ/LmRlZmluaXRpb24/Lm1vZHVsZSA/PyAnJztcblxuICAgICAgICBpZiAodGhpcy5yZWxhdGVTZXJ2aWNlKSB7XG4gICAgICAgICAgICB0aGlzLnJlbGF0ZVNlcnZpY2UuaW5pdCh0aGlzLmdldFJlbGF0ZWRNb2R1bGUoKSk7XG4gICAgICAgIH1cbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgYnVpbGRSZWxhdGUoaWQ6IHN0cmluZywgcmVsYXRlVmFsdWU6IHN0cmluZyk6IGFueSB7XG4gICAgICAgIGNvbnN0IHJlbGF0ZSA9IHtpZH07XG5cbiAgICAgICAgaWYgKHRoaXMuZ2V0UmVsYXRlRmllbGROYW1lKCkpIHtcbiAgICAgICAgICAgIHJlbGF0ZVt0aGlzLmdldFJlbGF0ZUZpZWxkTmFtZSgpXSA9IHJlbGF0ZVZhbHVlO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHJlbGF0ZTtcbiAgICB9XG5cbn1cbiJdfQ==