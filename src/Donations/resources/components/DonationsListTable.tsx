import {__} from '@wordpress/i18n';
import {ListTablePage} from '@givewp/components';
import {DonationRowActions} from './DonationRowActions';
import ListTableApi from '@givewp/components/ListTable/api';
import tableStyles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';
import styles from './ListTable.module.scss';
import {IdBadge} from '@givewp/components/ListTable/TableCell';
import {BulkActionsConfig, FilterConfig} from '@givewp/components/ListTable/ListTablePage';
import {Interweave} from 'interweave';
import BlankSlate from '@givewp/components/ListTable/BlankSlate';
import ProductRecommendations from '@givewp/components/ListTable/ProductRecommendations';
import {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';
import { StatConfig } from '@givewp/components/ListTable/ListTableStats/ListTableStats';

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
            recurringDonations: boolean;
            pluginUrl: string;
            dismissedRecommendations: Array<string>;
            addonsBulkActions: Array<BulkActionsConfig>;
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
    },
    {
        name: 'search',
        type: 'search',
        inlineSize: '14rem',
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
                <p>{__('Are you sure you want to delete the following donations?', 'give')}</p>
                <ul role="document" tabIndex={0}>
                    {selected.map((donationId, index) => (
                        <li key={donationId}>
                            <IdBadge id={donationId} />{' '}
                            <span>
                                {__('from ', 'give')} <Interweave content={names[index]} />
                            </span>
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
        action: async (selected) => await API.fetchWithArgs('/resendEmailReceipt', {ids: selected.join(',')}, 'POST'),
        confirm: (selected, names) => (
            <>
                <p>{__('Resend Email Receipts for following donations?', 'give')}</p>
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
        upgrade: !window.GiveDonations.recurringDonations && {
            href: ' https://docs.givewp.com/recurring-stat',
            tooltip: __('Increase your fundraising revenue by over 30% with recurring giving campaigns.', 'give')
        }
    },
};

/**
 * @since 4.10.0 Update button class names and add aria attributes.
 * @since 2.24.0
 */
export default function DonationsListTable() {
    return (
        <ListTablePage
            title={__('Donations', 'give')}
            singleName={__('donation', 'give')}
            pluralName={__('donations', 'give')}
            rowActions={DonationRowActions}
            bulkActions={bulkActions}
            apiSettings={window.GiveDonations}
            filterSettings={filters}
            paymentMode={!!window.GiveDonations.paymentMode}
            listTableBlankSlate={ListTableBlankSlate}
            productRecommendation={rotatingRecommendation}
            statsConfig={statsConfig}
        >
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
            <button
                className={`button button-tertiary ${tableStyles.secondaryActionButton}`}
                onClick={showLegacyDonations}
                aria-label={__('Switch to the legacy donations table view', 'give')}
            >
                {__('Switch to Legacy View', 'give')}
            </button>
        </ListTablePage>
    );
}

const showLegacyDonations = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.reload();
};
