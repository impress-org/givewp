import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {PanelColorSettings} from '@wordpress/block-editor';
import {PanelBody} from '@wordpress/components';
import ColorInheritanceToggle from '@givewp/form-builder/settings/design/style-controls/color-inheritance-toggle';
import defaultColors from './defaultColors';

/**
 * @since TBD Move the default palette to defaultColors.ts so the embed panel can share it.
 * @since 4.3.0 Update the value of the default colors Primary color to improve accessibility color contrast.
 */
export default function Color({dispatch}) {
    const {
        settings: {primaryColor, secondaryColor},
    } = useFormState();

    const {publishColors} = useDonationFormPubSub();


    return (
        <PanelBody title={__('Color', 'give')}>
            <ColorInheritanceToggle dispatch={dispatch}>
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
                            colors: defaultColors,
                        },
                        {
                            value: secondaryColor,
                            onChange: (secondaryColor: string) => {
                                dispatch(setFormSettings({secondaryColor}));
                                publishColors({secondaryColor});
                            },
                            label: __('Secondary Color', 'give'),
                            disableCustomColors: false,
                            colors: defaultColors,
                        },
                    ]}
                />
            </ColorInheritanceToggle>
        </PanelBody>
    );
}
