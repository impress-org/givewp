import type {FieldProps} from '@givewp/forms/propTypes';

export default function Hidden({inputProps, fieldError}: FieldProps) {
    if (fieldError) {
        console.error(fieldError);
    }

    return <input type="hidden" {...inputProps} />;
}
