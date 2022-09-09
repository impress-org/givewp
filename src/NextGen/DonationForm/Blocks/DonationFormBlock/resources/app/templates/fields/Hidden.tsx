import type {FieldProps} from '@givewp/forms/propTypes';

export default function Hidden({inputProps}: FieldProps) {
    return <input type="hidden" {...inputProps} />;
}
