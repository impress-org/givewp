import { __ } from "@wordpress/i18n";
import { ListTablePage } from "@givewp/components";
import { getSubscriptionOptionsWindowData } from "@givewp/subscriptions/utils";
import styles from './styles.module.scss';
import cn from 'classnames';
import { DonationRowActions } from "@givewp/donations/components/DonationRowActions";
import BlankSlate from "@givewp/components/ListTable/BlankSlate";
import apiSettings from './apiSettings';

const { mode, adminUrl, pluginUrl } = getSubscriptionOptionsWindowData();

// This is necessary to reuse the DonationRowActions component
// that expects the window.GiveDonations object to be present
// while avoiding to expose the entire window.GiveDonations object.
if (!window?.GiveDonations) {
    // @ts-ignore
    window.GiveDonations = {
        adminUrl,
    };
}

/**
 * @unreleased
 */
const ListTableBlankSlate = (
    <BlankSlate
        imagePath={`${pluginUrl}build/assets/dist/images/list-table/blank-slate-donations-icon.svg`}
        description={__('No donations found', 'give')}
        href={'https://docs.givewp.com/donations'}
        linkText={__('GiveWP Donations.', 'give')}
    />
);

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
                    listTableBlankSlate={ListTableBlankSlate}
                    singleName={__('result', 'give')}
                    pluralName={__('results', 'give')}
                    paymentMode={mode === 'test'}
                    contentMode={true}
                    perPage={10}
                    rowActions={DonationRowActions}
                />
            </div>
        </div>
    );
}
