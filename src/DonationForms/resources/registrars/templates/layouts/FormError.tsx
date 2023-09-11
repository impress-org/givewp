import { __ } from "@wordpress/i18n";

export default function FormError({error}: {error: string}) {
    return (
        <div className="givewp-donation-form__errors">
            <p className="givewp-donation-form__errors__description">
                {__('The following error occurred when submitting the form:', 'give')}
            </p>
            <ul className="givewp-donation-form__errors__messages">
                <li className="givewp-donation-form__errors__message">{error}</li>
            </ul>
        </div>
    );
}
