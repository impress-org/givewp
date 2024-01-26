import {__} from '@wordpress/i18n';
import {PanelRow} from '@wordpress/components';
import {SettingsSection} from '@givewp/form-builder-library';
import DonationConfirmation from './donation-confirmation';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import TemplateTags from '@givewp/form-builder/components/settings/TemplateTags';

const {donationConfirmationTemplateTags} = getFormBuilderWindowData();

/**
 * @since 3.3.0
 */
export default function FormDonationConfirmationSettingsGroup({settings, setSettings}) {
    const {receiptHeading, receiptDescription} = settings;

    return (
        <>
            <SettingsSection
                title={__('Header', 'give')}
                description={__('This is the first message that displays in the donation confirmation.', 'give')}
            >
                <DonationConfirmation
                    id={'give-form-settings__donation-confirmation-heading'}
                    content={receiptHeading}
                    onChange={(receiptHeading) => setSettings({receiptHeading})}
                />
            </SettingsSection>
            <SettingsSection
                title={__('Description', 'give')}
                description={__('This is the second message that displays in the donation confirmation.', 'give')}
            >
                <DonationConfirmation
                    id={'give-form-settings__donation-confirmation-description'}
                    content={receiptDescription}
                    onChange={(receiptDescription) => setSettings({receiptDescription})}
                />
            </SettingsSection>
            <SettingsSection
                title={__('Template tags', 'give')}
                description={__(
                    'Available template tags for the header and description of the donation confirmation.',
                    'give'
                )}
            >
                <PanelRow>
                    <TemplateTags templateTags={donationConfirmationTemplateTags} />
                </PanelRow>
            </SettingsSection>
        </>
    );
}
