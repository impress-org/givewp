import { __ } from "@wordpress/i18n";
import { ListTablePage } from "@givewp/components";
import { getSubscriptionOptionsWindowData } from "@givewp/subscriptions/utils";
import styles from './styles.module.scss';
import cn from 'classnames';

const { legacyApiRoot, apiNonce, mode } = getSubscriptionOptionsWindowData();

const urlParams = new URLSearchParams(window.location.search);
const subscriptionId = urlParams.get('id');

const apiSettings = {
    apiRoot: `${legacyApiRoot}/donations?subscriptionId=${subscriptionId}`,
    apiNonce,
    table: {
        columns: [
            {
                id: 'id',
                label: 'ID',
                sortable: true,
                visible: true,
            },
            {
                id: 'subscriptionDonationType',
                label: 'Type',
                sortable: false,
                visible: true,
            },
            {
                id: 'campaign',
                label: 'Campaign',
                sortable: false,
                visible: true,
            },
            {
                id: 'createdAt',
                label: 'Date',
                sortable: true,
                visible: true,
            },
            {
                id: 'status',
                label: 'Status',
                sortable: false,
                visible: true,
            },
            {
                id: 'amount',
                label: 'Amount',
                sortable: true,
                visible: true,
            },
        ],
        id: 'donations',
    },
};

/**
 * @unreleased
 */
export default function SubscriptionDetailsPageDonationsTab() {
    return (
        <div className={styles.container}>
            <h2 className={styles.title}>{__('Donations', 'give')}</h2>
            <p className={styles.description}>{__('Show all recurring donations under this subscription.', 'give')}</p>

            {/* TODO: Remove the inline classname once the new design is implemented in all list tables */}
            <div className={cn(styles.tableWrapper, 'list-table-page-container--new-design')}>
                <ListTablePage
                    title={__('Donations', 'give')}
                    apiSettings={apiSettings}
                    listTableBlankSlate={<div>No donations found</div>}
                    singleName={__('result', 'give')}
                    pluralName={__('results', 'give')}
                    paymentMode={mode === 'test'}
                    contentMode={true}
                    perPage={10}
                />
            </div>
        </div>
    );
}
