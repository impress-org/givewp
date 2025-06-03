import DonorAddress from './Address';
import DonorCustomFields from './CustomFields';
import DonorEmailAddress from './EmailAddress';
import DonorPersonalDetails from './PersonalDetails';

import sharedStyles from '@givewp/components/AdminDetailsPage/AdminDetailsPage.module.scss';

export default function DonorDetailsPageProfileTab() {
    return (
        <>
            <div className={sharedStyles.sections}>
                <DonorPersonalDetails />
                <DonorAddress />
                <DonorEmailAddress />
                <DonorCustomFields />
            </div>
        </>
    );
}
