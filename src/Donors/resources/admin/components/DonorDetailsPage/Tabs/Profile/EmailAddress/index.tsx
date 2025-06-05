/**
 * External Dependencies
 */
import { useState, useRef, useEffect } from "react";
import { useFormContext } from "react-hook-form";

/**
 * WordPress Dependencies
 */
import { __ } from "@wordpress/i18n";
import { useDispatch } from "@wordpress/data";

/**
 * Internal Dependencies
 */
import AdminSection, { AdminSectionField } from '@givewp/components/AdminDetailsPage/AdminSection';
import { DotsIcons, TrashIcon } from "@givewp/components/AdminDetailsPage/Icons";
import { useDonorEntityRecord } from "@givewp/donors/utils";
import AddEmailDialog from './AddEmailDialog';
import DeleteEmailDialog from './DeleteEmailDialog';
import styles from './styles.module.scss';

/**
 * @unreleased
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

    const toggleDropdown = (event: React.MouseEvent<HTMLButtonElement>, emailAddress: string) => {
        event.preventDefault();
        setOpenDropdown(openDropdown === emailAddress ? null : emailAddress);
    };

    const handleSetAsPrimaryAction = (emailAddress: string, shouldDirty: boolean = true) => {
        setValue('additionalEmails', [email, ...additionalEmails].filter((additionalEmail) => additionalEmail !== emailAddress), { shouldDirty });
        setValue('email', emailAddress, { shouldDirty });
        setOpenDropdown(null);
    };

    const handleDeleteEmailAction = (emailAddress: string) => {
        setEmailToDelete(emailAddress);
        setIsDeleteDialogOpen(true);
        setOpenDropdown(null);
    };

    const handleAddEmailConfirm = async (newEmail: string, setAsPrimary: boolean) => {
        setValue('additionalEmails', [...additionalEmails, newEmail], { shouldDirty: true });

        if (setAsPrimary) {
            handleSetAsPrimaryAction(newEmail, false);
        }
    };

    const handleDeleteEmailConfirm = async () => {
        setValue('additionalEmails', additionalEmails.filter((additionalEmail) => additionalEmail !== emailToDelete), { shouldDirty: true });

        setIsDeleteDialogOpen(false);
        setEmailToDelete('');
    };

    return (
        <>
            <AdminSection
                title={__('Email Address', 'give')}
                description={__('Manage the email address of the donor', 'give')}
            >
                <AdminSectionField>
                    <div className={styles.donorEmailAddress}>
                        <div className={styles.item}>
                            <span className={styles.address}>{email}</span>
                            <span className={`${styles.badge} ${styles.badgePrimary}`}>
                                {__('Primary', 'give')}
                            </span>
                        </div>

                        {additionalEmails.map((emailAddress) => (
                            <div
                                key={emailAddress}
                                className={styles.item}
                            >
                                <span className={styles.address}>{emailAddress}</span>
                                    <div
                                        className={styles.actions}
                                        ref={(el) => (dropdownRefs.current[emailAddress] = el)}
                                    >
                                        <button
                                            className={styles.menuTrigger}
                                            aria-label={__('Email actions', 'give')}
                                            onClick={(event) => toggleDropdown(event, emailAddress)}
                                        >
                                            <DotsIcons />
                                        </button>

                                        {openDropdown === emailAddress && (
                                            <div className={styles.dropdown}>
                                                <button
                                                    className={styles.dropdownItem}
                                                    onClick={() => handleSetAsPrimaryAction(emailAddress)}
                                                >
                                                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M7 4.668v4.667M4.667 7h4.666" stroke="#4B5563" strokeWidth="1.167" strokeLinecap="round" strokeLinejoin="round"/>
                                                        <path d="M1.75 4.55c0-.98 0-1.47.19-1.844a1.75 1.75 0 0 1 .766-.765c.374-.191.864-.191 1.844-.191h4.9c.98 0 1.47 0 1.845.19.329.169.597.436.764.766.191.374.191.864.191 1.844v4.9c0 .98 0 1.47-.19 1.845a1.75 1.75 0 0 1-.766.764c-.374.191-.864.191-1.844.191h-4.9c-.98 0-1.47 0-1.844-.19a1.75 1.75 0 0 1-.765-.766c-.191-.374-.191-.864-.191-1.844v-4.9z" stroke="#000" strokeWidth="1.167" strokeLinecap="round" strokeLinejoin="round"/>
                                                    </svg>

                                                    {__('Set as primary', 'give')}
                                                </button>
                                                <button
                                                    className={`${styles.dropdownItem} ${styles.delete}`}
                                                    onClick={() => handleDeleteEmailAction(emailAddress)}
                                                >
                                                    <TrashIcon />
                                                    {__('Delete', 'give')}
                                                </button>
                                            </div>
                                        )}
                                    </div>
                            </div>
                        ))}

                        <div className={styles.add}>
                            <button
                                className={styles.addButton}
                                onClick={() => setIsAddDialogOpen(true)}
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
