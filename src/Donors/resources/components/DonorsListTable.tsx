import {__} from '@wordpress/i18n';
import {ListTableApi, ListTablePage} from '@givewp/components';
import {donorsRowActions} from './DonorsRowActions';
import {FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import SwitchToLegacyViewButton from '@givewp/components/ListTable/ListTablePage/SwitchToLegacyViewButton';
import './style.scss';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';
import ProductRecommendations from '@givewp/components/ListTable/ProductRecommendations';
import { StatConfig } from '@givewp/components/ListTable/ListTableStats/ListTableStats';
import filterByOptions from '../constants/filterByOptions';
import listTableScreen from './listTableScreen';

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
        };
    }
}

const API = new ListTableApi(window.GiveDonors);

export const donorsFilters: Array<FilterConfig> = [
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
        text: __('Name, Email, or Donor ID', 'give'),
        ariaLabel: __('Search donors', 'give'),
    },
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: filterByOptions,
    }
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
 * @since 4.11.0
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

/**
 * @since TBD Pass the actions as data and the screen config.
 */
export default function DonorsListTable() {
    return (
        <ListTablePage
            title={__('Donors', 'give')}
            singleName={__('donors', 'give')}
            pluralName={__('donors', 'give')}
            rowActions={donorsRowActions}
            apiSettings={window.GiveDonors}
            filterSettings={donorsFilters}
            listTableBlankSlate={ListTableBlankSlate}
            productRecommendation={recommendation}
            statsConfig={statsConfig}
            screen={listTableScreen}
        >
            <SwitchToLegacyViewButton api={API} />
        </ListTablePage>
    );
}

