import EditablePenIcon from '@givewp/components/AdminUI/Icons/EditablePenIcon';

import {ActionContainerProps} from '../types';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */
export default function ActionContainer({label, display, type, formField, showEditDialog}: ActionContainerProps) {
    const handleDialog = (event) => {
        event.preventDefault;
        showEditDialog();
    };
    return (
        <div className={styles.actionContainer}>
            {showEditDialog ? (
                <button className={styles.buttonLabelContainer} type={'button'} onClick={handleDialog}>
                    <span aria-label={label}>
                        {label}
                        {showEditDialog && <EditablePenIcon />}
                    </span>
                    <span className={styles[type]}>{!formField && display}</span>
                </button>
            ) : (
                <div className={styles.labelContainer}>
                    <span aria-label={label}>{label}</span>
                    <span className={styles[type]}>{!formField && display}</span>
                </div>
            )}
            {formField && formField}
        </div>
    );
}
