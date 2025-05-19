import {__} from '@wordpress/i18n';

/**
 * @since 4.3.0 Add proper roles and ARIA attributes
 */
export default function FormError({error}: {error: string}) {
    return (
        <div role="alert" aria-live="assertive" className="givewp-donation-form__errors">
            <p className="givewp-donation-form__errors__description">
                {__('The following error occurred when submitting the form:', 'give')}
            </p>
            <ul className="givewp-donation-form__errors__messages">
                <li className="givewp-donation-form__errors__message">{error}</li>
            </ul>
        </div>
    );
}
