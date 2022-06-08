import {FormProvider, useForm} from 'react-hook-form';
import {ErrorMessage} from '@hookform/error-message';
import {__} from '@wordpress/i18n';
import {joiResolver} from '@hookform/resolvers/joi';
import Joi from 'joi';

import Field from '../fields/Field';
import getFieldErrorMessages from '../utilities/getFieldErrorMessages';
import FieldSection from '../fields/FieldSection';
import getWindowData from '../utilities/getWindowData';
import PaymentDetails from '../fields/PaymentDetails';
import DonationReceipt from './DonationReceipt';
import {useGiveDonationFormStore} from '../store';
import type {Field as FieldInterface, Gateway} from '@givewp/forms/types';
import postData from "../utilities/postData";

const messages = getFieldErrorMessages();

const {donateUrl} = getWindowData();

const schema = Joi.object({
    firstName: Joi.string().required().label('First Name').messages(messages),
    lastName: Joi.string().required().label('Last Name').messages(messages),
    email: Joi.string().email({tlds: false}).required().label('Email').messages(messages),
    amount: Joi.number().integer().min(5).required().label('Donation Amount'),
    gatewayId: Joi.string().required().label('Payment Gateway').messages(messages),
    formId: Joi.number().required(),
    currency: Joi.string().required(),
    formTitle: Joi.string().required(),
    userId: Joi.number().required(),
}).unknown();

type PropTypes = {
    fields: FieldInterface[];
    defaultValues: object;
};

type FormInputs = {
    amount: number;
    firstName: string;
    lastName: string;
    email: string;
    gatewayId: string;
};

const handleSubmitRequest = async (values, setError, gateway: Gateway) => {
    let beforeCreatePaymentGatewayResponse = {};

    try {
        if (gateway.beforeCreatePayment) {
            beforeCreatePaymentGatewayResponse = await gateway.beforeCreatePayment(values);
        }
    } catch (error) {
        return setError('FORM_ERROR', {message: error.message});
    }

    const request = await postData(donateUrl, {
        ...values,
        ...beforeCreatePaymentGatewayResponse,
    });

    if (!request.response.ok) {
        return setError('FORM_ERROR', {message: "Something went wrong, please try again or contact support."});
    }

    try {
        if (gateway.afterCreatePayment) {
            await gateway.afterCreatePayment(request.data);
        }
    } catch (error) {
        return setError('FORM_ERROR', {message: error.message});
    }
};

export default function Form({fields, defaultValues}: PropTypes) {
    const {gateways} = useGiveDonationFormStore();

    const getGateway = (gatewayId) => gateways.find(({id}) => id === gatewayId);

    const methods = useForm<FormInputs>({
        defaultValues,
        resolver: joiResolver(schema),
    });

    const {
        handleSubmit,
        setError,
        getValues,
        formState: {errors, isSubmitting, isSubmitSuccessful},
    } = methods;

    if (isSubmitSuccessful) {
        const {amount, firstName, lastName, email, gatewayId} = getValues();
        const gateway = gateways.find(({id}) => id === gatewayId);

        return (
            <DonationReceipt
                amount={amount}
                email={email}
                firstName={firstName}
                lastName={lastName}
                gateway={gateway}
                status={'Complete'}
                total={amount}
            />
        );
    }

    return (
        <FormProvider {...methods}>
            <form
                id="give-next-gen"
                onSubmit={handleSubmit((values) => handleSubmitRequest(values, setError, getGateway(values.gatewayId)))}
            >
                {fields.map(({type, name, label, readOnly, validationRules, nodes}: FieldInterface) => {
                    if (name === 'paymentDetails') {
                        return <PaymentDetails gateways={gateways} name={name} label={label} key={name} />;
                    }

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

                <button type="submit" disabled={isSubmitting} className="give-next-gen__submit-button">
                    {isSubmitting ? __('Submitting…', 'give') : __('Donate', 'give')}
                </button>
            </form>
        </FormProvider>
    );
}
