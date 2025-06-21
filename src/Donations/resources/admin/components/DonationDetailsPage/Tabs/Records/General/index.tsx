import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import DonationDetails from './DonationDetails';
import AssociatedDonor from './AssociatedDonor';
import BillingDetails from './BillingDetails';

/**
 * @unreleased
 */
export default function DonationDetailsPageRecordsGeneralTab() {
    return (
        <>
            <AdminSectionsWrapper>
                <DonationDetails />
                <AssociatedDonor />
                <BillingDetails />
            </AdminSectionsWrapper>
        </>
    );
}
