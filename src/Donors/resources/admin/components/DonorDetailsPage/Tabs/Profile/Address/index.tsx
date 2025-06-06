/**
 * External Dependencies
 */
import { useState, useRef, useEffect } from "react";
import { useFormContext } from "react-hook-form";

/**
 * WordPress Dependencies
 */
import { __, sprintf } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { DotsIcons, TrashIcon } from "@givewp/components/AdminDetailsPage/Icons";
import AddAddressDialog from './AddAddressDialog';
import DeleteAddressDialog from './DeleteAddressDialog';
import styles from './styles.module.scss';
import { DonorAddress as DonorAddressType } from "../../../../types";
import FormattedAddress from "./FormattedAddress";

/**
 * @unreleased
 */
export default function DonorAddress() {
    const [openDropdown, setOpenDropdown] = useState<number | null>(null);
    const [isAddDialogOpen, setIsAddDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [addressToDelete, setAddressToDelete] = useState<number | null>(null);
    const [addressToEdit, setAddressToEdit] = useState<number | null>(null);
    const dropdownRefs = useRef<{ [key: number]: HTMLDivElement | null }>({});

    const {
        watch,
        setValue,
    } = useFormContext();

    const addresses: DonorAddressType[] = watch('addresses') || [];

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            console.log('handleClickOutside', openDropdown, dropdownRefs.current[openDropdown]);
            if (openDropdown && dropdownRefs.current[openDropdown]) {
                const dropdownElement = dropdownRefs.current[openDropdown];
                if (dropdownElement && !dropdownElement.contains(event.target as Node)) {
                    setOpenDropdown(null);
                }
            }
        };

        if (openDropdown !== null) {
            document.addEventListener('mousedown', handleClickOutside);
            return () => {
                document.removeEventListener('mousedown', handleClickOutside);
            };
        }
    }, [openDropdown]);

    // Handle keyboard navigation
    useEffect(() => {
        const handleKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape' && openDropdown) {
                setOpenDropdown(null);
            }
        };

        if (openDropdown) {
            document.addEventListener('keydown', handleKeyDown);
            return () => {
                document.removeEventListener('keydown', handleKeyDown);
            };
        }
    }, [openDropdown]);

    const toggleDropdown = (event: React.MouseEvent<HTMLButtonElement>, addressIndex: number) => {
        event.preventDefault();
        setOpenDropdown(openDropdown === addressIndex ? null : addressIndex);
    };

    const handleEditAddressAction = (addressIndex: number) => {
        setAddressToEdit(addressIndex);
        setIsAddDialogOpen(true);
        setOpenDropdown(null);
    };

    const handleSetAsPrimaryAction = (addressIndex: number) => {
        if (addressIndex >= 0 && addressIndex < addresses.length) {
            const selectedAddress = addresses[addressIndex];
            const remainingAddresses = addresses.filter((_: any, index: number) => index !== addressIndex);
            const reorderedAddresses = [selectedAddress, ...remainingAddresses];

            setValue('addresses', reorderedAddresses, { shouldDirty: true });
        }

        setOpenDropdown(null);
    };

    const handleDeleteAddressAction = (addressIndex: number) => {
        setAddressToDelete(addressIndex);
        setIsDeleteDialogOpen(true);
        setOpenDropdown(null);
    };

    const handleAddAddressConfirm = (newAddress: string, setAsPrimary: boolean) => {
        setValue('addresses', [...addresses, newAddress], { shouldDirty: true });

        if (setAsPrimary) {
            handleSetAsPrimaryAction(addresses.length);
        }
    };

    const handleDeleteAddressConfirm = () => {
        setValue('addresses', addresses.filter((_: any, index: number) => index !== addressToDelete), { shouldDirty: true });

        setIsDeleteDialogOpen(false);
        setAddressToDelete(null);
    };

    const sectionId = 'donor-addresses';
    const descriptionId = `${sectionId}-description`;

    return (
        <>
            <AdminSection
                title={__('Address', 'give')}
                description={__('Manage the address of the donor', 'give')}
            >
                <AdminSectionField>
                    <div
                        className={styles.donorAddress}
                        role="region"
                        aria-labelledby={sectionId}
                        aria-describedby={descriptionId}
                    >
                        <div
                            className={styles.addresses}
                            role="list"
                            aria-label={__('Donor addresses', 'give')}
                            aria-live="polite"
                        >
                            {addresses.map((address, index) => (
                                <div
                                    key={index}
                                    className={styles.item}
                                    role="listitem"
                                    aria-label={`${__('Donor address', 'give')} ${index + 1} ${__('of', 'give')} ${addresses.length}`}
                                >
                                    <div className={styles.header}>
                                        <span
                                            className={styles.address}
                                            aria-label={`${__('Billing Address:', 'give')} ${index + 1}`}
                                        >
                                            {sprintf(__('Billing Address %s', 'give'), index + 1)}
                                        </span>
                                        {index === 0 && (
                                            <span
                                                className={`${styles.badge} ${styles.badgePrimary}`}
                                                aria-label={__('Primary address badge', 'give')}
                                            >
                                                {__('Primary', 'give')}
                                            </span>
                                        )}
                                        <div
                                            className={styles.actions}
                                            ref={(el) => (dropdownRefs.current[index] = el)}
                                        >
                                            <button
                                                className={styles.menuTrigger}
                                                aria-label={`${__('Address actions for', 'give')} ${index + 1}`}
                                                aria-haspopup="menu"
                                                aria-expanded={openDropdown === index}
                                                aria-controls={`address-dropdown-${index}`}
                                                onClick={(event) => toggleDropdown(event, index)}
                                            >
                                                <DotsIcons aria-hidden="true" />
                                            </button>

                                            {openDropdown === index && (
                                                <div
                                                    className={styles.dropdown}
                                                    role="menu"
                                                    id={`address-dropdown-${index}`}
                                                    aria-label={`${__('Actions for address', 'give')} ${index + 1}`}
                                                >
                                                    <button
                                                        className={styles.dropdownItem}
                                                        role="menuitem"
                                                        onClick={() => handleEditAddressAction(index)}
                                                    >
                                                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M6.417 2.331h-2.45c-.98 0-1.47 0-1.845.19a1.75 1.75 0 0 0-.765.766c-.19.374-.19.864-.19 1.844v4.9c0 .98 0 1.47.19 1.845.168.329.436.597.765.764.375.191.865.191 1.845.191h4.9c.98 0 1.47 0 1.844-.19a1.75 1.75 0 0 0 .765-.765c.19-.375.19-.865.19-1.845v-2.45m-7 1.75h.978c.285 0 .428 0 .562-.032.119-.029.233-.076.337-.14.118-.072.219-.173.42-.375l5.579-5.578a1.237 1.237 0 1 0-1.75-1.75L5.213 7.034c-.201.202-.302.303-.374.42a1.167 1.167 0 0 0-.14.338c-.032.134-.032.277-.032.562v.977z" stroke="#4B5563" strokeWidth="1.167" strokeLinecap="round" strokeLinejoin="round"/>
                                                        </svg>
                                                        {__('Edit', 'give')}
                                                    </button>
                                                    {index > 0 && (
                                                        <button
                                                            className={styles.dropdownItem}
                                                            role="menuitem"
                                                            onClick={() => handleSetAsPrimaryAction(index)}
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
                                                        onClick={() => handleDeleteAddressAction(index)}
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
                            ))}
                        </div>

                        <div className={styles.add}>
                            <button
                                className={styles.addButton}
                                onClick={(event) => {
                                    event.preventDefault();
                                    setIsAddDialogOpen(true);
                                }}
                                aria-label={`${__('Add new address. Currently', 'give')} ${addresses.length} ${addresses.length === 1 ? __('address', 'give') : __('addresses', 'give')} ${__('total', 'give')}`}
                                aria-describedby={descriptionId}
                            >
                                {__('Add address', 'give')}
                            </button>
                        </div>
                    </div>
                </AdminSectionField>
            </AdminSection>

            <AddAddressDialog
                isOpen={isAddDialogOpen}
                handleClose={() => setIsAddDialogOpen(false)}
                handleConfirm={handleAddAddressConfirm}
            />

            <DeleteAddressDialog
                isOpen={isDeleteDialogOpen}
                address={addresses[addressToDelete]}
                addressIndex={addressToDelete}
                handleClose={() => setIsDeleteDialogOpen(false)}
                handleConfirm={handleDeleteAddressConfirm}
            />
        </>
    );
}
