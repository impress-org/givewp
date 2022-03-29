import Field from './Field';

/**
 * @unreleased
 *
 * @param name
 * @param label
 * @param fields
 * @returns {JSX.Element}
 */
export default function FieldGroup({name, label, fields}) {
    return (
        <fieldset aria-labelledby={name}>
            <div>
                <h2 id={name}>{label}</h2>
            </div>
            {fields.map(({type, name, label, readOnly, validationRules}) => (
                <Field
                    key={name}
                    label={label}
                    type={type}
                    name={name}
                    readOnly={readOnly}
                    required={validationRules?.required}
                />
            ))}
        </fieldset>
    );
}
