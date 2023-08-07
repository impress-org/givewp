import type {RadioFieldProps} from '@givewp/forms/propTypes';

export default function Radio({Label, ErrorMessage, options, inputProps}: RadioFieldProps) {
    return (
        options.length > 0 && (
            <fieldset>
                <legend>
                    <Label />
                </legend>
                <div className="givewp-fields-radio__options">
                    {options.map(({value, label}, index) => {
                        const optionId = inputProps.name + '_' + index;
                        return <div key={index} className="givewp-fields-radio__option-container">
                            <input type="radio" id={optionId} name={inputProps.name} value={value} {...inputProps} />
                            <label htmlFor={optionId}>{label}</label>
                        </div>
                    })}
                </div>

                <ErrorMessage />
            </fieldset>
        )
    );
}
