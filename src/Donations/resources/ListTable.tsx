import {__, sprintf} from '@wordpress/i18n';
import {useSWRConfig} from 'swr';
import {ListTableColumn, ListTablePage} from '@givewp/components';
import RowAction from "@givewp/components/ListTable/RowAction";
import ListTableApi from '@givewp/components/ListTable/api';
import styles from "@givewp/components/ListTable/ListTablePage.module.scss";
import {IdBadge} from "@givewp/components/ListTable/TableCell";
import {BulkActionsConfig, ShowConfirmModalContext} from "@givewp/components/ListTable";
import {useContext} from "react";
import {DonationType} from "@givewp/components/ListTable/DonorType";

declare global {
    interface Window {
        GiveDonations;
    }
}

export default function () {

    const API = new ListTableApi(window.GiveDonations);
    const {mutate} = useSWRConfig();

    const rowActions = ({item, removeRow, setUpdateErrors, parameters}) => {
        const showConfirmModal = useContext(ShowConfirmModalContext);

        const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
            const response = await API.fetchWithArgs(endpoint, {ids: [id]}, method);
            setUpdateErrors(response);
            await mutate(parameters);
            return response;
        }

        const deleteItem = async (selected) => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE');

        const confirmDelete = (selected) => (
            <p>
                {sprintf(__('Really delete donation #%d?', 'give'), item.id)}
            </p>
        );

        const confirmModal = (event) => {
            showConfirmModal(__('Delete', 'give'), confirmDelete, deleteItem);
        }

        return (
            <>
                <RowAction
                    href={`/wp-admin/edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=${item.id}`}
                    displayText={__('Edit', 'give')}
                />
                <RowAction
                    onClick={confirmModal}
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
            name: 'paymentType',
            text: __('Payment Type'),
            inlineSize: '12rem',
            render: (donation: {donationType}) => (
                <DonationType type={types[Math.floor(Math.random() * 3)]}/>
            )
        },
        {
            name: 'createdAt',
            text: __('Date', 'give'),
        },
        {
            name: 'donorName',
            text: __('Donor Name', 'give'),
            render: (donation: { donorName, donorId }) => (
                <a href={`edit.php?post_type=give_forms&page=give-donors&view=overview&id=${donation.donorId}`}>
                    {donation.donorName}
                </a>
            ),
        },
        {
            name: 'formTitle',
            text: __('Donation Form', 'give'),
            render: (donation: { formTitle, formId }) => (
                <a href={`post.php?post=${donation.formId}&action=edit`}>
                    {donation.formTitle}
                </a>
            )
        },
        {
            name: 'gateway',
            text: __('Gateway', 'give'),
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

    const bulkActions: Array<BulkActionsConfig> = [
        {
            label: __('Delete', 'give'),
            value: 'delete',
            action: async (selected) => {
                const response = await API.fetchWithArgs('/delete', {ids: selected.join(',')}, 'DELETE');
                return response;
            },
            confirm: (selected) => (
                <>
                    {__('Really delete the following donations?', 'give')}
                    <ul>
                        {selected.map(donationId => (
                            <li key={donationId}><IdBadge id={donationId}/></li>
                        ))}
                    </ul>
                </>
            )
        },
        ...(() => {
            const donationStatuses = {
                'publish': __('Set To Completed', 'give'),
                'pending': __('Set To Pending', 'give'),
                'processing': __('Set To Processing', 'give'),
                'refunded': __('Set To Refunded', 'give'),
                'revoked': __('Set To Revoked', 'give'),
                'failed': __('Set To Failed', 'give'),
                'cancelled': __('Set To Cancelled', 'give'),
                'abandoned': __('Set To Abandoned', 'give'),
                'preapproval': __('Set To Preapproval', 'give')
            };

            return Object.entries(donationStatuses).map(([value, label]) => {
                return {
                    label,
                    value,
                    action: async (selected) => await API.fetchWithArgs('/setStatus', {
                        ids: selected.join(','),
                        status: value
                    }, 'POST'),
                    confirm: (selected) => (
                        <>
                            {__('Set status for the following donations?', 'give')}
                            <ul>
                                {selected.map(donationId => (
                                    <li key={donationId}><IdBadge id={donationId}/></li>
                                ))}
                            </ul>
                        </>
                    )
                };
            });
        })(),
        {
            label: __('Resend Email Receipts', 'give'),
            value: 'resendEmailReceipt',
            action: async (selected) => await API.fetchWithArgs('/resendEmailReceipt', {ids: selected.join(',')}, 'POST'),
            confirm: (selected) => (
                <>
                    {__('Resend Email Receipts for following donations?', 'give')}
                    <ul>
                        {selected.map(donationId => (
                            <li key={donationId}><IdBadge id={donationId}/></li>
                        ))}
                    </ul>
                </>
            )
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

const types = [ 'single', 'renewal', 'subscription' ];
