import { __ } from "@wordpress/i18n";
import { PanelRow, TextControl, ToggleControl } from "@wordpress/components";

/**
 * @since 3.3.0
 */
const FormGridSettings = ({settings, setSettings}) => {
    const {formGridCustomize, formGridRedirectUrl, formGridDonateButtonText} = settings;

    return (
        <>
            <PanelRow className={'no-extra-gap'}>
                <ToggleControl
                    label={__('Customize form grid', 'give')}
                    help={__(
                        'Customize the Redirect URL and Donate button text for this form in the Form Grid.',
                        'give'
                    )}
                    checked={formGridCustomize}
                    onChange={() => {
                        setSettings({formGridCustomize: !formGridCustomize});
                    }}
                />
            </PanelRow>

            {formGridCustomize && (
                <>
                    <PanelRow>
                        <TextControl
                            label={__('Redirect URL', 'give')}
                            placeholder={'https://example.com/donation'}
                            help={__(
                                'The full URL of the page you want this form to redirect to when clicked on from the Form Grid. This only applies when the Form Grid uses the "Redirect" method.',
                                'give'
                            )}
                            value={formGridRedirectUrl}
                            onChange={(formGridRedirectUrl) => setSettings({formGridRedirectUrl})}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('Donate Button Text', 'give')}
                            placeholder={__('Donate', 'give')}
                            help={__(
                                'The text on the Donate Button for this form when displayed on the Form Grid. This setting only applies if the Donate Button display option is enabled in your Form Grid.',
                                'give'
                            )}
                            value={formGridDonateButtonText}
                            onChange={(formGridDonateButtonText) => setSettings({formGridDonateButtonText})}
                        />
                    </PanelRow>
                </>
            )}
        </>
    );
};

export default FormGridSettings;
