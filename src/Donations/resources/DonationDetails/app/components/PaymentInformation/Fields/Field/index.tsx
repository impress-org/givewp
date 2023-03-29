import EditablePenIcon from '@givewp/components/AdminUI/Icons/EditablePenIcon';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */
type FieldProps = {
    label: string;
    children: React.ReactNode;
    editable?: boolean;
    onEdit?: () => void;
};

export default function Field({label, children, editable, onEdit}: FieldProps) {
    return (
        <div className={styles.field}>
            <div className={styles.labelContainer}>
                <span aria-label={label} onClick={() => editable && onEdit && onEdit()}>
                    {label}
                </span>
                {editable && (
                    <button type="button" className={styles.button} onClick={() => onEdit && onEdit()}>
                        <EditablePenIcon />
                    </button>
                )}
            </div>
            {children}
        </div>
    );
}

/**
 *
 * @unreleased
 */
export function CurrencyField({label, children, editable, onEdit}: FieldProps) {
    return (
        <Field label={label} editable={editable} onEdit={onEdit}>
            <span className={styles.amount}>{children}</span>
        </Field>
    );
}
