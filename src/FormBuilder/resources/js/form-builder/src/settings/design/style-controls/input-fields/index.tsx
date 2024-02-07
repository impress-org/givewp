import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';

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
        <PanelBody title={__('Input Styles', 'give')}>
            <PanelRow>
                <SelectControl
                    label={__('Input Field', 'give')}
                    help={__('Change the design of the input fields for this form.', 'give')}
                    onChange={(designSettingsTextFieldStyle) => {
                        dispatch(
                            setFormSettings({
                                designSettingsTextFieldStyle
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
