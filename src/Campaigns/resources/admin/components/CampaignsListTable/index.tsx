import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import {BulkActionsConfig} from '@givewp/components/ListTable/ListTablePage';
import {campaignsRowActions} from './CampaignsRowActions';
import styles from './CampaignsListTable.module.scss';
import {GiveCampaignsListTable} from './types';
import CreateCampaignModal from '../CreateCampaignModal';
import {useState} from 'react';
import MergeCampaignModal from '../MergeCampaign/Modal';
import ExistingUserIntroModal from '@givewp/campaigns/admin/components/ExistingUserIntroModal';
import {getCampaignOptionsWindowData} from '@givewp/campaigns/utils';
import {useCampaignNoticeHook} from '@givewp/campaigns/hooks';
import type {ListTableNotice} from '@givewp/components/ListTable/notices/types';
import listTableScreen from './listTableScreen';
import {campaignFilters} from './filters';

declare const window: {
    GiveCampaignsListTable: GiveCampaignsListTable;
} & Window;

/**
 * Auto open modal if the URL has the query parameter id as new
 *
 * @since 4.0.0
 */
const autoOpenCreateCampaignModal = () => {
    const queryParams = new URLSearchParams(window.location.search);
    const newParam = queryParams.get('new');

    return newParam === 'campaign';
};

const shouldShowExistingUserIntroModal = getCampaignOptionsWindowData().admin.showExistingUserIntroNotice;

export function getGiveCampaignsListTableWindowData() {
    return window.GiveCampaignsListTable;
}

const bulkActions: Array<BulkActionsConfig> = [
    {
        label: __('Merge', 'give'),
        value: 'merge',
        type: 'custom',
        minSelected: 2,
        confirm: (selected, names, isOpen, setOpen, list) => {
            return (
                <MergeCampaignModal
                    isOpen={isOpen}
                    setOpen={setOpen}
                    onMerged={() => {
                        list?.clearSelection();
                        list?.refresh();
                    }}
                    campaigns={{
                        selected: selected,
                        names: names,
                    }}
                />
            );
        },
    },
];

/**
 * @since TBD Show the "Campaign List" tip as a notice above the list, not a floating tooltip.
 * @since TBD Pass the filter declaration in place of the filter settings.
 * @since TBD Pass the row actions as data and the screen config; Merge needs two selected campaigns and reloads the list.
 * @since 4.0.0
 */
export default function CampaignsListTable() {
    const [isCreateCampaignModalOpen, setCreateCampaignModalOpen] = useState<boolean>(autoOpenCreateCampaignModal());
    const [isExistingUserIntroModalOpen, setExistingUserIntroModalOpen] = useState<boolean>(
        shouldShowExistingUserIntroModal
    );
    const [showTooltip, dismissTooltip] = useCampaignNoticeHook('givewp_campaign_listtable_notice');

    /**
     * Displays a blank slate for the Campaigns table.
     *
     * @since 4.0.0
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

    const notices: ListTableNotice[] =
        !isExistingUserIntroModalOpen && showTooltip
            ? [
                  {
                      id: 'campaign-list',
                      kind: 'onboarding',
                      title: __('Campaign List', 'give'),
                      message: __(
                          "We've created a campaign from each of your donation forms. Your forms still work as before, but now with the added power of campaign management! Select a campaign to see how you can seamlessly manage your online fundraising.",
                          'give'
                      ),
                      link: {
                          href: 'https://docs.givewp.com/campaigns-docs',
                          label: __('Read documentation on what we changed', 'give'),
                          opensInNewTab: true,
                      },
                      onDismiss: dismissTooltip,
                  },
              ]
            : [];

    return (
        <>
            <ListTablePage
                title={__('Campaigns', 'give')}
                singleName={__('campaign', 'give')}
                pluralName={__('campaigns', 'give')}
                apiSettings={getGiveCampaignsListTableWindowData()}
                filters={campaignFilters}
                bulkActions={bulkActions}
                rowActions={campaignsRowActions}
                listTableBlankSlate={ListTableBlankSlate()}
                screen={listTableScreen}
                notices={notices}
            >
                <CreateCampaignModal isOpen={isCreateCampaignModalOpen} setOpen={setCreateCampaignModalOpen} />
                <ExistingUserIntroModal isOpen={isExistingUserIntroModalOpen} setOpen={setExistingUserIntroModalOpen} />
            </ListTablePage>
        </>
    );
}
