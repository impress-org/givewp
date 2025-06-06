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
import { DotsIcons, TrashIcon } from "@givewp/components/AdminDetailsPage/Icons";
import FormattedAddress from "./FormattedAddress";
import styles from './styles.module.scss';
import { DonorAddress as DonorAddressType } from "../../../../types";

interface AddressItemProps {
    address: DonorAddressType;
    index: number;
    totalAddresses: number;
    onEdit: (index: number) => void;
    onSetAsPrimary: (index: number) => void;
    onDelete: (index: number) => void;
}

/**
 * @unreleased
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
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M6.417 2.331h-2.45c-.98 0-1.47 0-1.845.19a1.75 1.75 0 0 0-.765.766c-.19.374-.19.864-.19 1.844v4.9c0 .98 0 1.47.19 1.845.168.329.436.597.765.764.375.191.865.191 1.845.191h4.9c.98 0 1.47 0 1.844-.19a1.75 1.75 0 0 0 .765-.765c.19-.375.19-.865.19-1.845v-2.45m-7 1.75h.978c.285 0 .428 0 .562-.032.119-.029.233-.076.337-.14.118-.072.219-.173.42-.375l5.579-5.578a1.237 1.237 0 1 0-1.75-1.75L5.213 7.034c-.201.202-.302.303-.374.42a1.167 1.167 0 0 0-.14.338c-.032.134-.032.277-.032.562v.977z" stroke="#4B5563" strokeWidth="1.167" strokeLinecap="round" strokeLinejoin="round"/>
                                </svg>
                                {__('Edit', 'give')}
                            </button>
                            {!isPrimary && (
                                <button
                                    className={styles.dropdownItem}
                                    role="menuitem"
                                    onClick={handleSetAsPrimaryAction}
                                >
                                    <svg
                                        width="14"
                                        height="14"
                                        viewBox="0 0 14 14"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        aria-hidden="true"
                                    >
                                        <path d="M7 4.668v4.667M4.667 7h4.666" stroke="#4B5563" strokeWidth="1.167" strokeLinecap="round" strokeLinejoin="round"/>
                                        <path d="M1.75 4.55c0-.98 0-1.47.19-1.844a1.75 1.75 0 0 1 .766-.765c.374-.191.864-.191 1.844-.191h4.9c.98 0 1.47 0 1.845.19.329.169.597.436.764.766.191.374.191.864.191 1.844v4.9c0 .98 0 1.47-.19 1.845a1.75 1.75 0 0 1-.766.764c-.374.191-.864.191-1.844.191h-4.9c-.98 0-1.47 0-1.844-.19a1.75 1.75 0 0 1-.765-.766c-.191-.374-.191-.864-.191-1.844v-4.9z" stroke="#000" strokeWidth="1.167" strokeLinecap="round" strokeLinejoin="round"/>
                                    </svg>
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
