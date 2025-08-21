import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import DonationDetails from './DonationDetails';
import AssociatedDonor from './AssociatedDonor';
import BillingDetails from './BillingDetails';

/**
 * @since 4.7.0 removed AssociatedDonor
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
