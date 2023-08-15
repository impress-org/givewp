import {__} from '@wordpress/i18n';

/**
 * @since 0.4.0
 */
export default function SubmitButton({
    isSubmitting,
    submittingText = __('Submitting…', 'give'),
    buttonText = __('Donate Now', 'give'),
}) {
    return (
        <button type="submit" disabled={isSubmitting} aria-busy={isSubmitting}>
            {isSubmitting ? submittingText : buttonText}
        </button>
    );
}
