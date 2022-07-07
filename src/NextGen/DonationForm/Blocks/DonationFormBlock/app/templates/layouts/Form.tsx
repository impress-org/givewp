import {FormHTMLAttributes, ReactNode} from 'react';
import {__} from '@wordpress/i18n';

export interface FormProps {
    formProps: FormHTMLAttributes<unknown>;
    children: ReactNode;
    formError: string | null;
    isSubmitting: boolean;
}

export default function Form({children, formProps, formError, isSubmitting}: FormProps) {
    return (
        <form {...formProps}>
            {children}
            {formError && (
                <div style={{textAlign: 'center'}}>
                    <p>
                        {__('The following error occurred when submitting the form:', 'give')}
                    </p>
                    <p>{formError}</p>
                </div>
            )}
            <button type="submit" disabled={isSubmitting}>
                {isSubmitting ? __('Submitting…', 'give') : __('Donate', 'give')}
            </button>
        </form>
    );
}
