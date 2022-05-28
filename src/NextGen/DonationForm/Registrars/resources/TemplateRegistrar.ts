import {Template} from '@givewp/forms/types';

export interface TemplateRegistrar {
    mount(template: Template): void;
    get(): Template;
}

export default class Registrar implements TemplateRegistrar {
    private template: Template;

    public mount(template: Template): void {
        this.template = template;
    }

    public get(): Template {
        return this.template;
    }
}
