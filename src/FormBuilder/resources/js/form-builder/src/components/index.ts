import {CurrencyControl} from './CurrencyControl';
import Options from './OptionsPanel';

export {default as Header} from './header';
export {SecondarySidebar, Sidebar} from './sidebar';

export default function registerComponents() {
    window.givewp.components = window.givewp.form.components || {};
    window.givewp.components.CurrencyControl = CurrencyControl;
    window.givewp.components.DraggableOptionsControl = Options;
}
