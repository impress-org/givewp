/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import { AddressBlankSlateIcon } from "@givewp/components/AdminDetailsPage/Icons";
import styles from './styles.module.scss';

/**
 * @since 4.4.0
 */
export default function BlankSlate() {
    return (
        <div className={styles.blankSlate}>
            <AddressBlankSlateIcon />
            <p className={styles.description}>
                {__('This donor does not have any address saved.', 'give')}
            </p>
        </div>
    );
}
