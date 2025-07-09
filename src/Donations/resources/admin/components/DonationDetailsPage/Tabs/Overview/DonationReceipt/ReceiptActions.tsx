import { __ } from "@wordpress/i18n";
import styles from "./styles.module.scss";

/**
 * @unreleased
 */
export default function ReceiptActions() {
    return (
        <>
         <button className={styles.actionButton} type="button" aria-label={__('Download donation receipt', 'give')}>
            {__('Download receipt', 'give')}
          </button>
          <button className={styles.actionButton} type="button" aria-label={__('Resend donation receipt to email', 'give')}>
            {__('Resend receipt', 'give')}
          </button>
        </>
    );
}