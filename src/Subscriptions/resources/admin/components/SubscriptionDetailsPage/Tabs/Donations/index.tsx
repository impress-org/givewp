import { __ } from "@wordpress/i18n";
import { ListTablePage } from "@givewp/components";
import type { ListTablePageRef } from "@givewp/components";
import { getSubscriptionOptionsWindowData, useRefreshSubscriptionInBackground } from "@givewp/subscriptions/utils";
import styles from './styles.module.scss';
import cn from 'classnames';
import { DonationRowActions } from "@givewp/donations/components/DonationRowActions";
import BlankSlate from "@givewp/components/ListTable/BlankSlate";
import apiSettings from './apiSettings';
import AddRenewalDialog from "./AddRenewalModal";
import { useState, useRef } from "react";
import { useDispatch } from "@wordpress/data";
import { store as coreStore } from "@wordpress/core-data";

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
 * @since 4.8.0
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
 * @since 4.8.0
 */
export default function SubscriptionDetailsPageDonationsTab() {
    const [isAddRenewalDialogOpen, setIsAddRenewalDialogOpen] = useState(false);
    const listTableRef = useRef<ListTablePageRef>(null);
    const dispatch = useDispatch(`givewp/admin-details-page-notifications`);
    const { saveEntityRecord } = useDispatch(coreStore);
    const refreshSubscriptionInBackground = useRefreshSubscriptionInBackground();
    const urlParams = new URLSearchParams(window.location.search);
    const subscriptionId = Number(urlParams.get('id'));

    const handleAddRenewal = async (data: any) => {
        try {
            const response = await saveEntityRecord('givewp', 'donation', {
                subscriptionId,
                type: 'renewal',
                amount: data.amount,
                createdAt: (new Date(data.date)).toISOString(),
                updateRenewalDate: !!data.updateRenewalDate,
                gatewayTransactionId: data.transactionId,
            });

            if (response?.id) {
                await listTableRef.current?.refresh();

                if (!!data.updateRenewalDate) {
                    await refreshSubscriptionInBackground(subscriptionId);
                }

                dispatch.addSnackbarNotice({
                    id: `add-renewal-success`,
                    content: __(`Renewal added successfully`, 'give'),
                });
            } else {
                throw new Error(response);
            }
        } catch (error) {
            console.error(error);
            dispatch.addSnackbarNotice({
                id: `add-renewal-error`,
                type: 'error',
                content: __(`Failed to add renewal`, 'give'),
            });
        }
    };

    return (
        <div className={styles.container}>
            <div className={styles.header}>
                <h2 className={styles.title}>{__('Donations', 'give')}</h2>
                <p className={styles.description}>{__('Show all recurring donations under this subscription.', 'give')}</p>
            </div>
            <button className={styles.button} onClick={() => setIsAddRenewalDialogOpen(true)}>{__('Add renewal', 'give')}</button>

            <div className={styles.tableWrapper}>
                <ListTablePage
                    ref={listTableRef}
                    title={__('Donations', 'give')}
                    apiSettings={apiSettings}
                    listTableBlankSlate={ListTableBlankSlate}
                    singleName={__('result', 'give')}
                    pluralName={__('results', 'give')}
                    paymentMode={mode === 'test'}
                    contentMode={true}
                    rowActions={DonationRowActions}
                />
            </div>

            <AddRenewalDialog
                isOpen={isAddRenewalDialogOpen}
                handleClose={() => setIsAddRenewalDialogOpen(false)}
                handleConfirm={handleAddRenewal}
            />
        </div>
    );
}
