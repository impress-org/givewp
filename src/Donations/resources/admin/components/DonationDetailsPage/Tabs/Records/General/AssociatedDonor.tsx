import {default as AssociatedDonorField} from '@givewp/admin/fields/AssociatedDonor';
import AdminSection from '@givewp/components/AdminDetailsPage/AdminSection';
import {__} from '@wordpress/i18n';
import {getDonationOptionsWindowData} from '@givewp/donations/utils';

/**
 * @since 4.11.0
 */
export default function AssociatedDonor() {
    const { mode } = getDonationOptionsWindowData();

    return (
        <AdminSection
            title={__('Associated donor', 'give')}
            description={__('Manage the donor connected to this donation', 'give')}
        >
            <AssociatedDonorField
                mode={mode}
                name="donorId"
                label={__('Donor', 'give')}
                description={__('Link the donation to the selected donor', 'give')}
            />
        </AdminSection>
    );
}
