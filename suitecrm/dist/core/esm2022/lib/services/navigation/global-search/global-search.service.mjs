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
import { Router } from '@angular/router';
import * as i0 from "@angular/core";
import * as i1 from "@angular/router";
class GlobalSearch {
    constructor(router) {
        this.router = router;
    }
    /**
     * Public Api
     */
    /**
     * Navigate to global search
     *
     * @param {string} searchTerm to search
     * @returns {object} Promise<boolean>
     */
    navigateToSearch(searchTerm, searchController) {
        let route = '/home/search';
        if (searchController === 'UnifiedSearch') {
            route = '/home/unified-search';
        }
        let queryString = '';
        if (searchTerm) {
            queryString = searchTerm;
        }
        return this.router.navigate([route], {
            queryParams: {
                // eslint-disable-next-line camelcase,@typescript-eslint/camelcase
                query_string: queryString
            }
        });
    }
    static { this.ɵfac = function GlobalSearch_Factory(t) { return new (t || GlobalSearch)(i0.ɵɵinject(i1.Router)); }; }
    static { this.ɵprov = /*@__PURE__*/ i0.ɵɵdefineInjectable({ token: GlobalSearch, factory: GlobalSearch.ɵfac, providedIn: 'root' }); }
}
export { GlobalSearch };
(function () { (typeof ngDevMode === "undefined" || ngDevMode) && i0.ɵsetClassMetadata(GlobalSearch, [{
        type: Injectable,
        args: [{ providedIn: 'root' }]
    }], function () { return [{ type: i1.Router }]; }, null); })();
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiZ2xvYmFsLXNlYXJjaC5zZXJ2aWNlLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsiLi4vLi4vLi4vLi4vLi4vLi4vLi4vY29yZS9hcHAvY29yZS9zcmMvbGliL3NlcnZpY2VzL25hdmlnYXRpb24vZ2xvYmFsLXNlYXJjaC9nbG9iYWwtc2VhcmNoLnNlcnZpY2UudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUE7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztHQXdCRztBQUVILE9BQU8sRUFBQyxVQUFVLEVBQUMsTUFBTSxlQUFlLENBQUM7QUFDekMsT0FBTyxFQUFDLE1BQU0sRUFBQyxNQUFNLGlCQUFpQixDQUFDOzs7QUFFdkMsTUFDYSxZQUFZO0lBRXJCLFlBQXNCLE1BQWM7UUFBZCxXQUFNLEdBQU4sTUFBTSxDQUFRO0lBQ3BDLENBQUM7SUFFRDs7T0FFRztJQUVIOzs7OztPQUtHO0lBQ0ksZ0JBQWdCLENBQUMsVUFBa0IsRUFBRSxnQkFBd0I7UUFFaEUsSUFBSSxLQUFLLEdBQUcsY0FBYyxDQUFDO1FBRTNCLElBQUksZ0JBQWdCLEtBQUssZUFBZSxFQUFDO1lBQ3JDLEtBQUssR0FBRyxzQkFBc0IsQ0FBQztTQUNsQztRQUVELElBQUksV0FBVyxHQUFHLEVBQUUsQ0FBQztRQUNyQixJQUFJLFVBQVUsRUFBRTtZQUNaLFdBQVcsR0FBRyxVQUFVLENBQUM7U0FDNUI7UUFFRCxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsUUFBUSxDQUFDLENBQUMsS0FBSyxDQUFDLEVBQUU7WUFDakMsV0FBVyxFQUFFO2dCQUNULGtFQUFrRTtnQkFDbEUsWUFBWSxFQUFFLFdBQVc7YUFDNUI7U0FDSixDQUFDLENBQUM7SUFDUCxDQUFDOzZFQWxDUSxZQUFZO3VFQUFaLFlBQVksV0FBWixZQUFZLG1CQURBLE1BQU07O1NBQ2xCLFlBQVk7dUZBQVosWUFBWTtjQUR4QixVQUFVO2VBQUMsRUFBQyxVQUFVLEVBQUUsTUFBTSxFQUFDIiwic291cmNlc0NvbnRlbnQiOlsiLyoqXG4gKiBTdWl0ZUNSTSBpcyBhIGN1c3RvbWVyIHJlbGF0aW9uc2hpcCBtYW5hZ2VtZW50IHByb2dyYW0gZGV2ZWxvcGVkIGJ5IFNhbGVzQWdpbGl0eSBMdGQuXG4gKiBDb3B5cmlnaHQgKEMpIDIwMjEgU2FsZXNBZ2lsaXR5IEx0ZC5cbiAqXG4gKiBUaGlzIHByb2dyYW0gaXMgZnJlZSBzb2Z0d2FyZTsgeW91IGNhbiByZWRpc3RyaWJ1dGUgaXQgYW5kL29yIG1vZGlmeSBpdCB1bmRlclxuICogdGhlIHRlcm1zIG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2UgdmVyc2lvbiAzIGFzIHB1Ymxpc2hlZCBieSB0aGVcbiAqIEZyZWUgU29mdHdhcmUgRm91bmRhdGlvbiB3aXRoIHRoZSBhZGRpdGlvbiBvZiB0aGUgZm9sbG93aW5nIHBlcm1pc3Npb24gYWRkZWRcbiAqIHRvIFNlY3Rpb24gMTUgYXMgcGVybWl0dGVkIGluIFNlY3Rpb24gNyhhKTogRk9SIEFOWSBQQVJUIE9GIFRIRSBDT1ZFUkVEIFdPUktcbiAqIElOIFdISUNIIFRIRSBDT1BZUklHSFQgSVMgT1dORUQgQlkgU0FMRVNBR0lMSVRZLCBTQUxFU0FHSUxJVFkgRElTQ0xBSU1TIFRIRVxuICogV0FSUkFOVFkgT0YgTk9OIElORlJJTkdFTUVOVCBPRiBUSElSRCBQQVJUWSBSSUdIVFMuXG4gKlxuICogVGhpcyBwcm9ncmFtIGlzIGRpc3RyaWJ1dGVkIGluIHRoZSBob3BlIHRoYXQgaXQgd2lsbCBiZSB1c2VmdWwsIGJ1dCBXSVRIT1VUXG4gKiBBTlkgV0FSUkFOVFk7IHdpdGhvdXQgZXZlbiB0aGUgaW1wbGllZCB3YXJyYW50eSBvZiBNRVJDSEFOVEFCSUxJVFkgb3IgRklUTkVTU1xuICogRk9SIEEgUEFSVElDVUxBUiBQVVJQT1NFLiBTZWUgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZSBmb3IgbW9yZVxuICogZGV0YWlscy5cbiAqXG4gKiBZb3Ugc2hvdWxkIGhhdmUgcmVjZWl2ZWQgYSBjb3B5IG9mIHRoZSBHTlUgQWZmZXJvIEdlbmVyYWwgUHVibGljIExpY2Vuc2VcbiAqIGFsb25nIHdpdGggdGhpcyBwcm9ncmFtLiAgSWYgbm90LCBzZWUgPGh0dHA6Ly93d3cuZ251Lm9yZy9saWNlbnNlcy8+LlxuICpcbiAqIEluIGFjY29yZGFuY2Ugd2l0aCBTZWN0aW9uIDcoYikgb2YgdGhlIEdOVSBBZmZlcm8gR2VuZXJhbCBQdWJsaWMgTGljZW5zZVxuICogdmVyc2lvbiAzLCB0aGVzZSBBcHByb3ByaWF0ZSBMZWdhbCBOb3RpY2VzIG11c3QgcmV0YWluIHRoZSBkaXNwbGF5IG9mIHRoZVxuICogXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIiBsb2dvLiBJZiB0aGUgZGlzcGxheSBvZiB0aGUgbG9nb3MgaXMgbm90IHJlYXNvbmFibHlcbiAqIGZlYXNpYmxlIGZvciB0ZWNobmljYWwgcmVhc29ucywgdGhlIEFwcHJvcHJpYXRlIExlZ2FsIE5vdGljZXMgbXVzdCBkaXNwbGF5XG4gKiB0aGUgd29yZHMgXCJTdXBlcmNoYXJnZWQgYnkgU3VpdGVDUk1cIi5cbiAqL1xuXG5pbXBvcnQge0luamVjdGFibGV9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHtSb3V0ZXJ9IGZyb20gJ0Bhbmd1bGFyL3JvdXRlcic7XG5cbkBJbmplY3RhYmxlKHtwcm92aWRlZEluOiAncm9vdCd9KVxuZXhwb3J0IGNsYXNzIEdsb2JhbFNlYXJjaCB7XG5cbiAgICBjb25zdHJ1Y3Rvcihwcm90ZWN0ZWQgcm91dGVyOiBSb3V0ZXIpIHtcbiAgICB9XG5cbiAgICAvKipcbiAgICAgKiBQdWJsaWMgQXBpXG4gICAgICovXG5cbiAgICAvKipcbiAgICAgKiBOYXZpZ2F0ZSB0byBnbG9iYWwgc2VhcmNoXG4gICAgICpcbiAgICAgKiBAcGFyYW0ge3N0cmluZ30gc2VhcmNoVGVybSB0byBzZWFyY2hcbiAgICAgKiBAcmV0dXJucyB7b2JqZWN0fSBQcm9taXNlPGJvb2xlYW4+XG4gICAgICovXG4gICAgcHVibGljIG5hdmlnYXRlVG9TZWFyY2goc2VhcmNoVGVybTogc3RyaW5nLCBzZWFyY2hDb250cm9sbGVyOiBzdHJpbmcpOiBQcm9taXNlPGJvb2xlYW4+IHtcblxuICAgICAgICBsZXQgcm91dGUgPSAnL2hvbWUvc2VhcmNoJztcblxuICAgICAgICBpZiAoc2VhcmNoQ29udHJvbGxlciA9PT0gJ1VuaWZpZWRTZWFyY2gnKXtcbiAgICAgICAgICAgIHJvdXRlID0gJy9ob21lL3VuaWZpZWQtc2VhcmNoJztcbiAgICAgICAgfVxuXG4gICAgICAgIGxldCBxdWVyeVN0cmluZyA9ICcnO1xuICAgICAgICBpZiAoc2VhcmNoVGVybSkge1xuICAgICAgICAgICAgcXVlcnlTdHJpbmcgPSBzZWFyY2hUZXJtO1xuICAgICAgICB9XG5cbiAgICAgICAgcmV0dXJuIHRoaXMucm91dGVyLm5hdmlnYXRlKFtyb3V0ZV0sIHtcbiAgICAgICAgICAgIHF1ZXJ5UGFyYW1zOiB7XG4gICAgICAgICAgICAgICAgLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIGNhbWVsY2FzZSxAdHlwZXNjcmlwdC1lc2xpbnQvY2FtZWxjYXNlXG4gICAgICAgICAgICAgICAgcXVlcnlfc3RyaW5nOiBxdWVyeVN0cmluZ1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9XG59XG4iXX0=