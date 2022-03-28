import {FormProvider, useForm} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';
import PropTypes from 'prop-types';
import {__} from '@wordpress/i18n';
import {joiResolver} from '@hookform/resolvers/joi';
import Joi from 'joi';

import Field from '../fields/Field';
import getFieldErrorMessages from '../utilities/getFieldErrorMessages';

const messages = getFieldErrorMessages();

const schema = Joi.object({
    firstName: Joi.string().required().label('First Name').messages(messages),
    lastName: Joi.string().required().label('Last Name').messages(messages),
    email: Joi.string().email({tlds: false}).required().label('Email').messages(messages),
    donationAmount: Joi.number().integer().min(5).required().label('Donation Amount'),
    formId: Joi.number().required(),
});

function Form({fields, defaultValues}) {
    const isLoading = false;
    const onSubmit = (data) => console.log(data);

    const methods = useForm({
        defaultValues,
        resolver: joiResolver(schema),
    });

    const {
        handleSubmit,
        setError,
        formState: {errors, isSubmitting},
    } = methods;

    return (
        <FormProvider {...methods}>
            <form className="give-next-gen" onSubmit={handleSubmit(onSubmit)}>
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

                <ErrorMessage
                    errors={errors}
                    name="FORM_ERROR"
                    render={({message}) => (
                        <div style={{textAlign: 'center'}}>
                            <p className="give-next-gen__error-message">
                                {__('The following error occurred when submitting the form:', 'give-text-to-give')}
                            </p>
                            <p className="give-next-gen__error-message">{message}</p>
                        </div>
                    )}
                />

                <button type="submit" disabled={isSubmitting || isLoading} className="give-next-gen__submit-button">
                    {isSubmitting || isLoading ? __('Submitting…', 'give') : __('donate', 'give')}
                </button>
            </form>
        </FormProvider>
    );
}

Form.propTypes = {
    /** Form fields */
    fields: PropTypes.array.isRequired,
    /** Form default values */
    defaultValues: PropTypes.object.isRequired,
};

export default Form;
