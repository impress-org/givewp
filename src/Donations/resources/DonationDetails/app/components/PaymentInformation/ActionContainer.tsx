import EditablePenIcon from '@givewp/components/AdminUI/Icons/EditablePenIcon';

import {ActionContainerProps} from './types';

import styles from './style.module.scss';

/**
 *
 * @unreleased
 */
export default function ActionContainer({label, value, type, formField, showEditDialog}: ActionContainerProps) {
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
