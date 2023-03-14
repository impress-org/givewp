import styles from './style.module.scss';
import {ActionContainer} from '../types';
import EditablePenIcon from '@givewp/components/AdminUI/Icons/EditablePenIcon';

export default function ActionContainer({label, value, type, formField, showEditDialog}: ActionContainer) {
    return (
        <div className={styles.actionContainer}>
            {showEditDialog ? (
                <button className={styles.buttonLabelContainer} type={'button'} onClick={showEditDialog}>
                    <span aria-label={label}>
                        {label}
                        {showEditDialog && <EditablePenIcon />}
                    </span>
                    <span className={styles[type]}>{!formField && value}</span>
                </button>
            ) : (
                <div className={styles.labelContainer}>
                    <span aria-label={label}>{label}</span>
                    <span className={styles[type]}>{!formField && value}</span>
                </div>
            )}
            {formField && formField}
        </div>
    );
}
