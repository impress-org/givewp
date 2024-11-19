import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {IdBadge} from '@givewp/components/ListTable/TableCell';
import {Interweave} from 'interweave';
import {CampaignsRowActions} from './CampaignsRowActions';
import styles from './CampaignsListTable.module.scss';
import {GiveCampaignsListTable} from './types';
import CreateCampaignModal from '../CreateCampaignModal';
import {useState} from 'react';
import MergeCampaignModal from '../MergeCampaign/Modal';

declare const window: {
    GiveCampaignsListTable: GiveCampaignsListTable;
} & Window;

/**
 * Auto open modal if the URL has the query parameter id as new
 *
 * @unreleased
 */
const autoOpenModal = () => {
    const queryParams = new URLSearchParams(window.location.search);
    const newParam = queryParams.get('new');

    return newParam === 'campaign';
};

export function getGiveCampaignsListTableWindowData() {
    return window.GiveCampaignsListTable;
}

const API = new ListTableApi(getGiveCampaignsListTableWindowData());

const campaignStatus = [
    {
        value: 'any',
        text: __('All Status', 'give'),
    },
    {
        value: 'active',
        text: __('Active', 'give'),
    },
    {
        value: 'inactive',
        text: __('Archived', 'give'),
    },
    {
        value: 'draft',
        text: __('Draft', 'give'),
    },
    {
        value: 'pending',
        text: __('Pending', 'give'),
    },
    {
        value: 'processing',
        text: __('Processing', 'give'),
    },
    {
        value: 'failed',
        text: __('Failed', 'give'),
    },
];

const filters: Array<FilterConfig> = [
    {
        name: 'status',
        type: 'select',
        text: __('status', 'give'),
        ariaLabel: __('Filter campaign by status', 'give'),
        options: campaignStatus,
    },
    {
        name: 'search',
        type: 'search',
        text: __('Search by name or ID', 'give'),
        ariaLabel: __('Search donation forms', 'give'),
    },
];

const bulkActions: Array<BulkActionsConfig> = [
    {
        label: __('Delete', 'give'),
        value: 'delete',
        type: 'danger',
        action: async (selected) => {
            return await API.fetchWithArgs('', {ids: selected.join(',')}, 'DELETE');
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Really delete the following campaigns?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((campaignId, index) => (
                        <li key={campaignId}>
                            <IdBadge id={campaignId} />{' '}
                            <span>
                                {__('from ', 'give')} <Interweave content={names[index]} />
                            </span>
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
    {
        label: __('Merge', 'give'),
        value: 'merge',
        type: 'custom',
        confirm: (selected, names) => {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('action', 'merge');
            window.history.replaceState(
                {selected: selected, names: names},
                __('Merge Campaigns', 'give'),
                `${window.location.pathname}?${urlParams.toString()}`
            );

            return null;
        },
    },
];

export default function CampaignsListTable() {
    const [isOpen, setOpen] = useState<boolean>(autoOpenModal());

    /**
     * Displays a blank slate for the Campaigns table.
     *
     * @unreleased
     */
    const ListTableBlankSlate = () => {
        const imagePath = `${
            getGiveCampaignsListTableWindowData().pluginUrl
        }/assets/dist/images/list-table/blank-slate-donation-forms-icon.svg`;
        return (
            <div className={styles.container}>
                <img src={imagePath} alt={__('No campaign created yet', 'give')} />
                <h3>{__('No campaign created yet', 'give')}</h3>
                <p className={styles.helpMessage}>
                    {__('Don’t worry, let’s help you setup your first campaign.', 'give')}
                </p>
                <p>
                    <a onClick={() => setOpen(true)} className={`button button-primary ${styles.button}`}>
                        {__('Create campaign', 'give')}
                    </a>
                </p>
            </div>
        );
    };

    return (
        <>
            <ListTablePage
                title={__('Campaigns', 'give')}
                singleName={__('campaign', 'give')}
                pluralName={__('campaigns', 'give')}
                apiSettings={getGiveCampaignsListTableWindowData()}
                filterSettings={filters}
                bulkActions={bulkActions}
                rowActions={CampaignsRowActions}
                listTableBlankSlate={ListTableBlankSlate()}
            >
                <CreateCampaignModal isOpen={isOpen} setOpen={setOpen} />
                <MergeCampaignModal />
            </ListTablePage>
        </>
    );
}
