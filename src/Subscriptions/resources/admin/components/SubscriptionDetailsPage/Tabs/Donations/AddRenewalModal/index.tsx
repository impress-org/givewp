/**
 * External Dependencies
 */
import { useState } from 'react';

/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n';
import { Spinner } from '@wordpress/components';

/**
 * Internal Dependencies
 */
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import styles from './styles.module.scss';
import { CurrencyControl } from '@givewp/form-builder-library';
import { useFormContext } from 'react-hook-form';
import { CurrencyCode } from '@givewp/form-builder-library/build/CurrencyControl/CurrencyCode';
import { formatDateLocal } from '@givewp/components/AdminDetailsPage/utils';
import { InfoIcon } from '@givewp/components/AdminDetailsPage/Icons';

/**
 * @since 4.8.0
 */
interface AddRenewalDialogProps {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: (data: any) => void;
}

interface RenewalData {
    amount: number | string;
    date: string;
    updateRenewalDate: boolean;
    transactionId?: string;
}

/**
 * @since 4.8.0
 */
export default function AddRenewalDialog({
    isOpen,
    handleClose,
    handleConfirm,
}: AddRenewalDialogProps) {
    const { getValues } = useFormContext();
    const { amount } = getValues();

    const [data, setData] = useState<any>({
        amount: amount.value,
        date: formatDateLocal(new Date().toISOString()),
        updateRenewalDate: false,
        transactionId: '',
    });
    const [errors, setErrors] = useState<Partial<any>>({});
    const [isLoading, setIsLoading] = useState(false);


    const handleFieldChange = (field: keyof RenewalData, value: typeof data[keyof RenewalData]) => {
        setData(prev => ({
            ...prev,
            [field]: value
        }));

        // Clear error for this field if it exists
        if (errors[field]) {
            setErrors(prev => ({
                ...prev,
                [field]: ''
            }));
        }
    };

    const validateForm = (): boolean => {
        const newErrors: Partial<RenewalData> = {};

        if (!data.amount) {
            newErrors.amount = __('Amount is required', 'give');
        }

        if (!data.date) {
            newErrors.date = __('Date is required', 'give');
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const resetData = () => {
        setData({
            amount: amount.value,
            date: formatDateLocal(new Date().toISOString()),
            updateRenewalDate: false,
            transactionId: '',
        });
    };

    const handleFormSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
        e.stopPropagation();
        e.preventDefault();

        if (!validateForm() || isLoading) {
            return;
        }

        setIsLoading(true);

        try {
            await handleConfirm({
                ...data,
                amount: {
                    amount: data.amount,
                    currency: amount.currency,
                },
            });
            resetData();
            setErrors({});
            setIsLoading(false);
            handleClose();
        } catch (error) {
            console.error('Error adding renewal:', error);
            setIsLoading(false);
        }
    };

    const handleCancel = () => {
        resetData();
        setErrors({});
        handleClose();
    };

    const isFormValid = data.amount && data.date;
    const isSubmitDisabled = !isFormValid || isLoading;

    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleCancel}
            title={__('Renewal donation', 'give')}
        >
            <div className={styles.addRenewalDialog}>
                <form onSubmit={handleFormSubmit}>
                    <div className={styles.content}>
                        <div className={styles.formField}>
                            <label htmlFor="amount" className={styles.label}>
                                {__('Amount', 'give')}
                            </label>
                            <CurrencyControl
                                id="amount"
                                name="amount"
                                className={`${styles.input} ${errors.amount ? styles.inputError : ''}`}
                                currency={amount.currency as CurrencyCode}
                                disabled={false}
                                placeholder={__('Enter amount', 'give')}
                                value={data.amount}
                                onValueChange={(value) => {
                                    handleFieldChange('amount', Number(value ?? 0));
                                }}
                            />
                            {errors.amount && (
                                <div
                                    id="amount-error"
                                    className={styles.errorMessage}
                                    role="alert"
                                    aria-live="polite"
                                >
                                    {errors.amount}
                                </div>
                            )}
                        </div>

                        <div className={styles.formField}>
                            <label htmlFor="date" className={styles.label}>
                                {__('Date', 'give')}
                            </label>
                            <input
                                type="date"
                                id="date"
                                className={`${styles.input} ${errors.date ? styles.inputError : ''}`}
                                value={data.date}
                                onChange={(e) => {
                                    handleFieldChange('date', e.target.value);
                                }}
                            />
                            {errors.date && (
                                <div
                                    id="date-error"
                                    className={styles.errorMessage}
                                    role="alert"
                                    aria-live="polite"
                                >
                                    {errors.date}
                                </div>
                            )}
                        </div>

                        <div className={styles.checkboxField}>
                            <label className={styles.checkboxLabel}>
                                <input
                                    type="checkbox"
                                    className={styles.checkbox}
                                    checked={data.updateRenewalDate}
                                    onChange={(e) => handleFieldChange('updateRenewalDate', e.target.checked)}
                                    aria-describedby="update-renewal-date-description"
                                />
                                <span id="update-renewal-date-description">
                                    {__('Update renewal date', 'give')}
                                </span>
                            </label>
                        </div>

                        <div className={styles.formField}>
                            <label htmlFor="transactionId" className={styles.label}>
                                {__('Transaction ID', 'give')} <span className={styles.optional}>({__('optional', 'give')})</span>
                            </label>
                            <input
                                type="text"
                                id="transactionId"
                                className={`${styles.input} ${errors.transactionId ? styles.inputError : ''}`}
                                value={data.transactionId}
                                onChange={(e) => handleFieldChange('transactionId', e.target.value)}
                            />
                            {errors.transactionId && (
                                <div
                                    id="transactionId-error"
                                    className={styles.errorMessage}
                                    role="alert"
                                    aria-live="polite"
                                >
                                    {errors.transactionId}
                                </div>
                            )}
                        </div>
                    </div>

                    <div className={styles.buttons}>
                        <button
                            type="submit"
                            className={`button button-primary ${styles.addButton} ${isLoading ? styles.loading : ''}`}
                            disabled={isSubmitDisabled && !isLoading}
                            aria-describedby={!isFormValid ? 'submit-button-description' : undefined}
                        >
                            {isLoading ? (
                                <>
                                    {__('Adding renewal', 'give')}
                                    <Spinner />
                                </>
                            ) : __('Add renewal', 'give')}
                        </button>

                        {!isFormValid && (
                            <div
                                id="submit-button-description"
                                className="screen-reader-text"
                                aria-live="polite"
                            >
                                {__('Please fill in all required fields to continue', 'give')}
                            </div>
                        )}
                    </div>

                    <div className={styles.note}>
                        <InfoIcon />
                        {__('Please note that this will not charge the donor nor create the renewal at the gateway.', 'give')}
                    </div>
                </form>
            </div>
        </ModalDialog>
    );
}
