import {useState} from 'react';
import {__} from '@wordpress/i18n';
import {Button} from '@wordpress/components';
import Balloon from '@givewp/components/ListTable/Balloon';
import styles from './DonationFormsListTable.module.scss';

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
 * @since TBD Hide with component state, and render through the shared Balloon with a stock "Got it" button.
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
            <Balloon
                className={styles.tooltip}
                actions={
                    <Button variant="secondary" isSmall onClick={dismiss}>
                        {__('Got it', 'give')}
                    </Button>
                }
            >
                {__(
                    'The name of this form is already associated with an upgraded form. You can safely delete this form',
                    'give'
                )}
                .
            </Balloon>
        </div>
    );
}
