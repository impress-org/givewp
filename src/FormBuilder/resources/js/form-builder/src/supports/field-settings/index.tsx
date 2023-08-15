import {addFilter} from '@wordpress/hooks';

import FieldSettingsHOC from './FieldSettingsHOC';
import updateBlockTypes from './updateBlockTypes';
import {AfterDisplaySettingsFill, AfterFieldSettingsFill, DisplaySettingsFill, FieldSettingsFill} from './slots';

/**
 * Registers the necessary hooks for supports the field settings
 *
 * @since 3.0.0
 */
export default function registerHooks() {
    addFilter('editor.BlockEdit', 'givewp/supports/field-settings-hoc', FieldSettingsHOC);
    addFilter('blocks.registerBlockType', 'givewp/supports/field-settings-attributes', updateBlockTypes);

    // Mounts the field settings inspector slots so other plugins can extend the inspector controls
    // @ts-ignore
    window.givewp.form.slots = window.givewp.form.slots || {};
    // @ts-ignore
    window.givewp.form.slots.FieldSettingsFill = FieldSettingsFill;
    // @ts-ignore
    window.givewp.form.slots.AfterFieldSettingsFill = AfterFieldSettingsFill;
    // @ts-ignore
    window.givewp.form.slots.DisplaySettingsFill = DisplaySettingsFill;
    // @ts-ignore
    window.givewp.form.slots.AfterDisplaySettingsFill = AfterDisplaySettingsFill;
}
