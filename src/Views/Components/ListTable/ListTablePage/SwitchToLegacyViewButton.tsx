import {__} from '@wordpress/i18n';
import type ListTableApi from '../api';
import styles from './ListTablePage.module.scss';

/**
 * @since TBD
 */
export type SwitchToLegacyViewButtonProps = {
    api: ListTableApi;
    /** Where to go once the preference is saved. Reloads the page by default. */
    onSwitched?: () => void;
    ariaLabel?: string;
    className?: string;
};

/**
 * Saves the admin's choice of the legacy screen, through the list endpoint's `/view` action, then
 * shows it.
 *
 * @since TBD
 */
export default function SwitchToLegacyViewButton({
    api,
    onSwitched = () => window.location.reload(),
    ariaLabel,
    className = `button button-tertiary ${styles.secondaryActionButton}`,
}: SwitchToLegacyViewButtonProps) {
    const switchView = async () => {
        await api.fetchWithArgs('/view', {isLegacy: 1});
        onSwitched();
    };

    return (
        <button type="button" className={className} onClick={switchView} aria-label={ariaLabel}>
            {__('Switch to Legacy View', 'give')}
        </button>
    );
}
