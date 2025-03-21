import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import ListTableApi from '@givewp/components/ListTable/api';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {CampaignsRowActions} from './CampaignsRowActions';
import styles from './CampaignsListTable.module.scss';
import {GiveCampaignsListTable} from './types';
import CreateCampaignModal from '../CreateCampaignModal';
import {useState} from 'react';
import MergeCampaignModal from '../MergeCampaign/Modal';
import {useCampaignNoticeHook} from '@givewp/campaigns/hooks';
import CampaignNotice from '@givewp/campaigns/admin/components/CampaignDetailsPage/Components/Notices/CampaignNotice';

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

const bulkActions: Array<BulkActionsConfig> = [
    {
        label: __('Merge', 'give'),
        value: 'merge',
        type: 'custom',
        confirm: (selected, names, isOpen, setOpen) => {
            return (
                <MergeCampaignModal
                    isOpen={isOpen}
                    setOpen={setOpen}
                    campaigns={{
                        selected: selected,
                        names: names,
                    }}
                />
            );
        },
    },
];

export default function CampaignsListTable() {
    const [isCreateCampaignModalOpen, setCreateCampaignModalOpen] = useState<boolean>(autoOpenCreateCampaignModal());
    const [showTooltip, dismissTooltip] = useCampaignNoticeHook('givewp_campaign_listtable_notice');

    /**
     * Displays a blank slate for the Campaigns table.
     *
     * @unreleased
     */
    const ListTableBlankSlate = () => {
        const imagePath = `${
            getGiveCampaignsListTableWindowData().pluginUrl
        }build/assets/dist/images/list-table/blank-slate-campaigns-icon.svg`;
        return (
            <div className={styles.container}>
                <img src={imagePath} alt={__('No campaign created yet', 'give')} />
                <h3>{__('No campaign created yet', 'give')}</h3>
                <p className={styles.helpMessage}>
                    {__('Don’t worry, let’s help you set up your first campaign.', 'give')}
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
                {showTooltip && (
                    <CampaignNotice
                        title={__('Campaign List', 'give')}
                        description={__('We\'ve created a campaign from each of your donation forms. Your forms still work as before, but now with the added power of campaign management! Select a campaign to see how you can seamlessly manage your online fundraising.', 'give')}
                        linkText={__('Read documentation on what we changed', 'give')}
                        linkHref="#"
                        handleDismiss={dismissTooltip}
                        type={'campaignList'}
                    />
                )}
            </ListTablePage>
        </>
    );
}
