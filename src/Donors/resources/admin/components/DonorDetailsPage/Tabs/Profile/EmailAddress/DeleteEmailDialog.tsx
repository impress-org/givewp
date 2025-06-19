/**
 * WordPress Dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { createInterpolateElement } from '@wordpress/element';

/**
 * Internal Dependencies
 */
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import { ErrorIcon } from '@givewp/components/AdminDetailsPage/Icons';
import styles from './styles.module.scss';

/**
 * @since 4.4.0
 */
interface DeleteEmailDialogProps {
    isOpen: boolean;
    emailAddress: string;
    handleClose: () => void;
    handleConfirm: () => void;
}

/**
 * @since 4.4.0
 */
export default function DeleteEmailDialog({
    isOpen,
    emailAddress,
    handleClose,
    handleConfirm,
}: DeleteEmailDialogProps) {
    const contentId = 'delete-email-content';

    return (
        <ModalDialog
            icon={<ErrorIcon />}
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={__('Delete email address', 'give')}
            aria-describedby={contentId}
        >
            <div className={`${styles.dialog} ${styles.deleteEmailDialog}`} role="alertdialog">
                <div className={styles.content}>
                    <p id={contentId} className={styles.text}>
                        {createInterpolateElement(
                            sprintf(__('Are you sure you want to delete <strong>%s</strong>?', 'give'), emailAddress),
                            {
                                strong: <strong />,
                            }
                        )}
                    </p>
                </div>

                <div className={styles.buttons}>
                    <button
                        className={styles.cancelButton}
                        onClick={handleClose}
                        aria-label={__('Cancel deletion', 'give')}
                    >
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        className={styles.confirmButton}
                        onClick={handleConfirm}
                        aria-describedby={contentId}
                        aria-label={sprintf(__('Confirm deletion of %s', 'give'), emailAddress)}
                    >
                        {__('Delete email address', 'give')}
                    </button>
                </div>
            </div>
        </ModalDialog>
    );
}
