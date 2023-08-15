import {createSlotFill} from '@wordpress/components';

/**
 * Slots for used within the field settings inspector controls. This allows plugins and such to add further controls to
 * the inspector sections.
 *
 * @since 3.0.0
 */
const {Slot: FieldSettingsSlot, Fill: FieldSettingsFill} = createSlotFill('GiveWP/FieldSettings/FieldSettingSlot');

const {Slot: AfterFieldSettingsSlot, Fill: AfterFieldSettingsFill} = createSlotFill(
    'GiveWP/FieldSettings/AfterFieldSettingsSlot'
);
const {Slot: DisplaySettingsSlot, Fill: DisplaySettingsFill} = createSlotFill(
    'GiveWP/FieldSettings/DisplaySettingSlot'
);

const {Slot: AfterDisplaySettingsSlot, Fill: AfterDisplaySettingsFill} = createSlotFill(
    'GiveWP/FieldSettings/AfterDisplaySettingsSlot'
);

export {
    FieldSettingsSlot,
    FieldSettingsFill,
    AfterFieldSettingsSlot,
    AfterFieldSettingsFill,
    DisplaySettingsSlot,
    DisplaySettingsFill,
    AfterDisplaySettingsSlot,
    AfterDisplaySettingsFill,
};
