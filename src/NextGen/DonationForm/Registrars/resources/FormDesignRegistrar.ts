import {FormDesign} from '@givewp/forms/types';

export interface FormDesignRegistrar {
    mount(design: FormDesign): void;

    get(): FormDesign;
}

export default class Registrar implements FormDesignRegistrar {
    private design: FormDesign;

    public mount(design: FormDesign): void {
        this.design = design;
    }

    public get(): FormDesign {
        return this.design;
    }
}
