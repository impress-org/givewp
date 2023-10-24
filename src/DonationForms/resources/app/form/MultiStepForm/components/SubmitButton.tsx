import {__} from '@wordpress/i18n';

/**
 * @since 3.0.0
 */
export default function SubmitButton({isSubmitting, submittingText = __('Submitting…', 'give')}) {
    const {donateButtonCaption} = window.givewp.form.hooks.useDonationFormSettings();

    return (
        <button type="submit" disabled={isSubmitting} aria-busy={isSubmitting}>
            {isSubmitting ? submittingText : donateButtonCaption}
        </button>
    );
}
