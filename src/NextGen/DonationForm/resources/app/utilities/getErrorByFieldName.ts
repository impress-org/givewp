import {FieldErrors} from 'react-hook-form';

export default function getErrorByFieldName(errors: FieldErrors, name: string): string | null {
    return errors.hasOwnProperty(name) ? (errors[name].message as any) : null;
}
