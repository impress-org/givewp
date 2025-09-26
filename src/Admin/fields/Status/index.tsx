import { __ } from "@wordpress/i18n";
import cx from "classnames";
import { AdminSectionField } from "@givewp/components/AdminDetailsPage/AdminSection";
import Notice from "@givewp/admin/components/Notices";
import { useFormContext, useFormState } from "react-hook-form";
import styles from "./styles.module.scss";

/**
 * @since 4.10.0
 */
export default function Status({statusOptions}: {statusOptions: Record<string, string>}) {
    const {register, watch} = useFormContext();
    const {errors} = useFormState();
    const {isDirty, dirtyFields} = useFormState();
    const isStatusDirty = isDirty && dirtyFields?.status;
    const status = watch('status');

    return (
        <AdminSectionField error={errors.status?.message as string}>
            <label htmlFor="status">{__('Status', 'give')}</label>
            <div className={cx(styles.statusSelect, styles[`statusSelect--${status}`])}>
                <select id="status" className={styles.statusSelectInput} {...register('status')}>
                    {statusOptions && (
                        Object.entries(statusOptions).map(([value, label]) => (
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
    );
}
