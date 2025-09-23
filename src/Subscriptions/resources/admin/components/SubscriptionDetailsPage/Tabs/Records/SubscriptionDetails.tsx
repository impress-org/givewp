import { getSubscriptionOptionsWindowData } from "@givewp/subscriptions/utils";
import { useFormContext, useFormState } from "react-hook-form";
import { __ } from "@wordpress/i18n";
import AdminSection, { AdminSectionField } from "@givewp/components/AdminDetailsPage/AdminSection";
import cx from "classnames";
import styles from "./styles.module.scss";
import Notice from '@givewp/admin/components/Notices';

/**
 * @unreleased
 */
export default function SubscriptionDetails() {
    const {errors, isDirty, dirtyFields} = useFormState();
    const {register, watch} = useFormContext();
    const {subscriptionStatuses} = getSubscriptionOptionsWindowData();
    const status = watch('status');
    const isStatusDirty = isDirty && dirtyFields?.status;

    return (
        <AdminSection
            title={__('Subscription details', 'give')}
            description={__('This includes the subscription information', 'give')}
        >
            <AdminSectionField error={errors.status?.message as string}>
                <label htmlFor="status">{__('Status', 'give')}</label>
                <div className={cx(styles.statusSelect, styles[`statusSelect--${status}`])}>
                    <select id="status" className={styles.statusSelectInput} {...register('status')}>
                        {subscriptionStatuses && (
                            Object.entries(subscriptionStatuses).map(([value, label]) => (
                                <option key={value} value={value}>
                                    {label as string}
                                </option>
                            ))
                        )}
                    </select>
                </div>

                {isStatusDirty && (
                    <Notice type="info" className={styles.notice}>
                        {__('This will not change the status at the gateway.', 'give')}
                    </Notice>
                )}
            </AdminSectionField>
        </AdminSection>
    );
}
