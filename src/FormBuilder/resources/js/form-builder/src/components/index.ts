import {CurrencyControl} from './CurrencyControl';
import Options from './OptionsPanel';

export {default as Header} from './header';
export {SecondarySidebar, Sidebar} from './sidebar';

export default function registerComponents() {
    window.givewp.form.components = window.givewp.form.components || {};
    window.givewp.form.components.CurrencyControl = CurrencyControl;
    window.givewp.form.components.DraggableOptionsControl = Options;
}
