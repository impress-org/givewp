import {FieldSettings, FieldSettingsSupport} from './types';
import {__} from '@wordpress/i18n';

/**
 * Takes in the "supports" settings for a field and normalizes them into a standard object. It is recommended to use
 * this function rather than the settings directly.
 *
 * @since 3.0.0
 */
export default function normalizeFieldSettings(settings: FieldSettingsSupport | false): FieldSettings | null {
    if (settings === undefined || settings === false) {
        return null;
    }

    const getSupportSetting = (setting: keyof FieldSettings, enabledByDefault: boolean, defaultValue: any) => {
        if (settings[setting] === false) {
            return false;
        }

        if (settings[setting] === true) {
            return {default: defaultValue};
        }

        if (settings === true || settings[setting] === undefined) {
            return enabledByDefault ? {default: defaultValue} : false;
        }

        // @ts-ignore - it logically must be the default object
        return {default: settings[setting].default};
    };

    return {
        label: getSupportSetting('label', true, __('Custom field', 'give')),
        metaKey: !!getSupportSetting('metaKey', true, null),
        description: getSupportSetting('description', false, ''),
        placeholder: getSupportSetting('placeholder', false, ''),
        required: getSupportSetting('required', true, false),
        storeAsDonorMeta: getSupportSetting('storeAsDonorMeta', true, false),
        displayInAdmin: getSupportSetting('displayInAdmin', true, true),
        displayInReceipt: getSupportSetting('displayInReceipt', true, true),
        defaultValue: getSupportSetting('defaultValue', false, ''),
        emailTag: getSupportSetting('emailTag', true, ''),
    };
}
