/**
 * External Dependencies
 */
import { useState, useRef, useEffect } from "react";

/**
 * WordPress Dependencies
 */
import { __, sprintf } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import { DotsIcons, TrashIcon, EditIcon, SetAsPrimaryIcon } from "@givewp/components/AdminDetailsPage/Icons";
import FormattedAddress from "./FormattedAddress";
import styles from './styles.module.scss';
import { DonorAddress as DonorAddressType } from "../../../../types";

/**
 * @since 4.4.0
 */
interface AddressItemProps {
    address: DonorAddressType;
    index: number;
    totalAddresses: number;
    onEdit: (index: number) => void;
    onSetAsPrimary: (index: number) => void;
    onDelete: (index: number) => void;
}

/**
 * @since 4.4.0
 */
export default function AddressItem({
    address,
    index,
    totalAddresses,
    onEdit,
    onSetAsPrimary,
    onDelete,
}: AddressItemProps) {
    const [isDropdownOpen, setIsDropdownOpen] = useState(false);
    const dropdownRef = useRef<HTMLDivElement | null>(null);

    const isPrimary = index === 0;

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (isDropdownOpen && dropdownRef.current) {
                if (!dropdownRef.current.contains(event.target as Node)) {
                    setIsDropdownOpen(false);
                }
            }
        };

        if (isDropdownOpen) {
            document.addEventListener('mousedown', handleClickOutside);
            return () => {
                document.removeEventListener('mousedown', handleClickOutside);
            };
        }
    }, [isDropdownOpen]);

    // Handle keyboard navigation
    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape' && isDropdownOpen) {
                setIsDropdownOpen(false);
            }
        };

        if (isDropdownOpen) {
            document.addEventListener('keydown', handleKeyDown);
            return () => {
                document.removeEventListener('keydown', handleKeyDown);
            };
        }
    }, [isDropdownOpen]);

    const toggleDropdown = (event: React.MouseEvent<HTMLButtonElement>) => {
        event.preventDefault();
        setIsDropdownOpen(!isDropdownOpen);
    };

    const handleEditAction = () => {
        onEdit(index);
        setIsDropdownOpen(false);
    };

    const handleSetAsPrimaryAction = () => {
        onSetAsPrimary(index);
        setIsDropdownOpen(false);
    };

    const handleDeleteAction = () => {
        onDelete(index);
        setIsDropdownOpen(false);
    };

    return (
        <div
            className={styles.item}
            role="listitem"
            aria-label={`${__('Donor address', 'give')} ${index + 1} ${__('of', 'give')} ${totalAddresses}`}
        >
            <div className={styles.header}>
                <span
                    className={styles.address}
                    aria-label={`${__('Billing Address:', 'give')} ${index + 1}`}
                >
                    {sprintf(__('Billing Address %s', 'give'), index + 1)}
                </span>
                {isPrimary && (
                    <span
                        className={`${styles.badge} ${styles.badgePrimary}`}
                        aria-label={__('Primary address badge', 'give')}
                    >
                        {__('Primary', 'give')}
                    </span>
                )}
                <div
                    className={styles.actions}
                    ref={dropdownRef}
                >
                    <button
                        className={styles.menuTrigger}
                        aria-label={`${__('Address actions for', 'give')} ${index + 1}`}
                        aria-haspopup="menu"
                        aria-expanded={isDropdownOpen}
                        aria-controls={`address-dropdown-${index}`}
                        onClick={toggleDropdown}
                    >
                        <DotsIcons aria-hidden="true" />
                    </button>

                    {isDropdownOpen && (
                        <div
                            className={styles.dropdown}
                            role="menu"
                            id={`address-dropdown-${index}`}
                            aria-label={`${__('Actions for address', 'give')} ${index + 1}`}
                        >
                            <button
                                className={styles.dropdownItem}
                                role="menuitem"
                                onClick={handleEditAction}
                            >
                                <EditIcon />
                                {__('Edit', 'give')}
                            </button>
                            {!isPrimary && (
                                <button
                                    className={styles.dropdownItem}
                                    role="menuitem"
                                    onClick={handleSetAsPrimaryAction}
                                >
                                    <SetAsPrimaryIcon />
                                    {__('Set as primary', 'give')}
                                </button>
                            )}
                            <button
                                className={`${styles.dropdownItem} ${styles.delete}`}
                                role="menuitem"
                                onClick={handleDeleteAction}
                                aria-label={`${__('Delete address', 'give')} ${index + 1}`}
                            >
                                <TrashIcon aria-hidden="true" />
                                {__('Delete', 'give')}
                            </button>
                        </div>
                    )}
                </div>
            </div>
            <div className={styles.content}>
                <FormattedAddress address={address} />
            </div>
        </div>
    );
}
