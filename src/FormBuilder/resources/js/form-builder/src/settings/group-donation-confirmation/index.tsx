import {__} from '@wordpress/i18n';
import SettingsGroup from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsGroup';
import SettingsSection from '@givewp/form-builder/components/canvas/FormSettingsContainer/components/SettingsSection';
import DonationConfirmation from './donation-confirmation';

export default function FormDonationConfirmationSettingsGroup() {
    return (
        <SettingsGroup item="item-donation-confirmation" title={__('Donation Confirmation', 'give')}>
            <SettingsSection title={__('Donation Confirmation', 'give')}>
                <DonationConfirmation />
            </SettingsSection>
        </SettingsGroup>
    );
}
