import {__} from '@wordpress/i18n';
import {createInterpolateElement} from '@wordpress/element';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';
import DonationConfirmation from './donation-confirmation';
import TemplateTags from './template-tags';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

export default function FormDonationConfirmationSettingsGroup() {
    const {
        settings: {receiptHeading, receiptDescription},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    const templateTagsDescription = createInterpolateElement(
        __(
            'Available template tags for this email. HTML is accepted. <a>See our documentation</a> for examples of how to use custom meta email tags to output additional donor or donation information in your GiveWP emails',
            'give'
        ),
        {
            a: <a href="https://givewp.com/documentation/core/settings/emails/email-tags/" target="_blank" />,
        }
    );

    return (
        <>
            <SettingsSection
                title={__('Header', 'give')}
                description={__('This is the first message that displays in the donation confirmation.', 'give')}
            >
                <DonationConfirmation
                    id={'give-form-settings__donation-confirmation-heading'}
                    content={receiptHeading}
                    onChange={(receiptHeading) => dispatch(setFormSettings({receiptHeading}))}
                />
            </SettingsSection>
            <SettingsSection
                title={__('Description', 'give')}
                description={__('This is the second message that displays in the donation confirmation.', 'give')}
            >
                <DonationConfirmation
                    id={'give-form-settings__donation-confirmation-description'}
                    content={receiptDescription}
                    onChange={(receiptDescription) => dispatch(setFormSettings({receiptDescription}))}
                />
            </SettingsSection>
            <SettingsSection title={__('Template tags', 'give')} description={templateTagsDescription}>
                <TemplateTags />
            </SettingsSection>
        </>
    );
}
