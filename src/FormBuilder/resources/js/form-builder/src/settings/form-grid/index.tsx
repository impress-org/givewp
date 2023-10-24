import {__} from '@wordpress/i18n';
import {
    Button,
    ExternalLink,
    PanelBody,
    PanelRow,
    TextControl,
    ToggleControl
} from '@wordpress/components';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';
import {closeSmall} from "@wordpress/icons";

const FormGridSettings = () => {
    const {
        settings: {formGridCustomize, formGridRedirectUrl, formGridDonateButtonText, formGridHideDocumentationLink},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <PanelBody title={__('Form Grid', 'give')} initialOpen={false}>
            <PanelRow>
                <ToggleControl
                    label={__('Customize form grid', 'give')}
                    checked={formGridCustomize}
                    onChange={() => {
                        dispatch(setFormSettings({formGridCustomize: !formGridCustomize}));
                    }}
                />
            </PanelRow>

            {formGridCustomize && (
                <>
                    <PanelRow>
                        <TextControl
                            label={__('Redirect Url', 'give')}
                            placeholder={'https://example.com/donation'}
                            help={__('The full URL of the page you want this form to redirect to when clicked on from the Form Grid. This only applies when the Form Grid uses the "Redirect" method.', 'give')}
                            value={formGridRedirectUrl}
                            onChange={(formGridRedirectUrl) => dispatch(setFormSettings({formGridRedirectUrl}))}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label={__('Donate Button Text', 'give')}
                            placeholder={__('Donate', 'give')}
                            help={__('The text on the Donate Button for this form when displayed on the Form Grid. This setting only applies if the Donate Button display option is enabled in your Form Grid.', 'give')}
                            value={formGridDonateButtonText}
                            onChange={(formGridDonateButtonText) => dispatch(setFormSettings({formGridDonateButtonText}))}
                        />
                    </PanelRow>
                </>
            )}

            {!formGridHideDocumentationLink && (
                <div style={{
                    position: 'relative',
                    backgroundColor: 'var(--givewp-grey-25)',
                    padding: 'var(--givewp-spacing-4)',
                }}>
                    <h3 style={{
                        fontSize: 'var(--givewp-font-size-headline-sm)',
                        marginBottom: 'var(--givewp-spacing-2)',
                    }}>{__('What is the Form Grid?', 'give')}</h3>
                    <p>
                        {__('The GiveWP Form Grid provides a way to add a grid layout of multiple forms into posts and pages using either a block or shortcode.', 'give')}
                    </p>
                    <ExternalLink href={'https://docs.givewp.com/form-grid-addon'}>{__('Learn more about the Form Grid', 'give')}</ExternalLink>
                    <Button
                        icon={closeSmall}
                        aria-label={__('Dismiss', 'give')}
                        style={{position: 'absolute', top: 'var(--givewp-spacing-2)', right: 'var(--givewp-spacing-2)'}}
                        onClick={() => dispatch(setFormSettings({formGridHideDocumentationLink: true}))}
                    ></Button>
                </div>
            )}

        </PanelBody>
    );
};

export default FormGridSettings;
