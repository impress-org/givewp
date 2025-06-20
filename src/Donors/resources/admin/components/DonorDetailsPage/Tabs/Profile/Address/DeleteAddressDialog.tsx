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
import { DonorAddress } from '../../../../types';
import FormattedAddress from './FormattedAddress';

/**
 * @since 4.4.0
 */
interface DeleteAddressDialogProps {
    isOpen: boolean;
    address: DonorAddress;
    addressIndex: number;
    handleClose: () => void;
    handleConfirm: () => void;
}

/**
 * @since 4.4.0
 */
export default function DeleteAddressDialog({
    isOpen,
    address,
    addressIndex,
    handleClose,
    handleConfirm,
}: DeleteAddressDialogProps) {
    const contentId = 'delete-address-content';

    if (!address) {
        return null;
    }

    return (
        <ModalDialog
            icon={<ErrorIcon />}
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleClose}
            title={__('Delete address', 'give')}
            aria-describedby={contentId}
        >
            <div className={`${styles.dialog} ${styles.deleteAddressDialog}`} role="alertdialog">
                <div className={styles.content}>
                    <p id={contentId} className={styles.text}>
                        {createInterpolateElement(
                            sprintf(__('Are you sure you want to delete Billing Address <strong>%s</strong>?', 'give'), addressIndex + 1),
                            {
                                strong: <strong />,
                            }
                        )}
                    </p>
                    <div className={styles.address} role="group" aria-label={__('Address to be deleted', 'give')}>
                        <FormattedAddress address={address} />
                    </div>
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
                        aria-label={sprintf(__('Confirm deletion of Billing Address %s', 'give'), addressIndex + 1)}
                    >
                        {__('Delete billing address', 'give')}
                    </button>
                </div>
            </div>
        </ModalDialog>
    );
}
