import {__} from '@wordpress/i18n';
import {PanelRow, ToggleControl} from '@wordpress/components';
import {SettingsSection} from '@givewp/form-builder-library';
import DonationConfirmation from './donation-confirmation';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import TemplateTags from '@givewp/form-builder/components/settings/TemplateTags';

const {donationConfirmationTemplateTags} = getFormBuilderWindowData();

/**
 * @unreleased Added setting for enableReceiptConfirmationPage
 * @since 3.3.0
 */
export default function FormDonationConfirmationSettingsGroup({settings, setSettings}) {
    const {receiptHeading, receiptDescription, enableReceiptConfirmationPage} = settings;

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
            <SettingsSection title={__('Confirmation Page Redirect', 'give')}>
                <PanelRow>
                    <ToggleControl
                        label={__('Enable redirect to confirmation page', 'give')}
                        checked={enableReceiptConfirmationPage}
                        onChange={() => setSettings({enableReceiptConfirmationPage: !enableReceiptConfirmationPage})}
                        help={__(
                            'If enabled, donors will be sent to a donation confirmation page after successfully donating.  This page can be customized in the GiveWP settings under "Success Page".',
                            'give'
                        )}
                    />
                </PanelRow>
            </SettingsSection>
        </>
    );
}
