import {addFilter} from '@wordpress/hooks';

import FieldSettingsHOC from './FieldSettingsHOC';
import updateBlockTypes from './updateBlockTypes';
import {AfterDisplaySettingsFill, AfterFieldSettingsFill, DisplaySettingsFill, FieldSettingsFill} from './slots';

/**
 * Registers the necessary hooks for supports the field settings
 *
 * @since 0.6.0
 */
export default function registerHooks() {
    addFilter('editor.BlockEdit', 'givewp/supports/field-settings-hoc', FieldSettingsHOC);
    addFilter('blocks.registerBlockType', 'givewp/supports/field-settings-attributes', updateBlockTypes);

    // Mounts the field settings inspector slots so other plugins can extend the inspector controls
    window.givewp.form.slots = window.givewp.form.slots || {};
    window.givewp.form.slots.FieldSettingsFill = FieldSettingsFill;
    window.givewp.form.slots.AfterFieldSettingsFill = AfterFieldSettingsFill;
    window.givewp.form.slots.DisplaySettingsFill = DisplaySettingsFill;
    window.givewp.form.slots.AfterDisplaySettingsFill = AfterDisplaySettingsFill;
}
