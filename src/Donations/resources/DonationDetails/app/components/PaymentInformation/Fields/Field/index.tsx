import EditablePenIcon from '@givewp/components/AdminUI/Icons/EditablePenIcon';

import {FieldProps} from '../types';

import styles from './style.module.scss';

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

export function CurrencyField({label, children, editable, onEdit}: FieldProps) {
    return (
        <Field label={label} editable={editable} onEdit={onEdit}>
            <span className={styles.amount}>{children}</span>
        </Field>
    );
}
