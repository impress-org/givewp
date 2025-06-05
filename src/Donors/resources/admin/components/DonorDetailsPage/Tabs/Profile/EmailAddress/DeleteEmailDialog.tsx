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
 * @unreleased
 */
interface DeleteEmailDialogProps {
    isOpen: boolean;
    emailAddress: string;
    handleClose: () => void;
    handleConfirm: () => void;
}

/**
 * @unreleased
 */
export default function DeleteEmailDialog({
    isOpen,
    emailAddress,
    handleClose,
    handleConfirm,
}: DeleteEmailDialogProps) {
    return (
        <ModalDialog
            icon={<ErrorIcon />}
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={__('Delete email address', 'give')}
        >
            <div className={`${styles.dialog} ${styles.deleteEmailDialog}`}>
                <div className={styles.content}>
                    <p className={styles.text}>
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
                    >
                        {__('Cancel', 'give')}
                    </button>
                    <button
                        className={styles.confirmButton}
                        onClick={handleConfirm}
                    >
                        {__('Delete email address', 'give')}
                    </button>
                </div>
            </div>
        </ModalDialog>
    );
}
