/**
 * External Dependencies
 */
import { useState } from 'react';

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
interface AddEmailDialogProps {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: (newEmail: string, setAsPrimary: boolean) => void;
}

/**
 * Validates email format
 *
 * @unreleased
 */
const validateEmail = (email: string): boolean => {
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email);
};

/**
 * @unreleased
 */
export default function AddEmailDialog({
    isOpen,
    handleClose,
    handleConfirm,
}: AddEmailDialogProps) {
    const [email, setEmail] = useState('');
    const [setAsPrimary, setSetAsPrimary] = useState(false);
    const [emailError, setEmailError] = useState('');

    const handleEmailChange = (value: string) => {
        setEmail(value);
        // Clear existing errors when user starts typing again
        if (emailError) {
            setEmailError('');
        }
    };

    const handleKeyDown = (e: React.KeyboardEvent<HTMLInputElement>) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleSubmit();
        }
    };

    const handleSubmit = () => {
        const trimmedEmail = email.trim();

        if (!trimmedEmail) {
            setEmailError(__('Email is required', 'give'));
            return;
        }

        if (!validateEmail(trimmedEmail)) {
            setEmailError(__('Please enter a valid email address', 'give'));
            return;
        }

        handleConfirm(trimmedEmail, setAsPrimary);

        // Reset form
        setEmail('');
        setSetAsPrimary(false);
        setEmailError('');
    };

    const handleCancel = () => {
        // Reset form
        setEmail('');
        setSetAsPrimary(false);
        setEmailError('');
        handleClose();
    };

    const isFormValid = email.trim();

    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleCancel}
            title={__('Add an email', 'give')}
        >
            <div className={`${styles.dialog} ${styles.addEmailDialog}`}>
                <div className={styles.content}>
                    <div className={styles.formField}>
                        <label htmlFor="email" className={styles.label}>
                            {__('Email', 'give')}
                        </label>
                        <input
                            id="email"
                            type="email"
                            className={`${styles.input} ${emailError ? styles.inputError : ''}`}
                            value={email}
                            onChange={(e) => handleEmailChange(e.target.value)}
                            placeholder=""
                            aria-invalid={!!emailError}
                            aria-describedby={emailError ? 'email-error' : undefined}
                            aria-required="true"
                            onKeyDown={handleKeyDown}
                        />
                        {emailError && (
                            <div
                                id="email-error"
                                className={styles.errorMessage}
                                role="alert"
                                aria-live="polite"
                            >
                                {emailError}
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
                                aria-describedby="primary-email-description"
                            />
                            <span id="primary-email-description">
                                {__('Set as primary email address for this donor', 'give')}
                            </span>
                        </label>
                    </div>
                </div>

                <div className={styles.buttons}>
                    <button
                        className={`button button-primary ${styles.addButton}`}
                        onClick={handleSubmit}
                        disabled={!isFormValid}
                        aria-describedby={!isFormValid ? 'submit-button-description' : undefined}
                    >
                        {__('Add email', 'give')}
                    </button>

                    {!isFormValid && (
                        <div
                            id="submit-button-description"
                            className="screen-reader-text"
                            aria-live="polite"
                        >
                            {email.trim()
                                ? __('Please fix the email validation errors before submitting', 'give')
                                : __('Please enter an email address to continue', 'give')
                            }
                        </div>
                    )}
                </div>
            </div>
        </ModalDialog>
    );
}
