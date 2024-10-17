import type {FormProps} from '@givewp/forms/propTypes';
import {__} from '@wordpress/i18n';

export default function Form({children, formProps, formError, isSubmitting}: FormProps) {
    const {useDonationFormSettings, useWatch} = window.givewp.form.hooks;
    const FormError = window.givewp.form.templates.layouts.formError;

    const {donateButtonCaption} = useDonationFormSettings();
    const gatewayId = useWatch({name: 'gatewayId'});

    return (
        <form {...formProps}>
            {children}

            <section className="givewp-layouts givewp-layouts-section">
                {formError && <FormError error={formError} />}

                {gatewayId !== 'paypal-commerce' && (
                    <button type="submit" disabled={isSubmitting} aria-busy={isSubmitting}>
                        {isSubmitting ? __('Submitting…', 'give') : donateButtonCaption}
                    </button>
                )}
            </section>
        </form>
    );
}
