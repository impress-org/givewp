import {CurrencyControl} from '@givewp/form-builder/components/CurrencyControl';
import ClassicEditor from '@givewp/form-builder/components/ClassicEditor';
import OptionsPanel from '@givewp/form-builder/components/OptionsPanel';

export {default as Header} from './header';
export {SecondarySidebar, Sidebar} from './sidebar';

export default function registerComponents() {
    window.givewp.form.components = window.givewp.form.components || {};
    window.givewp.form.components.CurrencyControl = CurrencyControl;
    window.givewp.form.components.ClassicEditor = ClassicEditor;
    window.givewp.form.components.OptionsPanel = OptionsPanel;
}
