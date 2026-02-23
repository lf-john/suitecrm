/**
 * SuiteCRM is a customer relationship management program developed by SalesAgility Ltd.
 * Copyright (C) 2024 SalesAgility Ltd.
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
import { DateFieldHandler } from "./handlers/date.field-handler";
import { MultiEnumFieldHandler } from "./handlers/multienum.field-handler";
import { DefaultFieldHandler } from "./handlers/default.field-handler";
import { BaseServiceRegistry } from "common";
import * as i0 from "@angular/core";
import * as i1 from "./handlers/default.field-handler";
import * as i2 from "./handlers/date.field-handler";
import * as i3 from "./handlers/multienum.field-handler";
class FieldHandlerRegistry extends BaseServiceRegistry {
    constructor(defaultFieldHandler, dateFieldHandler, multienumFieldHandler) {
        super();
        this.defaultFieldHandler = defaultFieldHandler;
        this.dateFieldHandler = dateFieldHandler;
        this.multienumFieldHandler = multienumFieldHandler;
        this.defaultMap = {};
        this.defaultMap = {
            'default': defaultFieldHandler,
            'date': dateFieldHandler,
            'multienum': multienumFieldHandler
        };
        this.initDefault();
    }
    initDefault() {
        Object.keys(this.getDefaultMap()).forEach(type => {
            this.register('default', type, this.getDefaultMap()[type]);
        });
    }
    getDefaultMap() {
        return this.defaultMap ?? {};
    }
    static { this.ɵfac = function FieldHandlerRegistry_Factory(t) { return new (t || FieldHandlerRegistry)(i0.ɵɵinject(i1.DefaultFieldHandler), i0.ɵɵinject(i2.DateFieldHandler), i0.ɵɵinject(i3.MultiEnumFieldHandler)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: FieldHandlerRegistry, factory: FieldHandlerRegistry.ɵfac, providedIn: 'root' }); }
}
export { FieldHandlerRegistry };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(FieldHandlerRegistry, [{
        type: Injectable,
        args: [{
                providedIn: 'root'
            }]
    }], function () { return [{ type: i1.DefaultFieldHandler }, { type: i2.DateFieldHandler }, { type: i3.MultiEnumFieldHandler }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZmllbGQtaGFuZGxlci5yZWdpc3RyeS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uLy4uLy4uLy4uLy4uL2NvcmUvYXBwL2NvcmUvc3JjL2xpYi9zZXJ2aWNlcy9yZWNvcmQvZmllbGQvaGFuZGxlci9maWVsZC1oYW5kbGVyLnJlZ2lzdHJ5LnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBOzs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7R0F3Qkc7QUFFSCxPQUFPLEVBQUMsVUFBVSxFQUFDLE1BQU0sZUFBZSxDQUFDO0FBRXpDLE9BQU8sRUFBQyxnQkFBZ0IsRUFBQyxNQUFNLCtCQUErQixDQUFDO0FBQy9ELE9BQU8sRUFBQyxxQkFBcUIsRUFBQyxNQUFNLG9DQUFvQyxDQUFDO0FBQ3pFLE9BQU8sRUFBQyxtQkFBbUIsRUFBQyxNQUFNLGtDQUFrQyxDQUFDO0FBQ3JFLE9BQU8sRUFBQyxtQkFBbUIsRUFBQyxNQUFNLFFBQVEsQ0FBQzs7Ozs7QUFFM0MsTUFHYSxvQkFBcUIsU0FBUSxtQkFBc0M7SUFHNUUsWUFDYyxtQkFBd0MsRUFDeEMsZ0JBQWtDLEVBQ2xDLHFCQUE0QztRQUV0RCxLQUFLLEVBQUUsQ0FBQztRQUpFLHdCQUFtQixHQUFuQixtQkFBbUIsQ0FBcUI7UUFDeEMscUJBQWdCLEdBQWhCLGdCQUFnQixDQUFrQjtRQUNsQywwQkFBcUIsR0FBckIscUJBQXFCLENBQXVCO1FBSmhELGVBQVUsR0FBb0IsRUFBRSxDQUFDO1FBUXZDLElBQUksQ0FBQyxVQUFVLEdBQUc7WUFDZCxTQUFTLEVBQUUsbUJBQW1CO1lBQzlCLE1BQU0sRUFBRSxnQkFBZ0I7WUFDeEIsV0FBVyxFQUFFLHFCQUFxQjtTQUNyQyxDQUFDO1FBRUYsSUFBSSxDQUFDLFdBQVcsRUFBRSxDQUFDO0lBQ3ZCLENBQUM7SUFFUyxXQUFXO1FBQ2pCLE1BQU0sQ0FBQyxJQUFJLENBQUMsSUFBSSxDQUFDLGFBQWEsRUFBRSxDQUFDLENBQUMsT0FBTyxDQUFDLElBQUksQ0FBQyxFQUFFO1lBQzdDLElBQUksQ0FBQyxRQUFRLENBQUMsU0FBUyxFQUFFLElBQUksRUFBRSxJQUFJLENBQUMsYUFBYSxFQUFFLENBQUMsSUFBSSxDQUFDLENBQUMsQ0FBQztRQUMvRCxDQUFDLENBQUMsQ0FBQztJQUNQLENBQUM7SUFFUyxhQUFhO1FBQ25CLE9BQU8sSUFBSSxDQUFDLFVBQVUsSUFBSSxFQUFFLENBQUM7SUFDakMsQ0FBQztxRkEzQlEsb0JBQW9CO3VFQUFwQixvQkFBb0IsV0FBcEIsb0JBQW9CLG1CQUZqQixNQUFNOztTQUVULG9CQUFvQjt1RkFBcEIsb0JBQW9CO2NBSGhDLFVBQVU7ZUFBQztnQkFDUixVQUFVLEVBQUUsTUFBTTthQUNyQiIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogU3VpdGVDUk0gaXMgYSBjdXN0b21lciByZWxhdGlvbnNoaXAgbWFuYWdlbWVudCBwcm9ncmFtIGRldmVsb3BlZCBieSBTYWxlc0FnaWxpdHkgTHRkLlxuICogQ29weXJpZ2h0IChDKSAyMDI0IFNhbGVzQWdpbGl0eSBMdGQuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGZyZWUgc29mdHdhcmU7IHlvdSBjYW4gcmVkaXN0cmlidXRlIGl0IGFuZC9vciBtb2RpZnkgaXQgdW5kZXJcbiAqIHRoZSB0ZXJtcyBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIHZlcnNpb24gMyBhcyBwdWJsaXNoZWQgYnkgdGhlXG4gKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4gKiB0byBTZWN0aW9uIDE1IGFzIHBlcm1pdHRlZCBpbiBTZWN0aW9uIDcoYSk6IEZPUiBBTlkgUEFSVCBPRiBUSEUgQ09WRVJFRCBXT1JLXG4gKiBJTiBXSElDSCBUSEUgQ09QWVJJR0hUIElTIE9XTkVEIEJZIFNBTEVTQUdJTElUWSwgU0FMRVNBR0lMSVRZIERJU0NMQUlNUyBUSEVcbiAqIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBkaXN0cmlidXRlZCBpbiB0aGUgaG9wZSB0aGF0IGl0IHdpbGwgYmUgdXNlZnVsLCBidXQgV0lUSE9VVFxuICogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiAqIEZPUiBBIFBBUlRJQ1VMQVIgUFVSUE9TRS4gU2VlIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgZm9yIG1vcmVcbiAqIGRldGFpbHMuXG4gKlxuICogWW91IHNob3VsZCBoYXZlIHJlY2VpdmVkIGEgY29weSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiBhbG9uZyB3aXRoIHRoaXMgcHJvZ3JhbS4gIElmIG5vdCwgc2VlIDxodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvPi5cbiAqXG4gKiBJbiBhY2NvcmRhbmNlIHdpdGggU2VjdGlvbiA3KGIpIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIHZlcnNpb24gMywgdGhlc2UgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IHJldGFpbiB0aGUgZGlzcGxheSBvZiB0aGVcbiAqIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4gKiBmZWFzaWJsZSBmb3IgdGVjaG5pY2FsIHJlYXNvbnMsIHRoZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgZGlzcGxheVxuICogdGhlIHdvcmRzIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIuXG4gKi9cblxuaW1wb3J0IHtJbmplY3RhYmxlfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7RmllbGRIYW5kbGVyLCBGaWVsZEhhbmRsZXJNYXB9IGZyb20gXCIuL2ZpZWxkLWhhbmRsZXIubW9kZWxcIjtcbmltcG9ydCB7RGF0ZUZpZWxkSGFuZGxlcn0gZnJvbSBcIi4vaGFuZGxlcnMvZGF0ZS5maWVsZC1oYW5kbGVyXCI7XG5pbXBvcnQge011bHRpRW51bUZpZWxkSGFuZGxlcn0gZnJvbSBcIi4vaGFuZGxlcnMvbXVsdGllbnVtLmZpZWxkLWhhbmRsZXJcIjtcbmltcG9ydCB7RGVmYXVsdEZpZWxkSGFuZGxlcn0gZnJvbSBcIi4vaGFuZGxlcnMvZGVmYXVsdC5maWVsZC1oYW5kbGVyXCI7XG5pbXBvcnQge0Jhc2VTZXJ2aWNlUmVnaXN0cnl9IGZyb20gXCJjb21tb25cIjtcblxuQEluamVjdGFibGUoe1xuICAgIHByb3ZpZGVkSW46ICdyb290J1xufSlcbmV4cG9ydCBjbGFzcyBGaWVsZEhhbmRsZXJSZWdpc3RyeSBleHRlbmRzIEJhc2VTZXJ2aWNlUmVnaXN0cnk8RmllbGRIYW5kbGVyPGFueT4+IHtcblxuICAgIHByb3RlY3RlZCBkZWZhdWx0TWFwOiBGaWVsZEhhbmRsZXJNYXAgPSB7fTtcbiAgICBwcm90ZWN0ZWQgY29uc3RydWN0b3IoXG4gICAgICAgIHByb3RlY3RlZCBkZWZhdWx0RmllbGRIYW5kbGVyOiBEZWZhdWx0RmllbGRIYW5kbGVyLFxuICAgICAgICBwcm90ZWN0ZWQgZGF0ZUZpZWxkSGFuZGxlcjogRGF0ZUZpZWxkSGFuZGxlcixcbiAgICAgICAgcHJvdGVjdGVkIG11bHRpZW51bUZpZWxkSGFuZGxlcjogTXVsdGlFbnVtRmllbGRIYW5kbGVyXG4gICAgKSB7XG4gICAgICAgIHN1cGVyKCk7XG5cbiAgICAgICAgdGhpcy5kZWZhdWx0TWFwID0ge1xuICAgICAgICAgICAgJ2RlZmF1bHQnOiBkZWZhdWx0RmllbGRIYW5kbGVyLFxuICAgICAgICAgICAgJ2RhdGUnOiBkYXRlRmllbGRIYW5kbGVyLFxuICAgICAgICAgICAgJ211bHRpZW51bSc6IG11bHRpZW51bUZpZWxkSGFuZGxlclxuICAgICAgICB9O1xuXG4gICAgICAgIHRoaXMuaW5pdERlZmF1bHQoKTtcbiAgICB9XG5cbiAgICBwcm90ZWN0ZWQgaW5pdERlZmF1bHQoKTogdm9pZCB7XG4gICAgICAgIE9iamVjdC5rZXlzKHRoaXMuZ2V0RGVmYXVsdE1hcCgpKS5mb3JFYWNoKHR5cGUgPT4ge1xuICAgICAgICAgICAgdGhpcy5yZWdpc3RlcignZGVmYXVsdCcsIHR5cGUsIHRoaXMuZ2V0RGVmYXVsdE1hcCgpW3R5cGVdKTtcbiAgICAgICAgfSk7XG4gICAgfVxuXG4gICAgcHJvdGVjdGVkIGdldERlZmF1bHRNYXAoKTogRmllbGRIYW5kbGVyTWFwIHtcbiAgICAgICAgcmV0dXJuIHRoaXMuZGVmYXVsdE1hcCA/PyB7fTtcbiAgICB9XG59XG4iXX0=