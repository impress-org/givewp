import type {FormProps} from '@givewp/forms/propTypes';
import {__} from '@wordpress/i18n';

export default function Form({children, formProps, formError, isSubmitting}: FormProps) {
    return (
        <form {...formProps}>
            {children}
            {formError && (
                <div style={{textAlign: 'center'}}>
                    <p>{__('The following error occurred when submitting the form:', 'give')}</p>
                    <p>{formError}</p>
                </div>
            )}
            <section className="givewp-layouts givewp-layouts-section">
                <button type="submit" disabled={isSubmitting} aria-busy={isSubmitting}>
                    {isSubmitting ? __('Submitting…', 'give') : __('Donate', 'give')}
                </button>
            </section>
        </form>
    );
}
