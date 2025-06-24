/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";
import { CustomFieldsBlankSlateIcon } from '@givewp/components/AdminDetailsPage/Icons';

/**
 * Internal Dependencies
 */
import styles from './styles.module.scss';

/**
 * @since 4.4.0
 */
export default function BlankSlate() {
    return (
        <div className={styles.blankSlate}>
            <CustomFieldsBlankSlateIcon />
            <p className={styles.description}>
                {__('No custom fields added yet', 'give')}
            </p>
        </div>
    );
}
