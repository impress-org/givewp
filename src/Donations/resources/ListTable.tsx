import {__} from '@wordpress/i18n';
import {useSWRConfig} from 'swr';
import {ListTableColumn, ListTablePage} from '@givewp/components';
import RowAction from "@givewp/components/ListTable/RowAction";
import ListTableApi from '@givewp/components/ListTable/api';
import styles from "@givewp/components/ListTable/ListTablePage.module.scss";

declare global {
    interface Window {
        GiveDonations;
    }
}

export default function () {

    const API = new ListTableApi(window.GiveDonations);
    const {mutate} = useSWRConfig();

    const rowActions = ({item, removeRow, setUpdateErrors, parameters}) => {

        const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
            const response = await API.fetchWithArgs(endpoint, {ids: [id]}, method);
            setUpdateErrors(response);
            await mutate(parameters);
            return response;
        }

        return (
            <>
                <RowAction
                    href={`/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=${item.id}`}
                    displayText={__('Edit', 'give')}
                />
                <RowAction
                    onClick={removeRow(async () => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE'))}
                    actionId={item.id}
                    displayText={__('Delete', 'give')}
                    hiddenText={item.name}
                    highlight
                />
            </>
        );
    }

    const columns: Array<ListTableColumn> = [
        {
            name: 'id',
            text: __('ID', 'give'),
            heading: true,
            preset: 'idBadge'
        },
        {
            name: 'amount',
            text: __('Amount', 'give'),
            preset: 'monetary',
        },
        {
            name: 'createdAt',
            text: __('Date', 'give'),
        },
        {
            name: 'donorName',
            text: __('Donor Name', 'give'),
            render: (donation: {donorName, donorId}) => (
                <a href={`edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donation.donorId}`}>
                    {donation.donorName}
                </a>
            ),
        },
        {
            name: 'formTitle',
            text: __('Donation Form', 'give'),
            render: (donation: {formTitle, formId}) => (
                <a href={`post.php?post=${donation.formId}&action=edit`}>
                    {donation.formTitle}
                </a>
            )
        },
        {
            name: 'status',
            text: __('Status', 'give'),
            preset: 'donationStatus',
        },

    ];

    const filters = [
        {
            name: 'search',
            type: 'search',
            text: __('Name, Email, or Donation ID', 'give'),
            ariaLabel: __('Search donations', 'give')
        },
        {
            name: 'form',
            type: 'formselect',
            text: __('Select Form', 'give'),
            ariaLabel: __('Filter donation forms by status', 'give'),
            options: window.GiveDonations.forms
        }
    ]

    const bulkActions = [
        {
            label: __('Delete', 'give'),
            value: 'delete',
            action: () => {},
        }
    ]

    return (
        <ListTablePage
            title={__('Donations', 'give')}
            singleName={__('Donation', 'give')}
            pluralName={__('Donations', 'give')}
            columns={columns}
            rowActions={rowActions}
            bulkActions={bulkActions}
            apiSettings={window.GiveDonations}
            filterSettings={filters}
        >
            <a className={styles.addFormButton}
               href={'edit.php?post_type=give_forms&page=give-tools&tab=import&importer-type=import_donations'}
            >
                {__('Import Donations', 'give')}
            </a>
        </ListTablePage>
    )
}
