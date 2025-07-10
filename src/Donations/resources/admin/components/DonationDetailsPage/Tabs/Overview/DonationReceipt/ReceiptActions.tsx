import { __ } from "@wordpress/i18n";
import { useState } from "react";
import useResendReceipt from '../../../../../../hooks/useResendReceipt';
import ConfirmationDialog from '@givewp/components/AdminDetailsPage/ConfirmationDialog';
import styles from "./styles.module.scss";


 /**
 * @unreleased
 */
export default function ReceiptActions() {
  const [showConfirmationDialog, setShowConfirmationDialog] = useState(false);
  const { loading, message, handleResendReceipt } = useResendReceipt();

  const handleConfirmationModal = () => {
    setShowConfirmationDialog(true);
  }

    return (
        <>
          <button className={styles.action} type="button" aria-label={__('Resend donation receipt to email', 'give')} onClick={handleConfirmationModal} disabled={loading}>
            {loading ? __('Resending...', 'give') : message}
          </button>
          <ConfirmationDialog
                variant="regular"
                title={__('Resend Receipt', 'give')}
                actionLabel={__('Resend Receipt', 'give')}
                isOpen={showConfirmationDialog}
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={()=>{handleResendReceipt(); setShowConfirmationDialog(false)}}
            >
                {__('Are you sure you want to resend the donation receipt?', 'give')}
            </ConfirmationDialog>
        </>
    );
}
