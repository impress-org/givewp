import type {FieldLabelProps} from '@givewp/forms/propTypes';

/**
 * The label for a field with the required indicator if applicable.
 *
 * @since 3.0.0
 */
export default function FieldLabel({label, required}: FieldLabelProps) {
    return (
        <span className="givewp-fields__label-text">
            {label}
            {required && (
                <>
                    {' '}
                    <span className="givewp-field-required" title="Field Required">
                        *
                    </span>
                </>
            )}
        </span>
    );
}
