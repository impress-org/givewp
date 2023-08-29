import type {FieldLabelProps} from '@givewp/forms/propTypes';
import {Fragment} from '@wordpress/element';

/**
 * The label for a field with the required indicator if applicable.
 *
 * @since 3.0.0
 */
export default function FieldLabel({label, required, as}: FieldLabelProps) {
    const Wrapper = as || Fragment;

    return (
        <Wrapper>
            {label}
            {required && (
                <>
                    {' '}
                    <span className="givewp-field-required" title="Field Required">
                        *
                    </span>
                </>
            )}
        </Wrapper>
    );
}
