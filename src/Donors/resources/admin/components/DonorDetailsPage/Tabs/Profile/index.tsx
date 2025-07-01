import { AdminSectionsWrapper } from '@givewp/components/AdminDetailsPage/AdminSection';
import DonorAddress from './Address';
import DonorCustomFields from './CustomFields';
import DonorEmailAddress from './EmailAddress';
import DonorPersonalDetails from './PersonalDetails';
import { ProfileSectionsSlot } from '../../slots';

/**
 * @since 4.4.0
 */
export default function DonorDetailsPageProfileTab() {
    return (
        <>
            <AdminSectionsWrapper>
                <DonorPersonalDetails />
                <DonorAddress />
                <DonorEmailAddress />
                <DonorCustomFields />
                <ProfileSectionsSlot />
            </AdminSectionsWrapper>
        </>
    );
}
