import styles from './style.module.scss';
import {ActionContainer} from '../types';
import EditablePenIcon from '@givewp/components/AdminUI/Icons/EditablePenIcon';

export default function ActionContainer({label, value, type, showEditDialog}: ActionContainer) {
    return (
        <button type={'button'} onClick={showEditDialog} className={styles.actionContainer}>
            <span aria-label={label}>
                {label}
                {showEditDialog && <EditablePenIcon />}
            </span>
            <span className={styles[type]}>{value}</span>
        </button>
    );
}
