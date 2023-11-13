import {__} from '@wordpress/i18n';
import {PanelRow, TextControl, ToggleControl} from '@wordpress/components';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

const FormGridSettings = () => {
    const {
        settings: {formGridCustomize, formGridRedirectUrl, formGridDonateButtonText},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <>
            <PanelRow>
                <ToggleControl
                    label={__('Customize form grid', 'give')}
                    help={__(
                        'When enabled, donors will be notified that they have an account they can use to manage their donations. Disable if you do not want donors to be aware of their account.',
                        'give'
                    )}
                    checked={formGridCustomize}
                    onChange={() => {
                        dispatch(setFormSettings({formGridCustomize: !formGridCustomize}));
                    }}
                />
            </PanelRow>

            {formGridCustomize && (
                <div className={'givewp-form-settings__section__body__extra-gap'}>
                    <PanelRow>
                        <TextControl
                            label={__('Redirect Url', 'give')}
                            placeholder={'https://example.com/donation'}
                            help={__(
                                'The full URL of the page you want this form to redirect to when clicked on from the Form Grid. This only applies when the Form Grid uses the "Redirect" method.',
                                'give'
                            )}
                            value={formGridRedirectUrl}
                            onChange={(formGridRedirectUrl) => dispatch(setFormSettings({formGridRedirectUrl}))}
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
                            onChange={(formGridDonateButtonText) =>
                                dispatch(setFormSettings({formGridDonateButtonText}))
                            }
                        />
                    </PanelRow>
                </div>
            )}
        </>
    );
};

export default FormGridSettings;
