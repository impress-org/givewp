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

declare global {
    interface Window {
        GiveDonations: {
            apiNonce: string;
            apiRoot: string;
            adminUrl: string;
            forms: Array<{value: string; text: string}>;
            table: {columns: Array<object>};
            paymentMode: boolean;
            manualDonations: boolean;
            pluginUrl: string;
            dismissedRecommendations: Array<string>;
            addonsBulkActions: Array<BulkActionsConfig>;
        };
    }
}

const API = new ListTableApi(window.GiveDonations);

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
    {
        name: 'toggle',
        type: 'checkbox',
        text: __('Test', 'give'),
        ariaLabel: __('View Test Donations', 'give'),
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
        imagePath={`${window.GiveDonations.pluginUrl}/assets/dist/images/list-table/blank-slate-donations-icon.svg`}
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
        >
            {window.GiveDonations.manualDonations ? (
                <a
                    className={tableStyles.addFormButton}
                    href={`${window.GiveDonations.adminUrl}edit.php?post_type=give_forms&page=give-manual-donation`}
                >
                    {__('New Donation', 'give')}
                </a>
            ) : (
                <a
                    className={styles.manualDonationsNotice}
                    href={'https://docs.givewp.com/enterdonation'}
                    target={'_blank'}
                >
                    <span className={styles.manualDonationsAddOn}>{__('ADD-ON', 'give')}</span>
                    {__('Enter Donations', 'give')}
                    <span className={styles.manualDonationsMessage}>
                        <img
                            src={`${window.GiveDonations.pluginUrl}/assets/dist/images/admin/triangle-tip.svg`}
                            alt={'manual donations'}
                        />{' '}
                        {__(
                            'Need to add in a record for a donation received elsewhere, or reconcile with the payment gateway? Add donation records with the Manual Donations add-on!',
                            'give'
                        )}
                    </span>
                </a>
            )}
            <a
                className={tableStyles.addFormButton}
                href={` ${window.GiveDonations.adminUrl}edit.php?post_type=give_forms&page=give-tools&tab=import&importer-type=import_donations`}
            >
                {__('Import Donations', 'give')}
            </a>
            <button className={tableStyles.addFormButton} onClick={showLegacyDonations}>
                {__('Switch to Legacy View', 'give')}
            </button>
        </ListTablePage>
    );
}

const showLegacyDonations = async (event) => {
    await API.fetchWithArgs('/view', {isLegacy: 1});
    window.location.reload();
};
