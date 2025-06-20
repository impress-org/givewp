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
import { DonorAddress } from '../../../../types';
import { getDonorOptionsWindowData } from '@givewp/donors/utils';
import { getStatesForCountry, StatesConfig } from './addressUtils';

const { countries } = getDonorOptionsWindowData();

const DEFAULT_ADDRESS: DonorAddress = {
    country: '',
    address1: '',
    address2: '',
    city: '',
    state: '',
    zip: '',
};
/**
 * @since 4.4.0
 */
interface AddressDialogProps {
    isOpen: boolean;
    handleClose: () => void;
    handleConfirm: (address: DonorAddress, addressIndex?: number) => void;
    address?: DonorAddress;
    addressIndex?: number;
}

/**
 * @since 4.4.0
 */
export default function EditAddressDialog({
    isOpen,
    handleClose,
    handleConfirm,
    address: initialAddress,
    addressIndex,
}: AddressDialogProps) {
    const [address, setAddress] = useState<DonorAddress>(DEFAULT_ADDRESS);
    const [errors, setErrors] = useState<Partial<DonorAddress>>({});
    const [stateConfig, setStateConfig] = useState<StatesConfig>({
        hasStates: false,
        states: [],
        stateLabel: __('State', 'give'),
        isRequired: false,
        showField: true,
    });
    const countrySelectRef = useRef<HTMLSelectElement>(null);

    const isEditMode = initialAddress !== undefined && addressIndex !== undefined;

    useEffect(() => {
        if (isOpen) {
            // Initialize form with existing data when editing
            setAddress(initialAddress || DEFAULT_ADDRESS);
            setErrors({});

            if (countrySelectRef.current) {
                const timeoutId = setTimeout(() => {
                    countrySelectRef.current?.focus();
                }, 100);

                return () => clearTimeout(timeoutId);
            }
        }
    }, [isOpen, initialAddress]);

    useEffect(() => {
        if (address.country) {
            const config = getStatesForCountry(address.country);
            setStateConfig(config);
        }
    }, [address.country]);

    const handleFieldChange = (field: keyof DonorAddress, value: string) => {
        setAddress(prev => ({
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
        const newErrors: Partial<DonorAddress> = {};

        if (!address.country.trim()) {
            newErrors.country = __('Country is required', 'give');
        }

        if (!address.address1.trim()) {
            newErrors.address1 = __('Address 1 is required', 'give');
        }

        if (!address.city.trim()) {
            newErrors.city = __('City is required', 'give');
        }

        if (stateConfig.showField && stateConfig.isRequired && !address.state.trim()) {
            newErrors.state = __(`${stateConfig.stateLabel} is required`, 'give');
        }

        if (!address.zip.trim()) {
            newErrors.zip = __('Zip/Postal code is required', 'give');
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleFormSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.stopPropagation();
        e.preventDefault();

        if (!validateForm()) {
            return;
        }

        handleConfirm(address, addressIndex);

        setAddress(DEFAULT_ADDRESS);
        setErrors({});
        handleClose();
    };

    const handleCancel = () => {
        setAddress(DEFAULT_ADDRESS);
        setErrors({});
        handleClose();
    };

    const isFormValid = address.country.trim() &&
        address.address1.trim() &&
        address.city.trim() &&
        (!stateConfig.showField || !stateConfig.isRequired || address.state.trim()) &&
        address.zip.trim();

    return (
        <ModalDialog
            isOpen={isOpen}
            showHeader={true}
            handleClose={handleCancel}
            title={isEditMode ? __('Edit address', 'give') : __('Add an address', 'give')}
        >
            <div className={`${styles.dialog} ${styles.addAddressDialog}`}>
                <form onSubmit={handleFormSubmit}>
                    <div className={styles.content}>
                        <div className={styles.formField}>
                            <label htmlFor="country" className={styles.label}>
                                {__('Country', 'give')}
                            </label>
                            <select
                                ref={countrySelectRef}
                                id="country"
                                className={`${styles.input} ${styles.select} ${errors.country ? styles.inputError : ''}`}
                                value={address.country}
                                onChange={(e) => handleFieldChange('country', e.target.value)}
                                aria-invalid={!!errors.country}
                                aria-describedby={errors.country ? 'country-error' : undefined}
                                aria-required="true"
                            >
                                <option value="">{__('Select a country', 'give')}</option>
                                {Object.entries(countries).filter(([code]) => code !== '').map(([code, name]) => (
                                    <option key={code} value={code}>
                                        {name}
                                    </option>
                                ))}
                            </select>
                            {errors.country && (
                                <div
                                    id="country-error"
                                    className={styles.errorMessage}
                                    role="alert"
                                    aria-live="polite"
                                >
                                    {errors.country}
                                </div>
                            )}
                        </div>

                        <div className={styles.formField}>
                            <label htmlFor="address1" className={styles.label}>
                                {__('Address 1', 'give')}
                            </label>
                            <input
                                type="text"
                                id="address1"
                                className={`${styles.input} ${errors.address1 ? styles.inputError : ''}`}
                                value={address.address1}
                                onChange={(e) => handleFieldChange('address1', e.target.value)}
                                aria-invalid={!!errors.address1}
                                aria-describedby={errors.address1 ? 'address1-error' : undefined}
                                aria-required="true"
                            />
                            {errors.address1 && (
                                <div
                                    id="address1-error"
                                    className={styles.errorMessage}
                                    role="alert"
                                    aria-live="polite"
                                >
                                    {errors.address1}
                                </div>
                            )}
                        </div>

                        <div className={styles.formField}>
                            <label htmlFor="address2" className={styles.label}>
                                {__('Address 2', 'give')}
                            </label>
                            <input
                                type="text"
                                id="address2"
                                className={styles.input}
                                value={address.address2}
                                onChange={(e) => handleFieldChange('address2', e.target.value)}
                            />
                        </div>

                        <div className={styles.formField}>
                            <label htmlFor="city" className={styles.label}>
                                {__('City', 'give')}
                            </label>
                            <input
                                type="text"
                                id="city"
                                className={`${styles.input} ${errors.city ? styles.inputError : ''}`}
                                value={address.city}
                                onChange={(e) => handleFieldChange('city', e.target.value)}
                                aria-invalid={!!errors.city}
                                aria-describedby={errors.city ? 'city-error' : undefined}
                                aria-required="true"
                            />
                            {errors.city && (
                                <div
                                    id="city-error"
                                    className={styles.errorMessage}
                                    role="alert"
                                    aria-live="polite"
                                >
                                    {errors.city}
                                </div>
                            )}
                        </div>

                        <div className={styles.formRow}>
                            {stateConfig.showField && (
                                <div className={styles.formField}>
                                    <label htmlFor="state" className={styles.label}>
                                        {stateConfig.stateLabel}
                                    </label>
                                    {stateConfig.hasStates ? (
                                        <select
                                            id="state"
                                            className={`${styles.input} ${styles.select} ${errors.state ? styles.inputError : ''}`}
                                            value={address.state}
                                            onChange={(e) => handleFieldChange('state', e.target.value)}
                                            aria-invalid={!!errors.state}
                                            aria-describedby={errors.state ? 'state-error' : undefined}
                                            aria-required={stateConfig.isRequired}
                                        >
                                            <option value="">{__(`Select a ${stateConfig.stateLabel.toLowerCase()}`, 'give')}</option>
                                            {stateConfig.states.map((state) => (
                                                <option key={state.value} value={state.value}>
                                                    {state.label}
                                                </option>
                                            ))}
                                        </select>
                                    ) : (
                                        <input
                                            type="text"
                                            id="state"
                                            className={`${styles.input} ${errors.state ? styles.inputError : ''}`}
                                            value={address.state}
                                            onChange={(e) => handleFieldChange('state', e.target.value)}
                                            aria-invalid={!!errors.state}
                                            aria-describedby={errors.state ? 'state-error' : undefined}
                                            aria-required={stateConfig.isRequired}
                                            placeholder={stateConfig.stateLabel}
                                        />
                                    )}
                                    {errors.state && (
                                        <div
                                            id="state-error"
                                            className={styles.errorMessage}
                                            role="alert"
                                            aria-live="polite"
                                        >
                                            {errors.state}
                                        </div>
                                    )}
                                </div>
                            )}

                            <div className={styles.formField}>
                                <label htmlFor="zip" className={styles.label}>
                                    {__('Zip/Postal code', 'give')}
                                </label>
                                <input
                                    type="text"
                                    id="zip"
                                    className={`${styles.input} ${errors.zip ? styles.inputError : ''}`}
                                    value={address.zip}
                                    onChange={(e) => handleFieldChange('zip', e.target.value)}
                                    aria-invalid={!!errors.zip}
                                    aria-describedby={errors.zip ? 'zip-error' : undefined}
                                    aria-required="true"
                                />
                                {errors.zip && (
                                    <div
                                        id="zip-error"
                                        className={styles.errorMessage}
                                        role="alert"
                                        aria-live="polite"
                                    >
                                        {errors.zip}
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className={styles.buttons}>
                        <button
                            type="submit"
                            className={`button button-primary ${styles.addButton}`}
                            disabled={!isFormValid}
                            aria-describedby={!isFormValid ? 'submit-button-description' : undefined}
                        >
                            {isEditMode ? __('Update address', 'give') : __('Add address', 'give')}
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
                </form>
            </div>
        </ModalDialog>
    );
}
