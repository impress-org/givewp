import {__} from '@wordpress/i18n';

import styles from '../admin-donation-forms.module.scss';
import DonationFormsTable from './DonationFormsTable';

export default function AdminDonationFormsPage() {
    return (
        <article>
            <div className={styles.pageHeader}>
                <h1 className={styles.pageTitle}>{__('Donation Forms', 'give')}</h1>
                <a href="post-new.php?post_type=give_forms" className={styles.button}>
                    {__('Add Form', 'give')}
                </a>
            </div>
            <div className={styles.pageContent}>
                <DonationFormsTable />
            </div>
        </article>
    );
}
