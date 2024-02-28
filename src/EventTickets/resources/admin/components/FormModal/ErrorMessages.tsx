import {FieldErrors} from 'react-hook-form';

export default function ErrorMessages({errors, errorMessages}: ErrorMessagesProps) {
    return (
        <>
            {Object.keys(errors).length > 0 && (
                <ul className="givewp-event-tickets__form-errors">
                    {Object.keys(errors).map((key) => (
                        <li key={key}>{errorMessages[key]}</li>
                    ))}
                </ul>
            )}
        </>
    );
}

interface ErrorMessagesProps {
    errors: FieldErrors;
    errorMessages: { [key: string]: string };
}
