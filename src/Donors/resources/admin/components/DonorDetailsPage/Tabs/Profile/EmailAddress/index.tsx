/**
 * External Dependencies
 */
import { useState, useRef, useEffect } from "react";
import { useFormContext } from "react-hook-form";

/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";

/**
 * Internal Dependencies
 */
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { DotsIcons, TrashIcon } from "@givewp/components/AdminDetailsPage/Icons";
import AddEmailDialog from './AddEmailDialog';
import DeleteEmailDialog from './DeleteEmailDialog';
import styles from './styles.module.scss';

/**
 * @since 4.4.0
 */
export default function DonorEmailAddress() {
    const [openDropdown, setOpenDropdown] = useState<string | null>(null);
    const [isAddDialogOpen, setIsAddDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);
    const [emailToDelete, setEmailToDelete] = useState<string>('');
    const dropdownRefs = useRef<{ [key: string]: HTMLDivElement | null }>({});

    const {
        watch,
        setValue,
    } = useFormContext();

    const email = watch('email');
    const additionalEmails: string[] = (watch('additionalEmails') || []).filter((additionalEmail: string) => additionalEmail !== email);

    // Close dropdown when clicking outside
    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (openDropdown && dropdownRefs.current[openDropdown]) {
                const dropdownElement = dropdownRefs.current[openDropdown];
                if (dropdownElement && !dropdownElement.contains(event.target as Node)) {
                    setOpenDropdown(null);
                }
            }
        };

        if (openDropdown) {
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

    const toggleDropdown = (event: React.MouseEvent<HTMLButtonElement>, emailAddress: string) => {
        event.preventDefault();
        setOpenDropdown(openDropdown === emailAddress ? null : emailAddress);
    };

    const handleSetAsPrimaryAction = (emailAddress: string) => {
        setValue('additionalEmails', [email, ...additionalEmails].filter((additionalEmail) => additionalEmail !== emailAddress), { shouldDirty: true });
        setValue('email', emailAddress, { shouldDirty: true });
        setOpenDropdown(null);
    };

    const handleDeleteEmailAction = (emailAddress: string) => {
        setEmailToDelete(emailAddress);
        setIsDeleteDialogOpen(true);
        setOpenDropdown(null);
    };

    const handleAddEmailConfirm = (newEmail: string, setAsPrimary: boolean) => {
        setValue('additionalEmails', [...additionalEmails, newEmail], { shouldDirty: true });

        if (setAsPrimary) {
            handleSetAsPrimaryAction(newEmail);
        }
    };

    const handleDeleteEmailConfirm = () => {
        setValue('additionalEmails', additionalEmails.filter((additionalEmail) => additionalEmail !== emailToDelete), { shouldDirty: true });

        setIsDeleteDialogOpen(false);
        setEmailToDelete('');
    };

    const totalEmails = 1 + additionalEmails.length;
    const sectionId = 'donor-email-addresses';
    const descriptionId = `${sectionId}-description`;

    return (
        <>
            <AdminSection
                title={__('Email Address', 'give')}
                description={__('Manage the email address of the donor', 'give')}
            >
                <AdminSectionField>
                    <div
                        className={styles.donorEmailAddress}
                        role="region"
                        aria-labelledby={sectionId}
                        aria-describedby={descriptionId}
                    >
                        <div
                            role="list"
                            aria-label={__('Donor email addresses', 'give')}
                            aria-live="polite"
                        >
                            <div
                                className={styles.item}
                                role="listitem"
                                aria-label={__('Primary email address', 'give')}
                            >
                                <span
                                    className={styles.address}
                                    aria-label={`${__('Email address:', 'give')} ${email}`}
                                >
                                    {email}
                                </span>
                                <span
                                    className={`${styles.badge} ${styles.badgePrimary}`}
                                    aria-label={__('Primary email badge', 'give')}
                                >
                                    {__('Primary', 'give')}
                                </span>
                            </div>

                            {additionalEmails.map((emailAddress, index) => (
                                <div
                                    key={emailAddress}
                                    className={styles.item}
                                    role="listitem"
                                    aria-label={`${__('Additional email address', 'give')} ${index + 1} ${__('of', 'give')} ${additionalEmails.length}`}
                                >
                                    <span
                                        className={styles.address}
                                        aria-label={`${__('Email address:', 'give')} ${emailAddress}`}
                                    >
                                        {emailAddress}
                                    </span>
                                    <div
                                        className={styles.actions}
                                        ref={(el) => (dropdownRefs.current[emailAddress] = el)}
                                    >
                                        <button
                                            className={styles.menuTrigger}
                                            aria-label={`${__('Email actions for', 'give')} ${emailAddress}`}
                                            aria-haspopup="menu"
                                            aria-expanded={openDropdown === emailAddress}
                                            aria-controls={`email-dropdown-${emailAddress.replace(/[^a-zA-Z0-9]/g, '-')}`}
                                            onClick={(event) => toggleDropdown(event, emailAddress)}
                                        >
                                            <DotsIcons aria-hidden="true" />
                                        </button>

                                        {openDropdown === emailAddress && (
                                            <div
                                                className={styles.dropdown}
                                                role="menu"
                                                id={`email-dropdown-${emailAddress.replace(/[^a-zA-Z0-9]/g, '-')}`}
                                                aria-label={`${__('Actions for email', 'give')} ${emailAddress}`}
                                            >
                                                <button
                                                    className={styles.dropdownItem}
                                                    role="menuitem"
                                                    onClick={() => handleSetAsPrimaryAction(emailAddress)}
                                                    aria-label={`${__('Set', 'give')} ${emailAddress} ${__('as primary email', 'give')}`}
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
                                                <button
                                                    className={`${styles.dropdownItem} ${styles.delete}`}
                                                    role="menuitem"
                                                    onClick={() => handleDeleteEmailAction(emailAddress)}
                                                    aria-label={`${__('Delete email', 'give')} ${emailAddress}`}
                                                >
                                                    <TrashIcon aria-hidden="true" />
                                                    {__('Delete', 'give')}
                                                </button>
                                            </div>
                                        )}
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
                                aria-label={`${__('Add new email address. Currently', 'give')} ${totalEmails} ${totalEmails === 1 ? __('email', 'give') : __('emails', 'give')} ${__('total', 'give')}`}
                                aria-describedby={descriptionId}
                            >
                                {__('Add email', 'give')}
                            </button>
                        </div>
                    </div>
                </AdminSectionField>
            </AdminSection>

            <AddEmailDialog
                isOpen={isAddDialogOpen}
                handleClose={() => setIsAddDialogOpen(false)}
                handleConfirm={handleAddEmailConfirm}
            />

            <DeleteEmailDialog
                isOpen={isDeleteDialogOpen}
                emailAddress={emailToDelete}
                handleClose={() => setIsDeleteDialogOpen(false)}
                handleConfirm={handleDeleteEmailConfirm}
            />
        </>
    );
}
