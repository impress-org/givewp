import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import DonationDetails from './DonationDetails';
import AssociatedDonor from './AssociatedDonor';
import BillingDetails from './BillingDetails';

/**
 * @unreleased removed AssociatedDonor
 * @since 4.6.0
 */
export default function DonationDetailsPageRecordsGeneralTab() {
    return (
        <>
            <AdminSectionsWrapper>
                <DonationDetails />
                <BillingDetails />
            </AdminSectionsWrapper>
        </>
    );
}
