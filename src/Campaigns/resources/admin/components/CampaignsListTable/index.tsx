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

declare global {
    interface Window {
        GiveCampaignsListTable: GiveCampaignsListTable;
    }
}

const API = new ListTableApi(window.GiveCampaignsListTable);

console.log('window.GiveCampaignsListTable: ', window.GiveCampaignsListTable);

const filters: Array<FilterConfig> = [
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
        text: __('Search by keyword', 'give'),
        ariaLabel: __('search campaigns', 'give'),
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
];

/**
 * Displays a blank slate for the Campaigns table.
 *
 * @unreleased
 */
const ListTableBlankSlate = () => {
    const imagePath = `${window.GiveCampaignsListTable.pluginUrl}/assets/dist/images/list-table/blank-slate-donation-forms-icon.svg`;
    return (
        <div className={styles.container}>
            <img src={imagePath} alt={__('No campaign created yet', 'give')} />
            <h3>{__('No campaign created yet', 'give')}</h3>
            <p className={styles.helpMessage}>{__('Don’t worry, let’s help you setup your first campaign.', 'give')}</p>
            <p>
                <a
                    href={`${window.GiveCampaignsListTable.adminUrl}edit.php?post_type=give_forms&page=give-campaigns&new=campaign`}
                    className={`button button-primary ${styles.button}`}
                >
                    {__('Create campaign', 'give')}
                </a>
            </p>
        </div>
    );
};

export default function CampaignsListTable() {
    return (
        <>
            <ListTablePage
                title={__('Campaigns', 'give')}
                singleName={__('campaign', 'give')}
                pluralName={__('campaigns', 'give')}
                apiSettings={window.GiveCampaignsListTable}
                filterSettings={filters}
                bulkActions={bulkActions}
                rowActions={CampaignsRowActions}
                listTableBlankSlate={ListTableBlankSlate()}
            >
                <CreateCampaignModal />
            </ListTablePage>
        </>
    );
}
