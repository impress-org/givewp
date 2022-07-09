import {FieldProps} from '../index';

export default function Email({label, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            {label}
            <input type="email" {...inputProps} />
            {fieldError && <p>{fieldError}</p>}
        </label>
    );
}
