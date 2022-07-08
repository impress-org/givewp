import {FieldProps} from '../index';

export default function TextArea({label, fieldError, inputProps}: FieldProps) {
    return (
        <label>
            {label}
            <textarea {...inputProps} />
            {fieldError && <p>{fieldError}</p>}
        </label>
    );
}
