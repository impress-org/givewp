import styles from './style.module.scss';
import {ActionContainer} from '../types';
import EditablePenIcon from '@givewp/components/AdminUI/Icons/EditablePenIcon';

export default function ActionContainer({label, value, type, formField, showEditDialog}: ActionContainer) {
    return (
        <div className={styles.actionContainer}>
            <button type={'button'} onClick={showEditDialog}>
                <span aria-label={label}>
                    {label}
                    {showEditDialog && <EditablePenIcon />}
                </span>
                <span className={styles[type]}>{value}</span>
            </button>
            {formField && formField}
        </div>
    );
}
