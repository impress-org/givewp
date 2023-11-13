import {CurrencyControl} from '@givewp/form-builder/components/CurrencyControl';
import ClassicEditor from '@givewp/form-builder/components/ClassicEditor';
import SettingsGroup from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsGroup';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';

export {default as Header} from './header';
export {SecondarySidebar, Sidebar} from './sidebar';

export default function registerComponents() {
    window.givewp.form.components = window.givewp.form.components || {};
    window.givewp.form.components.CurrencyControl = CurrencyControl;
    window.givewp.form.components.ClassicEditor = ClassicEditor;
    window.givewp.form.components.SettingsGroup = SettingsGroup;
    window.givewp.form.components.SettingsSection = SettingsSection;
}
