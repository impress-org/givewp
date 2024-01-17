import {__} from '@wordpress/i18n';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import useDonationFormPubSub from '@givewp/forms/app/utilities/useDonationFormPubSub';
import {PanelBody, SelectControl} from '@wordpress/components';

export default function Section() {
    const {
        settings: {
            designSettingsSectionStyle,
            designSettingsImageStyle,
            designSettingsImageUrl,
            designSettingsLogoUrl,
            designSettingsLogoPosition,
        },
    } = useFormState();
    const dispatch = useFormStateDispatch();

    const {publishSettings} = useDonationFormPubSub();

    return (
        <PanelBody title={__('Section', 'give')}>
            <SelectControl
                label={__('Section Style', 'give')}
                onChange={(designSettingsSectionStyle) => {
                    dispatch(
                        setFormSettings({
                            designSettingsSectionStyle,
                            designSettingsImageStyle,
                            designSettingsImageUrl,
                            designSettingsLogoUrl,
                            designSettingsLogoPosition,
                        })
                    );
                    publishSettings({
                        designSettingsSectionStyle,
                        designSettingsImageStyle,
                        designSettingsImageUrl,
                        designSettingsLogoUrl,
                        designSettingsLogoPosition,
                    });
                }}
                value={designSettingsSectionStyle}
                options={[
                    {label: __('Default', 'give'), value: 'default'},
                    {label: __('Border', 'give'), value: 'border'},
                    {label: __('Solid', 'give'), value: 'solid'},
                    {label: __('Card', 'give'), value: 'card'},
                ]}
            />
        </PanelBody>
    );
}
