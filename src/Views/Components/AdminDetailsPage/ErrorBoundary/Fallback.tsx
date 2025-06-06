import {__} from "@wordpress/i18n";
import styles from "./styles.module.scss";

/**
 * @unrestricted
 */
export default function Fallback({error, resetErrorBoundary}) {
    return (
        <div role="alert" className={styles.errorBoundary}>
            <p  className={styles.errorBoundaryParagraph}>
                {__(
                    'An error occurred. The error message is:',
                    'give'
                )}
            </p>
            <pre className={styles.errorBoundaryPre}>{error.message}</pre>
            <button type="button" onClick={resetErrorBoundary} className={styles.errorBoundaryButton}>
                {__('Reload page', 'give')}
            </button>
        </div>
    );
}
