/**
 * External Dependencies
 */
import { useState, useEffect, useRef } from 'react';

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal Dependencies
 */
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
interface AddAddressDialogProps {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: (newAddress: string, setAsPrimary: boolean) => void;
}

/**
 * @unreleased
 */
export default function AddAddressDialog({
    isOpen,
    handleClose,
    handleConfirm,
}: AddAddressDialogProps) {
    const [address, setAddress] = useState('');
    const [setAsPrimary, setSetAsPrimary] = useState(false);
    const [addressError, setAddressError] = useState('');
    const addressInputRef = useRef<HTMLTextAreaElement>(null);

    useEffect(() => {
        if (isOpen && addressInputRef.current) {
            const timeoutId = setTimeout(() => {
                addressInputRef.current?.focus();
            }, 100);

            return () => clearTimeout(timeoutId);
        }
    }, [isOpen]);

    const handleAddressChange = (value: string) => {
        setAddress(value);

        if (addressError) {
            setAddressError('');
        }
    };

    const handleFormSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.stopPropagation();
        e.preventDefault();

        const trimmedAddress = address.trim();

        if (!trimmedAddress) {
            setAddressError(__('Address is required', 'give'));
            return;
        }

        handleConfirm(trimmedAddress, setAsPrimary);

        setAddress('');
        setSetAsPrimary(false);
        setAddressError('');
        handleClose();
    };

    const handleCancel = () => {
        setAddress('');
        setSetAsPrimary(false);
        setAddressError('');
        handleClose();
    };

    const isFormValid = address.trim();

    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleCancel}
            title={__('Add an address', 'give')}
        >
            <div className={`${styles.dialog} ${styles.addAddressDialog}`}>
                <form onSubmit={handleFormSubmit}>
                    <div className={styles.content}>
                        <div className={styles.formField}>
                            <label htmlFor="address" className={styles.label}>
                                {__('Address', 'give')}
                            </label>
                            <textarea
                                ref={addressInputRef}
                                id="address"
                                className={`${styles.input} ${styles.textarea} ${addressError ? styles.inputError : ''}`}
                                value={address}
                                onChange={(e) => handleAddressChange(e.target.value)}
                                placeholder=""
                                rows={3}
                                aria-invalid={!!addressError}
                                aria-describedby={addressError ? 'address-error' : undefined}
                                aria-required="true"
                            />
                            {addressError && (
                                <div
                                    id="address-error"
                                    className={styles.errorMessage}
                                    role="alert"
                                    aria-live="polite"
                                >
                                    {addressError}
                                </div>
                            )}
                        </div>

                        <div className={styles.checkboxField}>
                            <label className={styles.checkboxLabel}>
                                <input
                                    type="checkbox"
                                    className={styles.checkbox}
                                    checked={setAsPrimary}
                                    onChange={(e) => setSetAsPrimary(e.target.checked)}
                                    aria-describedby="primary-address-description"
                                />
                                <span id="primary-address-description">
                                    {__('Set as primary address for this donor', 'give')}
                                </span>
                            </label>
                        </div>
                    </div>

                    <div className={styles.buttons}>
                        <button
                            type="submit"
                            className={`button button-primary ${styles.addButton}`}
                            disabled={!isFormValid}
                            aria-describedby={!isFormValid ? 'submit-button-description' : undefined}
                        >
                            {__('Add address', 'give')}
                        </button>

                        {!isFormValid && (
                            <div
                                id="submit-button-description"
                                className="screen-reader-text"
                                aria-live="polite"
                            >
                                {address.trim()
                                    ? __('Please fix the address validation errors before submitting', 'give')
                                    : __('Please enter an address to continue', 'give')
                                }
                            </div>
                        )}
                    </div>
                </form>
            </div>
        </ModalDialog>
    );
}
