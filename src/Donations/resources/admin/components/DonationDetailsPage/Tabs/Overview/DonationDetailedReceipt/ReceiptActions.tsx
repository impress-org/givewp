import { __ } from "@wordpress/i18n";
import styles from "./styles.module.scss";

export default function ReceiptActions() {
    return (
        <>
         <button type="button" aria-label={__('Download donation receipt', 'give')}>
            {__('Download receipt', 'give')}
          </button>
          <button type="button" aria-label={__('Resend donation receipt to email', 'give')}>
            {__('Resend receipt', 'give')}
          </button>
        </>
    );
}