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
import { CUSTOM_ELEMENTS_SCHEMA, NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FieldComponent } from './field.component';
import { fieldModules } from './field.manifest';
import { DynamicFieldModule } from './dynamic-field/dynamic-field.module';
import { LineItemsModule } from './line-items/line-items.module';
import * as i0 from "@angular/core";
import * as i1 from "./varchar/templates/detail/varchar.module";
import * as i2 from "./varchar/templates/edit/varchar.module";
import * as i3 from "./varchar/templates/filter/filter.module";
import * as i4 from "./password/templates/detail/password.module";
import * as i5 from "./password/templates/edit/password.module";
import * as i6 from "./int/templates/detail/int.module";
import * as i7 from "./icon/templates/detail/icon.module";
import * as i8 from "./file/templates/detail/file.module";
import * as i9 from "./float/templates/detail/float.module";
import * as i10 from "./phone/templates/detail/phone.module";
import * as i11 from "./date/templates/detail/date.module";
import * as i12 from "./date/templates/edit/date.module";
import * as i13 from "./date/templates/filter/date.module";
import * as i14 from "./datetime/templates/detail/datetime.module";
import * as i15 from "./datetime/templates/edit/datetime.module";
import * as i16 from "./datetime/templates/filter/datetime.module";
import * as i17 from "./url/templates/detail/url.module";
import * as i18 from "./currency/templates/detail/currency.module";
import * as i19 from "./email/templates/list/email.module";
import * as i20 from "./text/templates/detail/text.module";
import * as i21 from "./text/templates/edit/text.module";
import * as i22 from "./text/templates/list/text.module";
import * as i23 from "./relate/templates/detail/relate.module";
import * as i24 from "./relate/templates/edit/relate.module";
import * as i25 from "./relate/templates/filter/relate.module";
import * as i26 from "./fullname/templates/detail/fullname.module";
import * as i27 from "./enum/templates/detail/enum.module";
import * as i28 from "./enum/templates/edit/enum.module";
import * as i29 from "./dropdownenum/templates/detail/dropdownenum.module";
import * as i30 from "./dropdownenum/templates/edit/dropdownenum.module";
import * as i31 from "./radioenum/templates/detail/radioenum.module";
import * as i32 from "./radioenum/templates/edit/radioenum.module";
import * as i33 from "./multienum/templates/detail/multienum.module";
import * as i34 from "./multienum/templates/edit/multienum.module";
import * as i35 from "./multienum/templates/filter/multienum.module";
import * as i36 from "./boolean/templates/detail/boolean.module";
import * as i37 from "./boolean/templates/edit/boolean.module";
import * as i38 from "./html/templates/detail/html.module";
import * as i39 from "./tinymce/templates/detail/tinymce.module";
import * as i40 from "./tinymce/templates/edit/tinymce.module";
import * as i41 from "./group-field/group-field.module";
import * as i42 from "./composite/composite.module";
class FieldModule {
    static { this.ɵfac = function FieldModule_Factory(t) { return new (t || FieldModule)(); }; }
    static { this.ɵmod = /*@__PURE__*/ i0.ɵɵdefineNgModule({ type: FieldModule }); }
    static { this.ɵinj = /*@__PURE__*/ i0.ɵɵdefineInjector({ imports: [fieldModules, CommonModule,
            DynamicFieldModule,
            LineItemsModule] }); }
}
export { FieldModule };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(FieldModule, [{
        type: NgModule,
        args: [{
                declarations: [
                    FieldComponent,
                ],
                exports: [
                    FieldComponent,
                ],
                imports: [
                    ...fieldModules,
                    CommonModule,
                    DynamicFieldModule,
                    LineItemsModule
                ],
                schemas: [CUSTOM_ELEMENTS_SCHEMA]
            }]
    }], null, null); })();
(function () { (typeof ngJitMode === "undefined" || ngJitMode) && i0.ɵɵsetNgModuleScope(FieldModule, { declarations: [FieldComponent], imports: [i1.VarcharDetailFieldModule, i2.VarcharEditFieldModule, i3.VarcharFilterFieldModule, i4.PasswordDetailFieldModule, i5.PasswordEditFieldModule, i6.IntDetailFieldModule, i7.IconListFieldModule, i8.FileDetailFieldModule, i9.FloatDetailFieldModule, i10.PhoneDetailFieldModule, i11.DateDetailFieldModule, i12.DateEditFieldModule, i13.DateFilterFieldModule, i14.DateTimeDetailFieldModule, i15.DateTimeEditFieldModule, i16.DateTimeFilterFieldModule, i17.UrlDetailFieldModule, i18.CurrencyDetailFieldModule, i19.EmailListFieldsModule, i20.TextDetailFieldModule, i21.TextEditFieldModule, i22.TextListFieldModule, i23.RelateDetailFieldsModule, i24.RelateEditFieldModule, i25.RelateFilterFieldModule, i26.FullNameDetailFieldsModule, i27.EnumDetailFieldModule, i28.EnumEditFieldModule, i29.DropdownEnumDetailFieldModule, i30.DropdownEnumEditFieldModule, i31.RadioEnumDetailFieldModule, i32.RadioEnumEditFieldModule, i33.MultiEnumDetailFieldModule, i34.MultiEnumEditFieldModule, i35.MultiEnumFilterFieldModule, i36.BooleanDetailFieldModule, i37.BooleanEditFieldModule, i38.HtmlDetailFieldModule, i39.TinymceDetailFieldModule, i40.TinymceEditFieldModule, i41.GroupFieldModule, i42.CompositeModule, CommonModule,
        DynamicFieldModule,
        LineItemsModule], exports: [FieldComponent] }); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZmllbGQubW9kdWxlLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vY29yZS9hcHAvY29yZS9zcmMvbGliL2ZpZWxkcy9maWVsZC5tb2R1bGUudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQXdCRztBQUVILE9BQU8sRUFBQyxzQkFBc0IsRUFBRSxRQUFRLEVBQUMsTUFBTSxlQUFlLENBQUM7QUFDL0QsT0FBTyxFQUFDLFlBQVksRUFBQyxNQUFNLGlCQUFpQixDQUFDO0FBQzdDLE9BQU8sRUFBQyxjQUFjLEVBQUMsTUFBTSxtQkFBbUIsQ0FBQztBQUNqRCxPQUFPLEVBQUMsWUFBWSxFQUFDLE1BQU0sa0JBQWtCLENBQUM7QUFDOUMsT0FBTyxFQUFDLGtCQUFrQixFQUFDLE1BQU0sc0NBQXNDLENBQUM7QUFDeEUsT0FBTyxFQUFDLGVBQWUsRUFBQyxNQUFNLGdDQUFnQyxDQUFDOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQUUvRCxNQWVhLFdBQVc7NEVBQVgsV0FBVzttRUFBWCxXQUFXO3VFQVBiLFlBQVksRUFDZixZQUFZO1lBQ1osa0JBQWtCO1lBQ2xCLGVBQWU7O1NBSVYsV0FBVzt1RkFBWCxXQUFXO2NBZnZCLFFBQVE7ZUFBQztnQkFDTixZQUFZLEVBQUU7b0JBQ1YsY0FBYztpQkFDakI7Z0JBQ0QsT0FBTyxFQUFFO29CQUNMLGNBQWM7aUJBQ2pCO2dCQUNELE9BQU8sRUFBRTtvQkFDTCxHQUFHLFlBQVk7b0JBQ2YsWUFBWTtvQkFDWixrQkFBa0I7b0JBQ2xCLGVBQWU7aUJBQ2xCO2dCQUNELE9BQU8sRUFBRSxDQUFDLHNCQUFzQixDQUFDO2FBQ3BDOzt3RkFDWSxXQUFXLG1CQWJoQixjQUFjLDZxQ0FPZCxZQUFZO1FBQ1osa0JBQWtCO1FBQ2xCLGVBQWUsYUFOZixjQUFjIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuXG5pbXBvcnQge0NVU1RPTV9FTEVNRU5UU19TQ0hFTUEsIE5nTW9kdWxlfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7Q29tbW9uTW9kdWxlfSBmcm9tICdAYW5ndWxhci9jb21tb24nO1xuaW1wb3J0IHtGaWVsZENvbXBvbmVudH0gZnJvbSAnLi9maWVsZC5jb21wb25lbnQnO1xuaW1wb3J0IHtmaWVsZE1vZHVsZXN9IGZyb20gJy4vZmllbGQubWFuaWZlc3QnO1xuaW1wb3J0IHtEeW5hbWljRmllbGRNb2R1bGV9IGZyb20gJy4vZHluYW1pYy1maWVsZC9keW5hbWljLWZpZWxkLm1vZHVsZSc7XG5pbXBvcnQge0xpbmVJdGVtc01vZHVsZX0gZnJvbSAnLi9saW5lLWl0ZW1zL2xpbmUtaXRlbXMubW9kdWxlJztcblxuQE5nTW9kdWxlKHtcbiAgICBkZWNsYXJhdGlvbnM6IFtcbiAgICAgICAgRmllbGRDb21wb25lbnQsXG4gICAgXSxcbiAgICBleHBvcnRzOiBbXG4gICAgICAgIEZpZWxkQ29tcG9uZW50LFxuICAgIF0sXG4gICAgaW1wb3J0czogW1xuICAgICAgICAuLi5maWVsZE1vZHVsZXMsXG4gICAgICAgIENvbW1vbk1vZHVsZSxcbiAgICAgICAgRHluYW1pY0ZpZWxkTW9kdWxlLFxuICAgICAgICBMaW5lSXRlbXNNb2R1bGVcbiAgICBdLFxuICAgIHNjaGVtYXM6IFtDVVNUT01fRUxFTUVOVFNfU0NIRU1BXVxufSlcbmV4cG9ydCBjbGFzcyBGaWVsZE1vZHVsZSB7XG59XG4iXX0=