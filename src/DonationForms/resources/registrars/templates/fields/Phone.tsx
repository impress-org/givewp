import InputMask from 'react-input-mask';

import {PhoneProps} from '@givewp/forms/propTypes';

export default function Phone({Label, ErrorMessage, fieldError, description, phoneFormat, inputProps}: PhoneProps) {
    const FieldDescription = window.givewp.form.templates.layouts.fieldDescription;

    return (
        <label>
            <Label />
            {description && <FieldDescription description={description} />}
            {phoneFormat === 'domestic' ? (
                <InputMask type={'phone'} {...inputProps} mask={'(999) 999-9999'} />
            ) : (
                <input type={'phone'} {...inputProps} />
            )}

            <ErrorMessage />
        </label>
    );
}
