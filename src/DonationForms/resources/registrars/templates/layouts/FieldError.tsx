import type {FieldErrorProps} from '@givewp/forms/propTypes';

/**
 * Conditionally renders a field's error message if a message is present.
 *
 * @since 3.0.0
 */
export default function FieldError({error}: FieldErrorProps) {
    if (!error) {
        return null;
    }

    return (
        <div className="error-message">
            <p role="alert">{error}</p>
        </div>
    );
}
