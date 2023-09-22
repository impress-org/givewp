import {CurrencyControl} from '@givewp/form-builder/components/CurrencyControl';

export {default as Header} from './header';
export {SecondarySidebar, Sidebar} from './sidebar';

export default function registerComponents() {
    // @ts-ignore
    window.givewp.form.components = window.givewp.form.components || {};
    // @ts-ignore
    window.givewp.form.components.CurrencyControl = CurrencyControl;
}
