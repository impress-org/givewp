import {__} from "@wordpress/i18n";
import {ListTableApi, ListTablePage} from "@givewp/components";
import {donationFormsColumns} from "./DonationFormsColumns";
import {DonationFormsRowActions} from "./DonationFormsRowActions";
import styles from "@givewp/components/ListTable/ListTablePage.module.scss";
import {BulkActionsConfig} from "@givewp/components/ListTable";

declare global {
    interface Window {
        GiveDonationForms: {apiNonce: string; apiRoot: string; authors: Array<{id: string|number, name: string}>};
    }
}

const API = new ListTableApi(window.GiveDonationForms);

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

const donationFormsBulkActions:Array<BulkActionsConfig> = [
    {
        label: __('Edit', 'give'),
        value: 'edit',
        action: async (selected) => {
            const authorSelect = document.getElementById('giveDonationFormsTableSetAuthor') as HTMLSelectElement;
            const author = authorSelect.value;
            const statusSelect = document.getElementById('giveDonationFormsTableSetStatus') as HTMLSelectElement;
            const status = statusSelect.value;
            if(! (author || status)){
                return {errors: [], successes: []};
            }
            const editParams = {
                ids: selected.join(','),
                author,
                status
            };
            const response = await API.fetchWithArgs('/edit', editParams, 'UPDATE');
            return response;
        },
        confirm: (selected, names) => (
            <>
                <p>Donation forms to be edited:</p>
                <ul>
                    {selected.map((id, index) => (
                        <li key={id}>{names[index]}</li>
                    ))}
                </ul>
                <label htmlFor='giveDonationFormsTableSetAuthor'>{__('Set form author', 'give')}</label>
                <select id='giveDonationFormsTableSetAuthor'>
                    <option value=''>{__('Keep current author', )}</option>
                    {window.GiveDonationForms.authors.map(author => (
                        <option value={author.id}>{author.name}</option>
                    ))}
                </select>
                <label htmlFor='giveDonationFormsTableSetStatus'>{__('Set form status', 'give')}</label>
                <select id='giveDonationFormsTableSetStatus'>
                    <option value=''>{__('Keep current status', )}</option>
                    <option value='publish'>{__('Published', 'give')}</option>
                    <option value='private'>{__('Private', 'give')}</option>
                    <option value='pending'>{__('Pending Review', 'give')}</option>
                    <option value='draft'>{__('Draft', 'give')}</option>
                </select>
            </>
        )
    },
    {
        label: __('Delete', 'give'),
        value: 'delete',
        action: async (selected) => {
            const response = await API.fetchWithArgs('/delete', {ids: selected.join(',')}, 'DELETE');
            return response;
        },
        confirm: (selected, names) => (
            <div>
                <p>
                    {__('Really delete the following donation forms?', 'give')}
                </p>
                <ul>
                    {selected.map((id, index) => (
                        <li key={id}>{names[index]}</li>
                    ))}
                </ul>
            </div>
        )
    }
];

export default function DonationFormsListTable(){
    return (
        <ListTablePage
            title={__('Donation Forms', 'give')}
            singleName={__('donation form', 'give')}
            pluralName={__('donation forms', 'give')}
            columns={donationFormsColumns}
            rowActions={DonationFormsRowActions}
            bulkActions={donationFormsBulkActions}
            apiSettings={window.GiveDonationForms}
            filterSettings={donationFormsFilters}
        >
            <a href={'post-new.php?post_type=give_forms'} className={styles.addFormButton}>
                {__('Add Form', 'give')}
            </a>
        </ListTablePage>
    );
}
