import {useState} from 'react';
import {__} from '@wordpress/i18n';
import styles from '@givewp/components/ListTable/ListTablePage/ListTablePage.module.scss';

/**
 * @since TBD
 */
export type UpgradedFormTooltipProps = {
    /** Remembers that the admin dismissed the tooltip. */
    onDismiss: () => void;
};

/**
 * Tells the admin that an upgraded v2 form can be deleted, until they click "Got it".
 *
 * @since TBD Hide with component state in place of removing the element from the page.
 */
export default function UpgradedFormTooltip({onDismiss}: UpgradedFormTooltipProps) {
    const [isDismissed, setIsDismissed] = useState(false);

    if (isDismissed) {
        return null;
    }

    const dismiss = () => {
        setIsDismissed(true);
        onDismiss();
    };

    return (
        <div className={styles.tooltipContainer}>
            <div className={styles.tooltip}>
                {__(
                    'The name of this form is already associated with an upgraded form. You can safely delete this form',
                    'give'
                )}
                .
                <button type="button" className={styles.link} onClick={dismiss}>
                    {__('Got it', 'give')}
                </button>
            </div>
        </div>
    );
}
