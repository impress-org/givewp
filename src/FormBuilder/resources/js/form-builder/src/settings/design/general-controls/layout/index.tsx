import {PanelBody, PanelRow, SelectControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';

export default function Layout({dispatch, publishSettings, formDesigns, designId}) {
    const {
        settings: {
            designSettingsTextFieldStyle,
            designSettingsImageStyle,
            designSettingsImageUrl,
            designSettingsLogoUrl,
            designSettingsLogoPosition,
        },
    } = useFormState();

    const designOptions = Object.values(formDesigns).map(({id, name}) => ({value: id, label: name}));

    return (
        <PanelBody title={__('Donation Form', 'give')} initialOpen={true}>
            <PanelRow>
                <SelectControl
                    label={__('Form layout', 'give')}
                    value={designId}
                    onChange={(designId: string) => dispatch(setFormSettings({designId}))}
                    options={designOptions}
                    help={__(
                        'Change the appearance of your donation form on your site. Each option has a different layout.',
                        'give'
                    )}
                />
            </PanelRow>
            {'classic' === designId && (
                <PanelRow>
                    <SelectControl
                        label={__('Input Field', 'give')}
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
            )}
        </PanelBody>
    );
}
