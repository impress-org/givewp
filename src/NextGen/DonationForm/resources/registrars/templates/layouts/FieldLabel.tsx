import type {FieldLabelProps} from '@givewp/forms/propTypes';

/**
 * The label for a field with the required indicator if applicable.
 *
 * @unreleased
 */
export default function FieldLabel({label, required}: FieldLabelProps) {
    return (
        <span>
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
