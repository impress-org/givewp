import {FieldProps} from '../index';

export default function Text({label, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            {label}
            <input type="text" {...inputProps} />
            {fieldError && <p>{fieldError}</p>}
        </label>
    );
}
