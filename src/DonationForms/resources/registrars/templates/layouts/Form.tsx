import type {FormProps} from '@givewp/forms/propTypes';
import {__} from '@wordpress/i18n';

export default function Form({children, formProps, formError, isSubmitting}: FormProps) {
    const {donateButtonCaption} = window.givewp.form.hooks.useDonationFormSettings();
    const FormError = window.givewp.form.templates.layouts.formError;

    return (
        <form {...formProps}>
            {children}

            <section className="givewp-layouts givewp-layouts-section">
                {formError && <FormError error={formError} />}

                <button type="submit" disabled={isSubmitting} aria-busy={isSubmitting}>
                    {isSubmitting ? __('Submitting…', 'give') : donateButtonCaption}
                </button>
            </section>
        </form>
    );
}
