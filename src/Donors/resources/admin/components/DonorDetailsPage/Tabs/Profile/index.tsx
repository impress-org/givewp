import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import DonorAddress from './Address';
import DonorCustomFields from './CustomFields';
import DonorEmailAddress from './EmailAddress';
import DonorPersonalDetails from './PersonalDetails';

export default function DonorDetailsPageProfileTab() {
    return (
        <>
            <AdminSectionsWrapper>
                <DonorPersonalDetails />
                <DonorAddress />
                <DonorEmailAddress />
                <DonorCustomFields />
            </AdminSectionsWrapper>
        </>
    );
}
