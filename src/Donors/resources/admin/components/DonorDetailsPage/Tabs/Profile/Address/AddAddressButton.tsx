/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import styles from './AddAddressButton.module.scss';

interface AddAddressButtonProps {
    onAddAddress: () => void;
    variant?: 'primary' | 'secondary';
    addressCount?: number;
    ariaDescribedBy?: string;
}

/**
 * @unreleased
 */
export default function AddAddressButton({
    onAddAddress,
    variant = 'secondary',
    addressCount = 0,
    ariaDescribedBy
}: AddAddressButtonProps) {
    const handleClick = (event: React.MouseEvent<HTMLButtonElement>) => {
        event.preventDefault();
        onAddAddress();
    };

    const getAriaLabel = () => {
        if (addressCount === 0) {
            return __('Add the first address for this donor', 'give');
        }

        return `${__('Add new address. Currently', 'give')} ${addressCount} ${addressCount === 1 ? __('address', 'give') : __('addresses', 'give')} ${__('total', 'give')}`;
    };

    return (
        <button
            className={`${styles.addButton} ${styles[variant]}`}
            onClick={handleClick}
            aria-label={getAriaLabel()}
            aria-describedby={ariaDescribedBy}
        >
            {__('Add address', 'give')}
        </button>
    );
}
