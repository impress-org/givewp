import {__} from "@wordpress/i18n";
import {ListTablePage} from "@givewp/components";
import {donationFormsColumns} from "./DonationFormsColumns";
import {DonationFormsRowActions} from "./DonationFormsRowActions";
import styles from "@givewp/components/ListTable/ListTablePage.module.scss";

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string};
    }
}

const donationStatus = [
    {
        value: 'any',
        text: __('All', 'give'),
    },
    {
        value: 'publish',
        text: __('Published', 'give'),
    },
    {
        value: 'pending',
        text: __('Pending', 'give'),
    },
    {
        value: 'draft',
        text: __('Draft', 'give'),
    },
    {
        value: 'trash',
        text: __('Trash', 'give'),
    }
]

const donationFormsFilters = [
    {
        name: 'search',
        type: 'search',
        text: __('Search by name or ID', 'give'),
        ariaLabel: __('Search donation forms', 'give')
    },
    {
        name: 'status',
        type: 'select',
        text: __('status', 'give'),
        ariaLabel: __('Filter donation forms by status', 'give'),
        options: donationStatus
    }
]

export default function DonationFormsListTable(){
    return (
        <ListTablePage
            title={__('Donation Forms', 'give')}
            singleName={__('donation form', 'give')}
            pluralName={__('donation forms', 'give')}
            columns={donationFormsColumns}
            rowActions={DonationFormsRowActions}
            apiSettings={window.GiveDonationForms}
            filterSettings={donationFormsFilters}
        >
            <a href={'post-new.php?post_type=give_forms'} className={styles.addFormButton}>
                {__('Add Form', 'give')}
            </a>
        </ListTablePage>
    );
}
