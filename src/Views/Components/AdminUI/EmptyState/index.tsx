import EmptyStateIcon from '@givewp/components/AdminUI/Icons/EmptyStateIcon';

import styles from './style.module.scss';

export type EmptyStateProps = {
    message: string;
};

export default function EmptyState({message}: EmptyStateProps) {
    return (
        <div className={styles.emptyState}>
            <EmptyStateIcon />
            {message}
        </div>
    );
}
