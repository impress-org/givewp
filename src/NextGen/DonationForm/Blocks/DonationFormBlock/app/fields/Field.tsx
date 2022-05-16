import PropTypes from 'prop-types';
import {useFormContext, useFormState} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';

function Field({type, name, label, required, ...rest}) {
    const {register} = useFormContext();
    const {errors} = useFormState();
    const inputId = `give-${name}`;
    const isAmount = name === 'donationAmount';

    if (type === 'hidden') {
        return <input type="hidden" id={inputId} {...register(name, {required})} {...rest} />;
    }

    if (type === 'html') {
        return <div id={inputId} dangerouslySetInnerHTML={rest.html} />;
    }

    return (
        <div className="give-next-gen__field">
            <label className="give-next-gen__label" htmlFor={inputId}>
                {label}
            </label>

            <div className="give-next-gen__input">
                {isAmount && (
                    <div className="give-next-gen__input-adornment give-next-gen__input-adornment--left">{'USD'}</div>
                )}
                <input
                    type={type}
                    id={inputId}
                    inputMode={isAmount ? 'decimal' : undefined}
                    required={required}
                    {...register(name, {required})}
                    {...rest}
                />
            </div>

            <ErrorMessage
                errors={errors}
                name={name}
                render={({message}) => <span className="give-next-gen__error-message">{message}</span>}
            />
        </div>
    );
}

Field.defaultProps = {
	label: null,
};

Field.propTypes = {
	/** Field type */
	type: PropTypes.string.isRequired,

	/** Field name */
	name: PropTypes.string.isRequired,

	/** Field validation rules */
	required: PropTypes.bool,

	/** Field label */
	label: PropTypes.string,
};

export default Field;
