import React from 'react';
import {FieldError} from 'react-hook-form';
import styles from './style.module.scss';

interface ErrorFieldProps {
    error?: FieldError | any; // Allow any type to handle complex form state types
    children: React.ReactElement;
    className?: string;
}

/**
 * Reusable component for displaying validation errors on form fields
 *
 * @unreleased
 *
 * @param error - The field error from react-hook-form
 * @param children - The input/field component to wrap
 * @param className - Additional CSS classes
 *
 * @example
 * ```tsx
 * <ErrorField error={errors.email}>
 *     <input {...register('email')} />
 * </ErrorField>
 * ```
 */
export default function ErrorField({error, children, className = ''}: ErrorFieldProps) {
    // Clone the child element and add error styling
    const childWithError = React.cloneElement(children, {
        'aria-invalid': error ? 'true' : 'false',
        className: `${children.props.className || ''} ${
            error ? styles['givewp-error-field__input--error'] : ''
        } ${className}`.trim(),
    });

    return (
        <div className={styles['givewp-error-field']}>
            {childWithError}
            {error && <div className={styles['givewp-error-field__error-message']}>{error.message as string}</div>}
        </div>
    );
}
