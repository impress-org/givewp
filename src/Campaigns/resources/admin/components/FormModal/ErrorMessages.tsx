import {FieldError, FieldErrors} from 'react-hook-form';

/**
 * Output error messages
 *
 * @unreleased
 */
export default function ErrorMessages({errors}: ErrorMessagesProps) {
    if (!(Object.values(errors).length > 0)) return null;

    return (
        <>
            <ul className="givewp-campaigns__form-errors">
                {Object.values(errors).map((error: FieldError, key) => (
                    <li key={key}>{error?.message}</li>
                ))}
            </ul>
        </>
    );
}

interface ErrorMessagesProps {
    errors: FieldErrors;
}
