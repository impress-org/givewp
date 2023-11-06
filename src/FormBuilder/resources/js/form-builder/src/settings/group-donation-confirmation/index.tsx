import {__} from '@wordpress/i18n';
import SettingsGroup from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsGroup';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';
import DonationConfirmation from './donation-confirmation';
import TemplateTags from './template-tags';
import {setFormSettings, useFormState, useFormStateDispatch} from '@givewp/form-builder/stores/form-state';

export default function FormDonationConfirmationSettingsGroup() {
    const {
        settings: {receiptHeading, receiptDescription},
    } = useFormState();
    const dispatch = useFormStateDispatch();

    return (
        <SettingsGroup item="item-donation-confirmation" title={__('Donation Confirmation', 'give')}>
            <SettingsSection
                title={__('Header', 'give')}
                description={__('This is the first message that displays in the donation confirmation.', 'give')}
            >
                <DonationConfirmation
                    content={receiptHeading}
                    onChange={(receiptHeading) => dispatch(setFormSettings({receiptHeading}))}
                />
            </SettingsSection>
            <SettingsSection
                title={__('Description', 'give')}
                description={__('This is the second message that displays in the donation confirmation.', 'give')}
            >
                <DonationConfirmation
                    content={receiptDescription}
                    onChange={(receiptDescription) => dispatch(setFormSettings({receiptDescription}))}
                />
            </SettingsSection>
            <SettingsSection
                title={__('Template tags', 'give')}
                description={__(
                    'Available template tags for this email. HTML is accepted. See our documentation for examples of how to use custom meta email tags to output additional donor or donation information in your GiveWP emails',
                    'give'
                )}
            >
                <TemplateTags />
            </SettingsSection>
        </SettingsGroup>
    );
}
