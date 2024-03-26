import {__} from '@wordpress/i18n';
import {PanelBody, PanelRow, TextControl} from '@wordpress/components';
import {setFormSettings, useFormState} from '@givewp/form-builder/stores/form-state';

export default function DonateButton({dispatch, publishSettings}) {
    const {
        settings: {
            donateButtonCaption,
            designSettingsImageUrl,
            designSettingsImageStyle,
            designSettingsLogoUrl,
            designSettingsLogoPosition,
        },
    } = useFormState();

    return (
        <PanelBody title={__('Donate Button', 'give')} initialOpen={false}>
            <PanelRow>
                <TextControl
                    label={__('Button caption', 'give')}
                    value={donateButtonCaption}
                    onChange={(donateButtonCaption) => {
                        dispatch(
                            setFormSettings({
                                donateButtonCaption,
                                designSettingsImageUrl,
                                designSettingsImageStyle,
                                designSettingsLogoUrl,
                                designSettingsLogoPosition,
                            })
                        );
                        publishSettings({
                            donateButtonCaption,
                            designSettingsImageUrl,
                            designSettingsImageStyle,
                            designSettingsLogoUrl,
                            designSettingsLogoPosition,
                        });
                    }}
                    help={__('Enter the text you want to display on the donation button.', 'give')}
                />
            </PanelRow>
        </PanelBody>
    );
}
