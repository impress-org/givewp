import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import {donationRowActions, isDonationsTrashView} from './DonationRowActions';
import SetDonationStatusDialog from './SetDonationStatusDialog';
import ListTableApi from '@givewp/components/ListTable/api';
import tableStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import styles from './ListTable.module.scss';
import {BulkActionsConfig, FilterConfig, ListTableRowAction} from '@givewp/components/ListTable/ListTablePage';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';
import ProductRecommendations from '@givewp/components/ListTable/ProductRecommendations';
import {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';
import { StatConfig } from '@givewp/components/ListTable/ListTableStats/ListTableStats';
import filterByOptions from '../constants/filterByOptions';

declare global {
    interface Window {
        GiveDonations: {
            apiNonce: string;
            apiRoot: string;
            adminUrl: string;
            campaigns: Array<{value: string; text: string}>;
            table: {columns: Array<object>};
            paymentMode: boolean;
            manualDonations: boolean;
            recurringDonationsEnabled: boolean;
            pluginUrl: string;
            dismissedRecommendations: Array<string>;
            addonsBulkActions: Array<BulkActionsConfig>;
            donationStatuses: {[statusCode: string]: string};
        };
    }
}

const API = new ListTableApi(window.GiveDonations);

const filters: Array<FilterConfig> = [
    {
        name: 'campaignId',
        type: 'campaignselect',
        text: __('Select Campaign', 'give'),
        ariaLabel: __('filter donations by campaign', 'give'),
        options: [{value: 'none', text: __('No campaign', 'give')}],
    },
    {
        name: 'formId',
        type: 'formselect',
        text: __('Select Form', 'give'),
        ariaLabel: __('filter donations by form', 'give'),
    },
    {
        name: 'search',
        type: 'search',
        text: __('Name, Email, or Donation ID', 'give'),
        ariaLabel: __('search donations', 'give'),
    },
    {
        name: 'toggle',
        type: 'checkbox',
        text: __('Test', 'give'),
        ariaLabel: __('View Test Donations', 'give'),
    },
    {
        name: 'donor',
        type: 'hidden',
        text: __('Donor', 'give'),
    },
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: filterByOptions,
    },
];

/**
 * The Donations actions: the ones shared with the subscription details tab, plus "Set status" and
 * "Resend Email Receipts" for rows and selections.
 *
 * @since TBD Declare the actions as data; one "Set status" replaces the nine "Set To …" bulk actions.
 */
const rowActions: Array<ListTableRowAction> = [
    ...donationRowActions(API, window.GiveDonations.adminUrl),
    {
        id: 'set-status',
        label: __('Set status', 'give'),
        supportsBulk: true,
        isVisible: (data, parameters) => !isDonationsTrashView(parameters),
        Dialog: (props) => (
            <SetDonationStatusDialog {...props} api={API} statuses={window.GiveDonations.donationStatuses ?? {}} />
        ),
    },
    {
        id: 'resend-email-receipts',
        label: __('Resend Email Receipts', 'give'),
        supportsBulk: true,
        isVisible: (data, parameters) => !isDonationsTrashView(parameters),
        confirm: {
            title: __('Resend Email Receipts', 'give'),
            body: () => <p>{__('Resend Email Receipts for following donations?', 'give')}</p>,
            confirmLabel: __('Resend', 'give'),
        },
        run: (items) => API.fetchWithArgs('/resendEmailReceipt', {ids: items.map((item) => item.id).join(',')}, 'POST'),
    },
];

/**
 * Displays a blank slate for the Donations table.
 * @since 2.27.0
 */
const ListTableBlankSlate = (
    <BlankSlate
        imagePath={`${window.GiveDonations.pluginUrl}build/assets/dist/images/list-table/blank-slate-donations-icon.svg`}
        description={__('No donations found', 'give')}
        href={'https://docs.givewp.com/donations'}
        linkText={__('GiveWP Donations.', 'give')}
    />
);

interface DonationTableRecommendations {
    recurring: RecommendedProductData;
    feeRecovery: RecommendedProductData;
    designatedFunds: RecommendedProductData;
}

/**
 * @since 2.27.1
 */
const RecommendationConfig: DonationTableRecommendations = {
    recurring: {
        enum: 'givewp_donations_recurring_recommendation_dismissed',
        documentationPage: 'https://docs.givewp.com/recurring-donations-list',
        message: __('Increase your fundraising revenue by over 30% with recurring giving campaigns.', 'give'),
        innerHtml: __('Get More Donations', 'give'),
    },
    feeRecovery: {
        enum: 'givewp_donations_fee_recovery_recommendation_dismissed',
        documentationPage: 'https://docs.givewp.com/feerecovery-donations-list',
        message: __(
            'Raise more money per donation by providing donors with the option to help cover the credit card processing fees.',
            'give'
        ),
        innerHtml: __('Get Fee Recovery', 'give'),
    },
    designatedFunds: {
        enum: 'givewp_donations_designated_funds_recommendation_dismissed',
        documentationPage: 'https://docs.givewp.com/funds-donations-list',
        message: __(
            'Elevate your fundraising campaigns with unlimited donation fund designations, and tailored fundraising reports.',
            'give'
        ),
        innerHtml: __('Start creating designated funds', 'give'),
    },
};

const rotatingRecommendation = (
    <ProductRecommendations
        options={[
            RecommendationConfig.recurring,
            RecommendationConfig.feeRecovery,
            RecommendationConfig.designatedFunds,
        ]}
        apiSettings={window.GiveDonations}
    />
);

/**
 * Configuration for the statistic tiles rendered above the ListTable.
 *
 * IMPORTANT: Object keys MUST MATCH the keys returned by the API's `stats` payload.
 * For example, if the API returns:
 *
 *   data.stats = {
 *     donationsCount: number;
 *     oneTimeDonationsCount: number;
 *     recurringDonationsCount: number;
 *   }
 *
 * then this config must use those same keys: "donationsCount", "oneTimeDonationsCount", "recurringDonationsCount".
 * Missing or mismatched keys will result in empty/undefined values in the UI.
 *
 * @since 4.10.0
 */
const statsConfig: Record<string, StatConfig> = {
    donationsCount: { label: __('Total Donations', 'give')},
    oneTimeDonationsCount: { label: __('One-Time Donations', 'give')},
    recurringDonationsCount: {
        label: __('Recurring Donations', 'give'),
        upgrade: !window.GiveDonations.recurringDonationsEnabled && {
            href: 'https://docs.givewp.com/recurring-stat',
            tooltip: __('Increase your fundraising revenue by over 30% with recurring giving campaigns.', 'give')
        }
    },
};

/**
 * @since TBD Pass the actions as data, and the add-ons' bulk actions (the shared list page no longer reads them).
 * @since 4.10.0 Update button class names and add aria attributes.
 * @since 2.24.0
 */
export default function DonationsListTable() {
    return (
        <ListTablePage
            title={__('Donations', 'give')}
            singleName={__('donation', 'give')}
            pluralName={__('donations', 'give')}
            rowActions={rowActions}
            // Add-ons add their bulk actions to `addonsBulkActions` before the screen mounts.
            bulkActions={window.GiveDonations.addonsBulkActions ?? []}
            apiSettings={window.GiveDonations}
            filterSettings={filters}
            paymentMode={!!window.GiveDonations.paymentMode}
            listTableBlankSlate={ListTableBlankSlate}
            productRecommendation={rotatingRecommendation}
            statsConfig={statsConfig}
        >
            <button
                className={`button button-tertiary ${tableStyles.secondaryActionButton}`}
                onClick={showLegacyDonations}
                aria-label={__('Switch to the legacy donations table view', 'give')}
            >
                {__('Switch to Legacy View', 'give')}
            </button>
            {window.GiveDonations.manualDonations ? (
                <a
                    className={`button button-tertiary ${tableStyles.secondaryActionButton}`}
                    href={`${window.GiveDonations.adminUrl}edit.php?post_type=give_forms&page=give-manual-donation`}
                    aria-label={__('Create a new donation record', 'give')}
                >
                    {__('New donation', 'give')}
                </a>
            ) : (
                <a
                    className={`button button-tertiary ${tableStyles.secondaryActionButton} ${styles.manualDonationsNotice}`}
                    href={'https://docs.givewp.com/enterdonation'}
                    target={'_blank'}
                    aria-label={__('Learn about Manual Donations add-on (opens in new tab)', 'give')}
                    aria-describedby="manual-donations-tooltip"
                >
                    <span className={styles.manualDonationsAddOn}>{__('ADD-ON', 'give')}</span>
                    {__('Enter donations', 'give')}
                    <span id="manual-donations-tooltip" className={styles.manualDonationsMessage}>
                        <img
                            src={`${window.GiveDonations.pluginUrl}build/assets/dist/images/admin/triangle-tip.svg`}
                            alt={__('Information', 'give')}
                        />{' '}
                        {__(
                            'Need to add in a record for a donation received elsewhere, or reconcile with the payment gateway? Add donation records with the Manual Donations add-on!',
                            'give'
                        )}
                    </span>
                </a>
            )}
            <a
                className={`button button-primary ${tableStyles.primaryActionButton}`}
                href={` ${window.GiveDonations.adminUrl}edit.php?post_type=give_forms&page=give-tools&tab=import&importer-type=import_donations`}
                aria-label={__('Import donations from external source', 'give')}
            >
                {__('Import donations', 'give')}
            </a>
        </ListTablePage>
    );
}

const showLegacyDonations = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.reload();
};
