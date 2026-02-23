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
import { Injectable } from "@angular/core";
import { ProcessService } from "../../../../process/process.service";
import { take } from "rxjs/operators";
import { BaseFieldHandler } from "./base.field-handler";
import { MessageService } from "../../../../message/message.service";
import * as i0 from "@angular/core";
import * as i1 from "../../../../process/process.service";
import * as i2 from "../../../../message/message.service";
class DateFieldHandler extends BaseFieldHandler {
    constructor(processService, messages) {
        super();
        this.processService = processService;
        this.messages = messages;
    }
    initDefaultValue(field, record) {
        if (field.defaultValueInitialized) {
            return;
        }
        let defaultValue = field?.default ?? field?.definition?.default ?? null;
        let displayDefault = field?.definition?.display_default ?? null;
        if (!defaultValue && !displayDefault) {
            field.defaultValueInitialized = true;
            return;
        }
        if (typeof defaultValue !== "string" && typeof displayDefault !== "string") {
            return;
        }
        if (defaultValue && typeof defaultValue !== "string") {
            super.initDefaultValue(field, record);
            return;
        }
        const processType = 'calculate-date-default';
        const options = {
            action: processType,
            module: record.module ?? '',
            field: field.name,
            displayDefault: displayDefault
        };
        field.loading = true;
        this.processService.submit(processType, options).pipe(take(1)).subscribe((result) => {
            const value = result?.data?.value ?? null;
            field.loading = false;
            if (value === null) {
                this.messages.addDangerMessageByKey("ERR_FIELD_LOGIC_BACKEND_CALCULATION");
                return;
            }
            this.updateValue(field, value.toString(), record);
            field.defaultValueInitialized = true;
        });
    }
    updateValue(field, value, record) {
        field.value = value.toString();
        field.formControl.setValue(value);
        // re-validate the parent form-control after value update
        record.formGroup.updateValueAndValidity({ onlySelf: true, emitEvent: true });
    }
    static { this.ɵfac = function DateFieldHandler_Factory(t) { return new (t || DateFieldHandler)(i0.ɵɵinject(i1.ProcessService), i0.ɵɵinject(i2.MessageService)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: DateFieldHandler, factory: DateFieldHandler.ɵfac, providedIn: 'root' }); }
}
export { DateFieldHandler };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(DateFieldHandler, [{
        type: Injectable,
        args: [{
                providedIn: 'root'
            }]
    }], function () { return [{ type: i1.ProcessService }, { type: i2.MessageService }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZGF0ZS5maWVsZC1oYW5kbGVyLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vLi4vY29yZS9hcHAvY29yZS9zcmMvbGliL3NlcnZpY2VzL3JlY29yZC9maWVsZC9oYW5kbGVyL2hhbmRsZXJzL2RhdGUuZmllbGQtaGFuZGxlci50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7O0dBd0JHO0FBQ0gsT0FBTyxFQUFDLFVBQVUsRUFBQyxNQUFNLGVBQWUsQ0FBQztBQUN6QyxPQUFPLEVBQUMsY0FBYyxFQUFDLE1BQU0scUNBQXFDLENBQUM7QUFDbkUsT0FBTyxFQUFDLElBQUksRUFBQyxNQUFNLGdCQUFnQixDQUFDO0FBR3BDLE9BQU8sRUFBQyxnQkFBZ0IsRUFBQyxNQUFNLHNCQUFzQixDQUFDO0FBQ3RELE9BQU8sRUFBQyxjQUFjLEVBQUMsTUFBTSxxQ0FBcUMsQ0FBQzs7OztBQUNuRSxNQUdhLGdCQUFpQixTQUFRLGdCQUEyQjtJQUU3RCxZQUNjLGNBQThCLEVBQzlCLFFBQXdCO1FBRWxDLEtBQUssRUFBRSxDQUFDO1FBSEUsbUJBQWMsR0FBZCxjQUFjLENBQWdCO1FBQzlCLGFBQVEsR0FBUixRQUFRLENBQWdCO0lBR3RDLENBQUM7SUFFRCxnQkFBZ0IsQ0FBQyxLQUFnQixFQUFFLE1BQWM7UUFFN0MsSUFBSSxLQUFLLENBQUMsdUJBQXVCLEVBQUU7WUFDL0IsT0FBTztTQUNWO1FBRUQsSUFBSSxZQUFZLEdBQUcsS0FBSyxFQUFFLE9BQU8sSUFBSSxLQUFLLEVBQUUsVUFBVSxFQUFFLE9BQU8sSUFBSSxJQUFJLENBQUM7UUFDeEUsSUFBSSxjQUFjLEdBQUcsS0FBSyxFQUFFLFVBQVUsRUFBRSxlQUFlLElBQUksSUFBSSxDQUFDO1FBQ2hFLElBQUksQ0FBQyxZQUFZLElBQUksQ0FBQyxjQUFjLEVBQUU7WUFDbEMsS0FBSyxDQUFDLHVCQUF1QixHQUFHLElBQUksQ0FBQztZQUNyQyxPQUFPO1NBQ1Y7UUFFRCxJQUFJLE9BQU8sWUFBWSxLQUFLLFFBQVEsSUFBSSxPQUFPLGNBQWMsS0FBSyxRQUFRLEVBQUU7WUFDeEUsT0FBTztTQUNWO1FBRUQsSUFBSSxZQUFZLElBQUksT0FBTyxZQUFZLEtBQUssUUFBUSxFQUFFO1lBQ2xELEtBQUssQ0FBQyxnQkFBZ0IsQ0FBQyxLQUFLLEVBQUUsTUFBTSxDQUFDLENBQUM7WUFDdEMsT0FBTztTQUNWO1FBRUQsTUFBTSxXQUFXLEdBQUcsd0JBQXdCLENBQUM7UUFFN0MsTUFBTSxPQUFPLEdBQUc7WUFDWixNQUFNLEVBQUUsV0FBVztZQUNuQixNQUFNLEVBQUUsTUFBTSxDQUFDLE1BQU0sSUFBSSxFQUFFO1lBQzNCLEtBQUssRUFBRSxLQUFLLENBQUMsSUFBSTtZQUNqQixjQUFjLEVBQUUsY0FBYztTQUNiLENBQUM7UUFFdEIsS0FBSyxDQUFDLE9BQU8sR0FBRyxJQUFJLENBQUM7UUFFckIsSUFBSSxDQUFDLGNBQWMsQ0FBQyxNQUFNLENBQUMsV0FBVyxFQUFFLE9BQU8sQ0FBQyxDQUFDLElBQUksQ0FBQyxJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUMsQ0FBQyxTQUFTLENBQUMsQ0FBQyxNQUFNLEVBQUUsRUFBRTtZQUVoRixNQUFNLEtBQUssR0FBRyxNQUFNLEVBQUUsSUFBSSxFQUFFLEtBQUssSUFBSSxJQUFJLENBQUM7WUFDMUMsS0FBSyxDQUFDLE9BQU8sR0FBRyxLQUFLLENBQUM7WUFFdEIsSUFBSSxLQUFLLEtBQUssSUFBSSxFQUFFO2dCQUNoQixJQUFJLENBQUMsUUFBUSxDQUFDLHFCQUFxQixDQUFDLHFDQUFxQyxDQUFDLENBQUM7Z0JBQzNFLE9BQU87YUFDVjtZQUNELElBQUksQ0FBQyxXQUFXLENBQUMsS0FBSyxFQUFFLEtBQUssQ0FBQyxRQUFRLEVBQUUsRUFBRSxNQUFNLENBQUMsQ0FBQztZQUNsRCxLQUFLLENBQUMsdUJBQXVCLEdBQUcsSUFBSSxDQUFDO1FBRXpDLENBQUMsQ0FBQyxDQUFDO0lBR1AsQ0FBQztJQUVTLFdBQVcsQ0FBQyxLQUFZLEVBQUUsS0FBYSxFQUFFLE1BQWM7UUFDN0QsS0FBSyxDQUFDLEtBQUssR0FBRyxLQUFLLENBQUMsUUFBUSxFQUFFLENBQUM7UUFDL0IsS0FBSyxDQUFDLFdBQVcsQ0FBQyxRQUFRLENBQUMsS0FBSyxDQUFDLENBQUM7UUFDbEMseURBQXlEO1FBQ3pELE1BQU0sQ0FBQyxTQUFTLENBQUMsc0JBQXNCLENBQUMsRUFBQyxRQUFRLEVBQUUsSUFBSSxFQUFFLFNBQVMsRUFBRSxJQUFJLEVBQUMsQ0FBQyxDQUFDO0lBQy9FLENBQUM7aUZBaEVRLGdCQUFnQjt1RUFBaEIsZ0JBQWdCLFdBQWhCLGdCQUFnQixtQkFGYixNQUFNOztTQUVULGdCQUFnQjt1RkFBaEIsZ0JBQWdCO2NBSDVCLFVBQVU7ZUFBQztnQkFDUixVQUFVLEVBQUUsTUFBTTthQUNyQiIsInNvdXJjZXNDb250ZW50IjpbIi8qKlxuICogU3VpdGVDUk0gaXMgYSBjdXN0b21lciByZWxhdGlvbnNoaXAgbWFuYWdlbWVudCBwcm9ncmFtIGRldmVsb3BlZCBieSBTYWxlc0FnaWxpdHkgTHRkLlxuICogQ29weXJpZ2h0IChDKSAyMDI0IFNhbGVzQWdpbGl0eSBMdGQuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGZyZWUgc29mdHdhcmU7IHlvdSBjYW4gcmVkaXN0cmlidXRlIGl0IGFuZC9vciBtb2RpZnkgaXQgdW5kZXJcbiAqIHRoZSB0ZXJtcyBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlIHZlcnNpb24gMyBhcyBwdWJsaXNoZWQgYnkgdGhlXG4gKiBGcmVlIFNvZnR3YXJlIEZvdW5kYXRpb24gd2l0aCB0aGUgYWRkaXRpb24gb2YgdGhlIGZvbGxvd2luZyBwZXJtaXNzaW9uIGFkZGVkXG4gKiB0byBTZWN0aW9uIDE1IGFzIHBlcm1pdHRlZCBpbiBTZWN0aW9uIDcoYSk6IEZPUiBBTlkgUEFSVCBPRiBUSEUgQ09WRVJFRCBXT1JLXG4gKiBJTiBXSElDSCBUSEUgQ09QWVJJR0hUIElTIE9XTkVEIEJZIFNBTEVTQUdJTElUWSwgU0FMRVNBR0lMSVRZIERJU0NMQUlNUyBUSEVcbiAqIFdBUlJBTlRZIE9GIE5PTiBJTkZSSU5HRU1FTlQgT0YgVEhJUkQgUEFSVFkgUklHSFRTLlxuICpcbiAqIFRoaXMgcHJvZ3JhbSBpcyBkaXN0cmlidXRlZCBpbiB0aGUgaG9wZSB0aGF0IGl0IHdpbGwgYmUgdXNlZnVsLCBidXQgV0lUSE9VVFxuICogQU5ZIFdBUlJBTlRZOyB3aXRob3V0IGV2ZW4gdGhlIGltcGxpZWQgd2FycmFudHkgb2YgTUVSQ0hBTlRBQklMSVRZIG9yIEZJVE5FU1NcbiAqIEZPUiBBIFBBUlRJQ1VMQVIgUFVSUE9TRS4gU2VlIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgZm9yIG1vcmVcbiAqIGRldGFpbHMuXG4gKlxuICogWW91IHNob3VsZCBoYXZlIHJlY2VpdmVkIGEgY29weSBvZiB0aGUgR05VIEFmZmVybyBHZW5lcmFsIFB1YmxpYyBMaWNlbnNlXG4gKiBhbG9uZyB3aXRoIHRoaXMgcHJvZ3JhbS4gIElmIG5vdCwgc2VlIDxodHRwOi8vd3d3LmdudS5vcmcvbGljZW5zZXMvPi5cbiAqXG4gKiBJbiBhY2NvcmRhbmNlIHdpdGggU2VjdGlvbiA3KGIpIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIHZlcnNpb24gMywgdGhlc2UgQXBwcm9wcmlhdGUgTGVnYWwgTm90aWNlcyBtdXN0IHJldGFpbiB0aGUgZGlzcGxheSBvZiB0aGVcbiAqIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIgbG9nby4gSWYgdGhlIGRpc3BsYXkgb2YgdGhlIGxvZ29zIGlzIG5vdCByZWFzb25hYmx5XG4gKiBmZWFzaWJsZSBmb3IgdGVjaG5pY2FsIHJlYXNvbnMsIHRoZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgZGlzcGxheVxuICogdGhlIHdvcmRzIFwiU3VwZXJjaGFyZ2VkIGJ5IFN1aXRlQ1JNXCIuXG4gKi9cbmltcG9ydCB7SW5qZWN0YWJsZX0gZnJvbSBcIkBhbmd1bGFyL2NvcmVcIjtcbmltcG9ydCB7UHJvY2Vzc1NlcnZpY2V9IGZyb20gXCIuLi8uLi8uLi8uLi9wcm9jZXNzL3Byb2Nlc3Muc2VydmljZVwiO1xuaW1wb3J0IHt0YWtlfSBmcm9tIFwicnhqcy9vcGVyYXRvcnNcIjtcbmltcG9ydCB7QXN5bmNBY3Rpb25JbnB1dH0gZnJvbSBcIi4uLy4uLy4uLy4uL3Byb2Nlc3MvcHJvY2Vzc2VzL2FzeW5jLWFjdGlvbi9hc3luYy1hY3Rpb25cIjtcbmltcG9ydCB7QmFzZUZpZWxkLCBGaWVsZCwgUmVjb3JkfSBmcm9tIFwiY29tbW9uXCI7XG5pbXBvcnQge0Jhc2VGaWVsZEhhbmRsZXJ9IGZyb20gXCIuL2Jhc2UuZmllbGQtaGFuZGxlclwiO1xuaW1wb3J0IHtNZXNzYWdlU2VydmljZX0gZnJvbSBcIi4uLy4uLy4uLy4uL21lc3NhZ2UvbWVzc2FnZS5zZXJ2aWNlXCI7XG5ASW5qZWN0YWJsZSh7XG4gICAgcHJvdmlkZWRJbjogJ3Jvb3QnXG59KVxuZXhwb3J0IGNsYXNzIERhdGVGaWVsZEhhbmRsZXIgZXh0ZW5kcyBCYXNlRmllbGRIYW5kbGVyPEJhc2VGaWVsZD4ge1xuXG4gICAgY29uc3RydWN0b3IoXG4gICAgICAgIHByb3RlY3RlZCBwcm9jZXNzU2VydmljZTogUHJvY2Vzc1NlcnZpY2UsXG4gICAgICAgIHByb3RlY3RlZCBtZXNzYWdlczogTWVzc2FnZVNlcnZpY2UsXG4gICAgKSB7XG4gICAgICAgIHN1cGVyKCk7XG4gICAgfVxuXG4gICAgaW5pdERlZmF1bHRWYWx1ZShmaWVsZDogQmFzZUZpZWxkLCByZWNvcmQ6IFJlY29yZCk6IHZvaWQge1xuXG4gICAgICAgIGlmIChmaWVsZC5kZWZhdWx0VmFsdWVJbml0aWFsaXplZCkge1xuICAgICAgICAgICAgcmV0dXJuO1xuICAgICAgICB9XG5cbiAgICAgICAgbGV0IGRlZmF1bHRWYWx1ZSA9IGZpZWxkPy5kZWZhdWx0ID8/IGZpZWxkPy5kZWZpbml0aW9uPy5kZWZhdWx0ID8/IG51bGw7XG4gICAgICAgIGxldCBkaXNwbGF5RGVmYXVsdCA9IGZpZWxkPy5kZWZpbml0aW9uPy5kaXNwbGF5X2RlZmF1bHQgPz8gbnVsbDtcbiAgICAgICAgaWYgKCFkZWZhdWx0VmFsdWUgJiYgIWRpc3BsYXlEZWZhdWx0KSB7XG4gICAgICAgICAgICBmaWVsZC5kZWZhdWx0VmFsdWVJbml0aWFsaXplZCA9IHRydWU7XG4gICAgICAgICAgICByZXR1cm47XG4gICAgICAgIH1cblxuICAgICAgICBpZiAodHlwZW9mIGRlZmF1bHRWYWx1ZSAhPT0gXCJzdHJpbmdcIiAmJiB0eXBlb2YgZGlzcGxheURlZmF1bHQgIT09IFwic3RyaW5nXCIpIHtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGlmIChkZWZhdWx0VmFsdWUgJiYgdHlwZW9mIGRlZmF1bHRWYWx1ZSAhPT0gXCJzdHJpbmdcIikge1xuICAgICAgICAgICAgc3VwZXIuaW5pdERlZmF1bHRWYWx1ZShmaWVsZCwgcmVjb3JkKTtcbiAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgfVxuXG4gICAgICAgIGNvbnN0IHByb2Nlc3NUeXBlID0gJ2NhbGN1bGF0ZS1kYXRlLWRlZmF1bHQnO1xuXG4gICAgICAgIGNvbnN0IG9wdGlvbnMgPSB7XG4gICAgICAgICAgICBhY3Rpb246IHByb2Nlc3NUeXBlLFxuICAgICAgICAgICAgbW9kdWxlOiByZWNvcmQubW9kdWxlID8/ICcnLFxuICAgICAgICAgICAgZmllbGQ6IGZpZWxkLm5hbWUsXG4gICAgICAgICAgICBkaXNwbGF5RGVmYXVsdDogZGlzcGxheURlZmF1bHRcbiAgICAgICAgfSBhcyBBc3luY0FjdGlvbklucHV0O1xuXG4gICAgICAgIGZpZWxkLmxvYWRpbmcgPSB0cnVlO1xuXG4gICAgICAgIHRoaXMucHJvY2Vzc1NlcnZpY2Uuc3VibWl0KHByb2Nlc3NUeXBlLCBvcHRpb25zKS5waXBlKHRha2UoMSkpLnN1YnNjcmliZSgocmVzdWx0KSA9PiB7XG5cbiAgICAgICAgICAgIGNvbnN0IHZhbHVlID0gcmVzdWx0Py5kYXRhPy52YWx1ZSA/PyBudWxsO1xuICAgICAgICAgICAgZmllbGQubG9hZGluZyA9IGZhbHNlO1xuXG4gICAgICAgICAgICBpZiAodmFsdWUgPT09IG51bGwpIHtcbiAgICAgICAgICAgICAgICB0aGlzLm1lc3NhZ2VzLmFkZERhbmdlck1lc3NhZ2VCeUtleShcIkVSUl9GSUVMRF9MT0dJQ19CQUNLRU5EX0NBTENVTEFUSU9OXCIpO1xuICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgIH1cbiAgICAgICAgICAgIHRoaXMudXBkYXRlVmFsdWUoZmllbGQsIHZhbHVlLnRvU3RyaW5nKCksIHJlY29yZCk7XG4gICAgICAgICAgICBmaWVsZC5kZWZhdWx0VmFsdWVJbml0aWFsaXplZCA9IHRydWU7XG5cbiAgICAgICAgfSk7XG5cblxuICAgIH1cblxuICAgIHByb3RlY3RlZCB1cGRhdGVWYWx1ZShmaWVsZDogRmllbGQsIHZhbHVlOiBzdHJpbmcsIHJlY29yZDogUmVjb3JkKTogdm9pZCB7XG4gICAgICAgIGZpZWxkLnZhbHVlID0gdmFsdWUudG9TdHJpbmcoKTtcbiAgICAgICAgZmllbGQuZm9ybUNvbnRyb2wuc2V0VmFsdWUodmFsdWUpO1xuICAgICAgICAvLyByZS12YWxpZGF0ZSB0aGUgcGFyZW50IGZvcm0tY29udHJvbCBhZnRlciB2YWx1ZSB1cGRhdGVcbiAgICAgICAgcmVjb3JkLmZvcm1Hcm91cC51cGRhdGVWYWx1ZUFuZFZhbGlkaXR5KHtvbmx5U2VsZjogdHJ1ZSwgZW1pdEV2ZW50OiB0cnVlfSk7XG4gICAgfVxuXG59XG4iXX0=