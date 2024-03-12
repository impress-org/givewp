import {FieldError, FieldErrors} from 'react-hook-form';

/**
 * Output error messages
 *
 * @unreleased
 */
export default function ErrorMessages({errors}: ErrorMessagesProps) {
    if (!(Object.values(errors).length > 0)) return null;
    const filteredErrors = Object.values(errors).filter((error) => !error?.message);

    if (filteredErrors.length === 0) return null;

    return (
        <>
            <ul className="givewp-event-tickets__form-errors">
                {filteredErrors.map((error: FieldError, key) => (
                    <li key={key}>{error?.message}</li>
                ))}
            </ul>
        </>
    );
}

interface ErrorMessagesProps {
    errors: FieldErrors;
}
