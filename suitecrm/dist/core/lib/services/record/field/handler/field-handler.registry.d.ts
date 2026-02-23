import { FieldHandler, FieldHandlerMap } from "./field-handler.model";
import { DateFieldHandler } from "./handlers/date.field-handler";
import { MultiEnumFieldHandler } from "./handlers/multienum.field-handler";
import { DefaultFieldHandler } from "./handlers/default.field-handler";
import { BaseServiceRegistry } from "common";
import * as i0 from "@angular/core";
export declare class FieldHandlerRegistry extends BaseServiceRegistry<FieldHandler<any>> {
    protected defaultFieldHandler: DefaultFieldHandler;
    protected dateFieldHandler: DateFieldHandler;
    protected multienumFieldHandler: MultiEnumFieldHandler;
    protected defaultMap: FieldHandlerMap;
    protected constructor(defaultFieldHandler: DefaultFieldHandler, dateFieldHandler: DateFieldHandler, multienumFieldHandler: MultiEnumFieldHandler);
    protected initDefault(): void;
    protected getDefaultMap(): FieldHandlerMap;
    static ɵfac: i0.ɵɵFactoryDeclaration<FieldHandlerRegistry, never>;
    static ɵprov: i0.ɵɵInjectableDeclaration<FieldHandlerRegistry>;
}
//# sourceMappingURL=field-handler.registry.d.ts.map