import {FieldProps} from '../index';

export default function Hidden({inputProps}: FieldProps) {
    return <input type="hidden" {...inputProps} />;
}
