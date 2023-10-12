import type {FieldErrorProps} from '@givewp/forms/propTypes';

/**
 * Conditionally renders a field's error message if a message is present.
 *
 * @since 3.0.0
 */
export default function FieldError({error, name}: FieldErrorProps) {
    if (!error) {
        return null;
    }

    return (
        <div id={`givewp-field-error-${name}`} className="givewp-field-error-message" role="alert">
            <p>{error}</p>
        </div>
    );
}
