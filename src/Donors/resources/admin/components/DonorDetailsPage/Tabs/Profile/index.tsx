import DonorAddress from './Address';
import DonorCustomFields from './CustomFields';
import DonorEmailAddress from './EmailAddress';
import DonorPersonalDetails from './PersonalDetails';

import styles from '../../DonorDetailsPage.module.scss';

export default function DonorDetailsPageProfileTab() {
    return (
        <>
            <div className={styles.sections}>
                <DonorPersonalDetails />
                <DonorAddress />
                <DonorEmailAddress />
                <DonorCustomFields />
            </div>
        </>
    );
}
