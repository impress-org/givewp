import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';

/**
 * @note currently unused
 * @since 3.4.0
 */
export default function InputFields({dispatch, publishSettings}) {
    const {
        settings: {
            designSettingsTextFieldStyle,
            designSettingsImageStyle,
            designSettingsImageUrl,
            designSettingsLogoUrl,
            designSettingsLogoPosition,
        },
    } = useFormState();

    return (
        <PanelBody title={__('Input Fields', 'give')}>
            <PanelRow>
                <SelectControl
                    label={__('Input Field Style', 'give')}
                    help={__('Change the design of the input fields for this form.', 'give')}
                    onChange={(designSettingsTextFieldStyle) => {
                        dispatch(
                            setFormSettings({
                                designSettingsTextFieldStyle,
                                designSettingsImageStyle,
                                designSettingsImageUrl,
                                designSettingsLogoUrl,
                                designSettingsLogoPosition,
                            })
                        );
                        publishSettings({
                            designSettingsTextFieldStyle,
                            designSettingsImageStyle,
                            designSettingsImageUrl,
                            designSettingsLogoUrl,
                            designSettingsLogoPosition,
                        });
                    }}
                    value={designSettingsTextFieldStyle}
                    options={[
                        {label: __('Default', 'give'), value: 'default'},
                        {label: __('Box (inner-label)', 'give'), value: 'box'},
                        {label: __('Line (inner-label)', 'give'), value: 'line'},
                    ]}
                />
            </PanelRow>
        </PanelBody>
    );
}
