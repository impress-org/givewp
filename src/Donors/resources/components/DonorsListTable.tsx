import {__} from '@wordpress/i18n';
import {ListTableApi, ListTablePage} from '@givewp/components';
import {DonorsRowActions} from './DonorsRowActions';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import styles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import {Interweave} from 'interweave';
import './style.scss';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';
import ProductRecommendations from '@givewp/components/ListTable/ProductRecommendations';
import { StatConfig } from '@givewp/components/ListTable/ListTableStats/ListTableStats';
import filterByOptions from '../constants/filterByOptions';

declare global {
    interface Window {
        GiveDonors: {
            apiNonce: string;
            apiRoot: string;
            forms: Array<{value: string; text: string}>;
            table: {columns: Array<object>};
            adminUrl: string;
            pluginUrl: string;
            dissedRecommendations: Array<string>;
            recurringDonationsEnabled: boolean;
            donorStatuses: {[statusCode: string]: string};
        };
    }
}

const API = new ListTableApi(window.GiveDonors);

const donorsFilters: Array<FilterConfig> = [
    {
        name: 'campaignId',
        type: 'campaignselect',
        text: __('All Campaigns', 'give'),
        ariaLabel: __('Filter donors by campaign', 'give'),
        options: window.GiveDonors.forms,
    },
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
        text: __('Name, Email, or Donor ID', 'give'),
        ariaLabel: __('Search donors', 'give'),
    },
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: filterByOptions,
    }
];

const donorsBulkActions: Array<BulkActionsConfig> = [
    {
        label: __('Delete', 'give'),
        value: 'delete',
        type: 'danger',
        isVisible: (data, parameters) => parameters?.status?.includes('trash'),
        action: async (selected) => {
            const deleteDonations = document.querySelector('#giveDonorsTableDeleteDonations') as HTMLInputElement;
            const args = {ids: selected.join(','), deleteDonationsAndRecords: deleteDonations.checked};
            const response = await API.fetchWithArgs('/delete', args, 'DELETE');
            return response;
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Are you sure you want to permamently delete the following donors?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((id, index) => (
                        <li key={id}>
                            <Interweave attributes={{className: 'donorBulkModalContent'}} content={names[index]} />
                        </li>
                    ))}
                </ul>
                <br></br>
                <label htmlFor="giveDonorsTableDeleteDonations">
                    <input id="giveDonorsTableDeleteDonations" type="checkbox" defaultChecked={true} />
                    {__('Delete all associated donations and records', 'give')}
                </label>
            </>
        ),
    },
    {
        label: __('Trash', 'give'),
        value: 'trash',
        type: 'warning',
        isVisible: (data, parameters) => parameters?.status?.includes('active'),
        action: async (selected) => {
            const response = await API.fetchWithArgs('/status', {ids: selected.join(','), status: 'trash'}, 'POST');
            return response;
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Are you sure you want add to trash the following donors?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((id, index) => (
                        <li key={id}>
                            <Interweave attributes={{className: 'donorBulkModalContent'}} content={names[index]} />
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
    {
        label: __('Restore', 'give'),
        value: 'restore',
        type: 'normal',
        isVisible: (data, parameters) => parameters?.status?.includes('trash'),
        action: async (selected) => {
            const response = await API.fetchWithArgs('/status', {ids: selected.join(','), status: 'active'}, 'POST');
            return response;
        },
        confirm: (selected, names) => (
            <>
                <p>{__('Are you sure you want remove from trash the following donors?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((id, index) => (
                        <li key={id}>
                            <Interweave attributes={{className: 'donorBulkModalContent'}} content={names[index]} />
                        </li>
                    ))}
                </ul>
            </>
        ),
    },
];

/**
 * Displays a blank slate for the Donors table.
 * @since 2.27.0
 */
const ListTableBlankSlate = (
    <BlankSlate
        imagePath={`${window.GiveDonors.pluginUrl}build/assets/dist/images/list-table/blank-slate-donor-icon.svg`}
        description={__('No donors found', 'give')}
        href={'https://docs.givewp.com/donors'}
        linkText={__('GiveWP Donors.', 'give')}
    />
);

/**
 * @since 2.27.1
 */
const RecommendationConfig: any = {
    feeRecovery: {
        enum: 'givewp_donors_fee_recovery_recommendation_dismissed',
        documentationPage: 'https://docs.givewp.com/feerecovery-donors-list',
        message: __(
            ' 90% of donors opt to give more to help cover transaction fees when given the opportunity. Give donors that opportunity.',
            'give'
        ),
        innerHtml: __('Get the Fee Recovery add-on today', 'give'),
    },
};

const recommendation = (
    <ProductRecommendations options={[RecommendationConfig.feeRecovery]} apiSettings={window.GiveDonors} />
);

/**
 * Configuration for the statistic tiles rendered above the ListTable.
 *
 * IMPORTANT: Object keys MUST MATCH the keys returned by the API's `stats` payload.
 * For example, if the API returns:
 *
 *   data.stats = {
 *     donorsCount: number;
 *     oneTimeDonorsCount: number;
 *     recurringDonationsCount: number;
 *   }
 *
 * then this config must use those same keys: "donorsCount", "oneTimeDonorsCount", "subscribersCount".
 * Missing or mismatched keys will result in empty/undefined values in the UI.
 *
 * @unreleased
 */
const statsConfig: Record<string, StatConfig> = {
    donorsCount: { label: __('Number of Donors', 'give')},
    oneTimeDonorsCount: { label: __('One-Time Donors', 'give')},
    subscribersCount: {
        label: __('Subscribers', 'give'),
        upgrade: !window.GiveDonors.recurringDonationsEnabled && {
            href: 'https://docs.givewp.com/subscribers-stat',
            tooltip: __('Increase your fundraising revenue by over 30% with recurring giving campaigns.', 'give')
        }
    },
};

export default function DonorsListTable() {
    return (
        <ListTablePage
            title={__('Donors', 'give')}
            singleName={__('donors', 'give')}
            pluralName={__('donors', 'give')}
            rowActions={DonorsRowActions}
            bulkActions={donorsBulkActions}
            apiSettings={window.GiveDonors}
            filterSettings={donorsFilters}
            listTableBlankSlate={ListTableBlankSlate}
            productRecommendation={recommendation}
            statsConfig={statsConfig}
        >
            <button className={`button button-tertiary ${styles.secondaryActionButton}`} onClick={showLegacyDonors}>
                {__('Switch to Legacy View', 'give')}
            </button>
        </ListTablePage>
    );
}

const showLegacyDonors = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.reload();
};
