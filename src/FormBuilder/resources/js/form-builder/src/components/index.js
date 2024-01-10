import {CurrencyControl} from '@givewp/form-builder/components/CurrencyControl';

export {default as Header} from './header';
export {SecondarySidebar, Sidebar} from './sidebar';

export default function registerComponents() {
    window.givewp.form.components = window.givewp.form.components || {};
    window.givewp.form.components.CurrencyControl = CurrencyControl;
}
