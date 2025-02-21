import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {CampaignsRowActions} from './CampaignsRowActions';
import styles from './CampaignsListTable.module.scss';
import {GiveCampaignsListTable} from './types';
import CreateCampaignModal from '../CreateCampaignModal';
import {useEffect, useRef, useState} from 'react';
import MergeCampaignModal from '../MergeCampaign/Modal';

declare const window: {
    GiveCampaignsListTable: GiveCampaignsListTable;
} & Window;

/**
 * Auto open modal if the URL has the query parameter id as new
 *
 * @unreleased
 */
const autoOpenCreateCampaignModal = () => {
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

export default function CampaignsListTable() {
    const [isCreateCampaignModalOpen, setCreateCampaignModalOpen] = useState<boolean>(autoOpenCreateCampaignModal());
    const [isMergeCampaignsModalOpen, setMergeCampaignsModalOpen] = useState<boolean>(false);
    const [campaignsToMerge, setCampaignsToMerge] = useState({
        selected: [] as (string | number)[],
        names: [] as string[],
    });

    /**
     * useRef is used to store the latest value of `isMergeCampaignsModalOpen` without causing re-renders.
     * This ensures that the most up-to-date state is accessible synchronously, even during asynchronous updates or closures,
     * such as when the `confirm` function is executed. Without useRef, the stale value of `isMergeCampaignsModalOpen` captured by
     * the closure could lead to incorrect behavior, like reopening the modal unnecessarily.
     */
    const isMergeCampaignsModalOpenRef = useRef<boolean>(isMergeCampaignsModalOpen);
    useEffect(() => {
        isMergeCampaignsModalOpenRef.current = isMergeCampaignsModalOpen;
    }, [isMergeCampaignsModalOpen]);

    const bulkActions: Array<BulkActionsConfig> = [
        {
            label: __('Merge', 'give'),
            value: 'merge',
            type: 'custom',
            confirm: (selected, names) => {
                if (!isMergeCampaignsModalOpenRef.current) {
                    /**
                     * This timeout prevents this error from being thrown in the browser console:
                     * Warning: Cannot update a component (`CampaignsListTable`) while rendering a different component (`ListTablePage`).
                     *
                     * @see https://github.com/facebook/react/issues/18178#issuecomment-595846312
                     */
                    setTimeout(() => {
                        setCampaignsToMerge({selected, names});
                        setMergeCampaignsModalOpen(true);
                    }, 0);
                }
                return null;
            },
        },
    ];

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
                    <a
                        onClick={() => setCreateCampaignModalOpen(true)}
                        className={`button button-primary ${styles.button}`}
                    >
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
                <CreateCampaignModal isOpen={isCreateCampaignModalOpen} setOpen={setCreateCampaignModalOpen} />
                <MergeCampaignModal
                    isOpen={isMergeCampaignsModalOpen}
                    setOpen={setMergeCampaignsModalOpen}
                    campaigns={campaignsToMerge}
                />
            </ListTablePage>
        </>
    );
}
