import {Field as FieldInterface} from '@givewp/forms/types';
import Field from './Field';

type Props = {
    name: string;
    label: string;
    fields: FieldInterface[];
};

export default function FieldSection({name, label, fields}: Props) {
    return (
        <fieldset aria-labelledby={name}>
            <div>
                <h2 id={name}>{label}</h2>
            </div>
            {fields.map(({type, name, label, readOnly, validationRules, nodes}) => {
                if (type === 'section' && nodes) {
                    return <FieldSection fields={nodes} name={name} label={label} key={name} />;
                }

                return (
                    <Field
                        key={name}
                        label={label}
                        type={type}
                        name={name}
                        readOnly={readOnly}
                        required={validationRules?.required}
                    />
                );
            })}
        </fieldset>
    );
}
