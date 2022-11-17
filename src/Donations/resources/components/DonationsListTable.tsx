import {useState} from 'react';
import {__, sprintf} from '@wordpress/i18n';
import {useSWRConfig} from 'swr';
import {ListTablePage} from '@givewp/components';
import RowAction from '@givewp/components/ListTable/RowAction';
import ListTableApi from '@givewp/components/ListTable/api';
import tableStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {IdBadge} from '@givewp/components/ListTable/TableCell';
import {
    BulkActionsConfig,
    FilterConfig,
    ShowConfirmModalContext,
    ToggleActionConfig,
} from '@givewp/components/ListTable/ListTablePage';
import {useContext} from 'react';
import {Interweave} from 'interweave';
import Switch from '../../../Views/Components/ListTable/Switch';

declare global {
    interface Window {
        GiveDonations: {
            apiNonce: string;
            apiRoot: string;
            adminUrl: string;
            forms: Array<{value: string; text: string}>;
            table: {columns: Array<object>};
            testMode: boolean;
        };
    }
}

const API = new ListTableApi(window.GiveDonations);

export default function () {
    const {mutate} = useSWRConfig();
    const [testMode, setTestMode] = useState(false);

    const rowActions = ({item, removeRow, setUpdateErrors, parameters}) => {
        const showConfirmModal = useContext(ShowConfirmModalContext);

        const fetchAndUpdateErrors = async (parameters, endpoint, id, method) => {
            const response = await API.fetchWithArgs(endpoint, {ids: [id]}, method);
            setUpdateErrors(response);
            await mutate(parameters);
            return response;
        };

        const deleteItem = async (selected) => await fetchAndUpdateErrors(parameters, '/delete', item.id, 'DELETE');

        const confirmDelete = (selected) => <p>{sprintf(__('Really delete donation #%d?', 'give'), item.id)}</p>;

        const confirmModal = (event) => {
            showConfirmModal(__('Delete', 'give'), confirmDelete, deleteItem, 'danger');
        };

        return (
            <>
                <RowAction
                    href={
                        window.GiveDonations.adminUrl +
                        `edit.php?post_type=give_forms&page=give-payment-history&view=view-payment-details&id=${item.id}`
                    }
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
    };

    const filters: Array<FilterConfig> = [
        {
            name: 'search',
            type: 'search',
            inlineSize: '14rem',
            text: __('Name, Email, or Donation ID', 'give'),
            ariaLabel: __('search donations', 'give'),
        },
        {
            name: 'form',
            type: 'formselect',
            text: __('Select Form', 'give'),
            ariaLabel: __('filter donation forms by status', 'give'),
            options: window.GiveDonations.forms,
        },
    ];

    const bulkActions: Array<BulkActionsConfig> = [
        {
            label: __('Delete', 'give'),
            value: 'delete',
            type: 'danger',
            action: async (selected) => {
                const response = await API.fetchWithArgs('/delete', {ids: selected.join(',')}, 'DELETE');
                return response;
            },
            confirm: (selected, names) => (
                <>
                    <p>{__('Really delete the following donations?', 'give')}</p>
                    <ul role="document" tabIndex={0}>
                        {selected.map((donationId, index) => (
                            <li key={donationId}>
                                <IdBadge id={donationId} />{' '}
                                <span>{sprintf(__('from %s', 'give'), <Interweave content={names[index]} />)}</span>
                            </li>
                        ))}
                    </ul>
                </>
            ),
        },
        ...(() => {
            const donationStatuses = {
                publish: __('Set To Completed', 'give'),
                pending: __('Set To Pending', 'give'),
                processing: __('Set To Processing', 'give'),
                refunded: __('Set To Refunded', 'give'),
                revoked: __('Set To Revoked', 'give'),
                failed: __('Set To Failed', 'give'),
                cancelled: __('Set To Cancelled', 'give'),
                abandoned: __('Set To Abandoned', 'give'),
                preapproval: __('Set To Preapproval', 'give'),
            };

            return Object.entries(donationStatuses).map(([value, label]) => {
                return {
                    label,
                    value,
                    action: async (selected) =>
                        await API.fetchWithArgs(
                            '/setStatus',
                            {
                                ids: selected.join(','),
                                status: value,
                            },
                            'POST'
                        ),
                    confirm: (selected, names) => (
                        <>
                            <p>{__('Set status for the following donations?', 'give')}</p>
                            <ul role="document" tabIndex={0}>
                                {selected.map((donationId, index) => (
                                    <li key={donationId}>
                                        <IdBadge id={donationId} /> <span>{__('from', 'give')}</span>
                                        <Interweave content={names[index]} />
                                    </li>
                                ))}
                            </ul>
                        </>
                    ),
                };
            });
        })(),
        {
            label: __('Resend Email Receipts', 'give'),
            value: 'resendEmailReceipt',
            action: async (selected) =>
                await API.fetchWithArgs('/resendEmailReceipt', {ids: selected.join(',')}, 'POST'),
            confirm: (selected, names) => (
                <>
                    <p>{__('Resend Email Receipts for following donations?', 'give')}</p>
                    <ul role="document" tabIndex={0}>
                        {selected.map((donationId, index) => (
                            <li key={donationId}>
                                <IdBadge id={donationId} /> <span>{sprintf(__('from %s', 'give'), names[index])}</span>
                            </li>
                        ))}
                    </ul>
                </>
            ),
        },
    ];

    const TestModeConfig: ToggleActionConfig = {
        testMode: testMode,
        setTestMode: (val) => setTestMode(val),
        switch: <Switch selected={testMode} action={setTestMode} label={__('View Test Donations', 'give')} />,
    };

    return (
        <ListTablePage
            title={__('Donations', 'give')}
            singleName={__('donation', 'give')}
            pluralName={__('donations', 'give')}
            rowActions={rowActions}
            bulkActions={bulkActions}
            apiSettings={window.GiveDonations}
            giveTestMode={window.GiveDonations.testMode}
            filterSettings={filters}
            toggleAction={TestModeConfig}
        >
            <a
                className={tableStyles.addFormButton}
                href={
                    window.GiveDonations.adminUrl +
                    'edit.php?post_type=give_forms&page=give-tools&tab=import&importer-type=import_donations'
                }
            >
                {__('Import Donations', 'give')}
            </a>
            <button className={tableStyles.addFormButton} onClick={showLegacyDonations}>
                {__('Switch to Legacy View')}
            </button>
        </ListTablePage>
    );
}

const showLegacyDonations = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.reload();
};
