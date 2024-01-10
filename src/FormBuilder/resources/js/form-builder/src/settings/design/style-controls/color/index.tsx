import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {PanelColorSettings, SETTINGS_DEFAULTS} from '@wordpress/block-editor';
import {PanelBody} from '@wordpress/components';

export default function Color({dispatch}) {
    const {
        settings: {primaryColor, secondaryColor},
    } = useFormState();

    const {publishColors} = useDonationFormPubSub();

    return (
        <PanelBody title={__('Color', 'give')}>
            <PanelColorSettings
                colorSettings={[
                    {
                        value: primaryColor,
                        onChange: (primaryColor: string) => {
                            dispatch(setFormSettings({primaryColor}));
                            publishColors({primaryColor});
                        },
                        label: __('Primary Color', 'give'),
                        disableCustomColors: false,
                        colors: SETTINGS_DEFAULTS.colors,
                    },
                    {
                        value: secondaryColor,
                        onChange: (secondaryColor: string) => {
                            dispatch(setFormSettings({secondaryColor}));
                            publishColors({secondaryColor});
                        },
                        label: __('Secondary Color', 'give'),
                        disableCustomColors: false,
                        colors: SETTINGS_DEFAULTS.colors,
                    },
                ]}
            />
        </PanelBody>
    );
}
