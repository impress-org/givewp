import { __ } from "@wordpress/i18n";
import { useState, useEffect } from "react";
import useResendReceipt from '../../../../../../hooks/useResendReceipt';
import ConfirmationDialog from '@givewp/components/AdminDetailsPage/ConfirmationDialog';
import styles from "./styles.module.scss";


/**
 * @since 4.6.0
 */
export default function ReceiptActions() {
  const [showConfirmationDialog, setShowConfirmationDialog] = useState(false);
  const { loading, hasResolved, message, handleResendReceipt } = useResendReceipt();

  useEffect(() => {
    if (hasResolved) {
      setShowConfirmationDialog(false);
    }
  }, [hasResolved]);


  const handleConfirmationModal = () => {
    setShowConfirmationDialog(true);
  }

    return (
        <>
          <button className={styles.action} type="button" aria-label={__('Resend donation receipt to email', 'give')} onClick={handleConfirmationModal} disabled={loading}>
            {__('Resend receipt', 'give')}
          </button>
          <ConfirmationDialog
                variant="regular"
                title={__('Resend Receipt', 'give')}
                actionLabel={message}
                isOpen={showConfirmationDialog}
                handleClose={() => setShowConfirmationDialog(null)}
                handleConfirm={()=>{handleResendReceipt()}}
                isConfirming={loading}
                spinner={'arc'}
            >
                {__('Are you sure you want to resend the donation receipt?', 'give')}
          </ConfirmationDialog>
        </>
    );
}
