import {__} from '@wordpress/i18n'
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {ErrorIcon} from './Icons';
import styles from './AdminDetailsPage.module.scss'

/**
 * @since 4.4.0
 */
export default function ConfirmationDialog({
    isOpen,
    title,
    handleClose,
    handleConfirm,
    className,
    actionLabel,
    children,
}: {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: () => void;
    title: string;
    className?: string;
    actionLabel: string;
    children: React.ReactNode;
}) {
    return (
        <ModalDialog
            icon={<ErrorIcon />}
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={title}
            wrapperClassName={className}
        >
            <>
                <div className={styles.archiveDialogContent}>
                    {children}
                </div>
                <div className={styles.archiveDialogButtons}>
                    <button
                        className={styles.cancelButton}
                        onClick={handleClose}

                    >
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        className={styles.confirmButton}
                        onClick={handleConfirm}
                    >
                        {actionLabel}
                    </button>
                </div>
            </>
        </ModalDialog>
    );
}
