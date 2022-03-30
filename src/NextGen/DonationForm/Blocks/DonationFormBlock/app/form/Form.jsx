import {useEffect} from 'react';
import {FormProvider, useForm} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';
import PropTypes from 'prop-types';
import {__} from '@wordpress/i18n';
import {joiResolver} from '@hookform/resolvers/joi';
import Joi from 'joi';

import Field from '../fields/Field';
import getFieldErrorMessages from '../utilities/getFieldErrorMessages';
import FieldGroup from '../fields/FieldGroup';
import axios from 'axios';
import getWindowData from '../utilities/getWindowData';

const messages = getFieldErrorMessages();

const [donateUrl] = getWindowData('donateUrl');

const schema = Joi.object({
    firstName: Joi.string().required().label('First Name').messages(messages),
    lastName: Joi.string().required().label('Last Name').messages(messages),
    email: Joi.string().email({tlds: false}).required().label('Email').messages(messages),
    amount: Joi.number().integer().min(5).required().label('Donation Amount'),
    formId: Joi.number().required(),
    currency: Joi.string().required(),
    formTitle: Joi.string().required(),
    userId: Joi.number().required(),
});

/**
 * Handle submit request
 *
 * @param {Array} values - form values
 * @return {Promise} - Axios
 */
const handleSubmitRequest = async (values) => {
    const request = await axios.post(donateUrl, {
        ...values,
        gatewayId: 'test-gateway',
    });

    if (request.status === 200) {
        alert('Thank You!');
    }
};

/**
 * @unreleased
 *
 * @param fields
 * @param defaultValues
 * @returns {JSX.Element}
 */
function Form({fields, defaultValues}) {
    const isLoading = false;

    const methods = useForm({
        defaultValues,
        resolver: joiResolver(schema),
    });

    const {
        handleSubmit,
        setError,
        formState: {errors, isSubmitting, isSubmitSuccessful},
        reset,
    } = methods;

    useEffect(() => {
        reset();
    }, [isSubmitSuccessful]);

    return (
        <FormProvider {...methods}>
            <form className="give-next-gen" onSubmit={handleSubmit(handleSubmitRequest)}>
                {fields.map(({type, name, label, readOnly, validationRules, nodes}) => {
                    if (type === 'group' && nodes) {
                        return <FieldGroup fields={nodes} name={name} label={label} key={name} />;
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

                <ErrorMessage
                    errors={errors}
                    name="FORM_ERROR"
                    render={({message}) => (
                        <div style={{textAlign: 'center'}}>
                            <p className="give-next-gen__error-message">
                                {__('The following error occurred when submitting the form:', 'give')}
                            </p>
                            <p className="give-next-gen__error-message">{message}</p>
                        </div>
                    )}
                />

                <button type="submit" disabled={isSubmitting || isLoading} className="give-next-gen__submit-button">
                    {isSubmitting || isLoading ? __('Submitting…', 'give') : __('Donate', 'give')}
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
