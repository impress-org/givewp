import type {FormProps} from '@givewp/forms/propTypes';
import {__} from '@wordpress/i18n';

export default function Form({children, formProps, formError, isSubmitting}: FormProps) {
    const {donateButtonCaption} = window.givewp.form.hooks.useDonationFormSettings();

    return (
        <form {...formProps}>
            {children}

            <section className="givewp-layouts givewp-layouts-section">
                {formError && (
                    <div className="givewp-donation-form__errors">
                        <p className="givewp-donation-form__errors__description">
                            {__('The following error occurred when submitting the form:', 'give')}
                        </p>
                        <ul className="givewp-donation-form__errors__messages">
                            <li className="givewp-donation-form__errors__message">{formError}</li>
                        </ul>
                    </div>
                )}
                <button type="submit" disabled={isSubmitting} aria-busy={isSubmitting}>
                    {isSubmitting ? __('Submitting…', 'give') : donateButtonCaption}
                </button>
            </section>
        </form>
    );
}
