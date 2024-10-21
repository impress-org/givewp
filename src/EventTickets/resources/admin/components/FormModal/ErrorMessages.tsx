import {FieldError, FieldErrors} from 'react-hook-form';

/**
 * Output error messages
 *
 * @since 3.6.0
 */
export default function ErrorMessages({errors}: ErrorMessagesProps) {
    if (!(Object.values(errors).length > 0)) return null;

    return (
        <>
            <ul className="givewp-event-tickets__form-errors">
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
