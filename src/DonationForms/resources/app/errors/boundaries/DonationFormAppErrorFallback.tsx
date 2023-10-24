import {__} from "@wordpress/i18n";

/**
 * @since 3.0.0
 */
export default function DonationFormAppErrorFallback({error, resetErrorBoundary}) {
    const {useFormState, useFormContext} = window.givewp.form.hooks;
    const {errors} = useFormState();
    const {getValues} = useFormContext();

    // console log more information about the form for debugging.
    console.info({donationFormErrors: errors, donationFormValues: getValues()});

    return (
        <div role="alert">
            <p>
                {__(
                    'An error occurred in the form.  Please notify the site administrator.  The error message is:',
                    'give'
                )}
            </p>
            <pre style={{padding: '0.5rem'}}>{error.message}</pre>
            <button type="button" onClick={resetErrorBoundary}>
                {__('Reload form', 'give')}
            </button>
        </div>
    );
}
