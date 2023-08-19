import {
    PayPalButtons,
    PayPalHostedField,
    PayPalHostedFieldsProvider,
    PayPalScriptProvider,
    usePayPalHostedFields,
    usePayPalScriptReducer,
} from '@paypal/react-paypal-js';
import type {Gateway} from '@givewp/forms/types';
import {__} from '@wordpress/i18n';
import {debounce} from 'react-ace/lib/editorOptions';
import {Flex, TextControl} from '@wordpress/components';
import {CSSProperties, useEffect, useState} from 'react';

(() => {
    /**
     * Hoisted values are used to pass data in contexts where hooks cannot be used.
     * For example, the form uses hooks to get the values of the form fields,
     * but the callbacks for PayPal cannot use hooks to access those values.
     *
     * The <FormFieldsProvider /> component is used to hoist the form field values
     * which are then collected in createOrderHandler(). This callback is not
     * a component, so it cannot use hooks to access the form field values.
     */
    let amount;
    let firstName;
    let lastName;
    let email;
    let cardholderName;
    let hostedField;
    let payPalDonationsSettings;
    let payPalOrderId;
    let payPalSubscriptionId;

    let subscriptionFrequency;
    let subscriptionInstallments;
    let subscriptionPeriod;

    const buttonsStyle = {
        color: 'gold' as 'gold' | 'blue' | 'silver' | 'white' | 'black',
        label: 'paypal' as 'paypal' | 'checkout' | 'buynow' | 'pay' | 'installment' | 'subscribe' | 'donate',
        layout: 'vertical' as 'vertical' | 'horizontal',
        shape: 'rect' as 'rect' | 'pill',
        tagline: false,
    };

    const CUSTOM_FIELD_STYLE = {
        height: '50px', // @todo Magic number, but it works
        borderWidth: '.078rem',
        borderStyle: 'solid',
        borderColor: '#666',
        borderRadius: '.25rem',
        padding: '0 1.1875rem',
        width: '100%',
        marginBottom: '.5rem',
        boxSizing: 'inherit',
        inlineSize: '100%',
        backgroundColor: '#fff',
        color: '#4d4d4d',
        fontSize: '1rem',
        fontFamily: 'inherit',
        fontWeight: '500',
        lineHeight: '1.2',
    } as CSSProperties;

    const getFormData = () => {
        const formData = new FormData();

        formData.append('give-form-id', payPalDonationsSettings.donationFormId);
        formData.append('give-form-hash', payPalDonationsSettings.donationFormNonce);

        formData.append('give-amount', amount);

        formData.append('give-recurring-period', subscriptionPeriod);
        formData.append('period', subscriptionPeriod);
        formData.append('frequency', subscriptionFrequency);
        formData.append('times', subscriptionInstallments);

        formData.append('give_first', firstName);
        formData.append('give_last', lastName);
        formData.append('give_email', email);

        return formData;
    };

    const validateHostedFields = () => {
        return Object.values(hostedField.cardFields.getState().fields).some(
            (field: {isValid: boolean}) => field.isValid
        );
    };

    const createOrderHandler = async (): Promise<string> => {
        const response = await fetch(`${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_create_order`, {
            method: 'POST',
            body: getFormData(),
        });
        const responseJson = await response.json();

        if (!responseJson.success) {
            throw responseJson.data.error;
        }

        return (payPalOrderId = responseJson.data.id);
    };

    const createSubscriptionHandler = async (data, actions) => {
        // eslint-disable-next-line
        const response = await fetch(`${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_create_plan_id`, {
            method: 'POST',
            body: getFormData(),
        });

        const responseJson = await response.json();

        if (!responseJson.success) {
            throw responseJson.data.error;
        }

        return actions.subscription.create({plan_id: responseJson.data.id}).then((orderId) => {
            return payPalSubscriptionId = orderId;
        });
    };

    const Divider = ({label, style = {}}) => {
        const styles = {
            container: {
                fontSize: '16px',
                fontStyle: 'italic',
                display: 'flex',
                justifyContent: 'center',
                alignItems: 'center',
                ...style,
            },
            dashedLine: {
                border: '1px solid #d4d4d4',
                flexGrow: 1,
            },
            label: {
                padding: '0 6px',
                fontSize: '14px',
                color: '#8d8e8e',
            },
        };

        return (
            <div className="separator-with-text" style={styles.container}>
                <div className="dashed-line" style={styles.dashedLine} />
                <div className="label" style={styles.label}>
                    {label}
                </div>
                <div className="dashed-line" style={styles.dashedLine} />
            </div>
        );
    };

    const HoistHostedFieldContext = () => {
        hostedField = usePayPalHostedFields();
        return <></>;
    };

    const FormFieldsProvider = ({children}) => {
        const {useWatch} = window.givewp.form.hooks;
        amount = useWatch({name: 'amount'});
        firstName = useWatch({name: 'firstName'});
        lastName = useWatch({name: 'lastName'});
        email = useWatch({name: 'email'});


        subscriptionFrequency = useWatch({name: 'subscriptionFrequency'});
        subscriptionInstallments = useWatch({name: 'subscriptionInstallments'});
        subscriptionPeriod = useWatch({name: 'subscriptionPeriod'});

        return children;
    };

    const SmartButtonsContainer = () => {
        const {useWatch, useFormState} = window.givewp.form.hooks;
        const currency = useWatch({name: 'currency'});
        const donationType = useWatch({name: 'donationType'});

        const {isSubmitting, isSubmitSuccessful} = useFormState();

        const props = {
            style: buttonsStyle,
            disabled: isSubmitting || isSubmitSuccessful,
            forceReRender: debounce(() => [amount, firstName, lastName, email, currency], 500),
            onApprove: async (data, actions) => {

                if(donationType === 'subscription') {
                    // @ts-ignore
                    document.forms[0].querySelector('[type="submit"]').click();
                    return;
                }

                return actions.order.capture().then((details) => {
                    // @ts-ignore
                    document.forms[0].querySelector('[type="submit"]').click();
                });
            }
        }

        return donationType === 'subscription'
            // @ts-ignore
            ? <PayPalButtons {...props} createSubscription={createSubscriptionHandler} />
            // @ts-ignore
            : <PayPalButtons {...props} createOrder={createOrderHandler} />;
    };

    const HostedFieldsContainer = () => {
        const {useWatch} = window.givewp.form.hooks;
        const firstName = useWatch({name: 'firstName'});
        const lastName = useWatch({name: 'lastName'});
        const donationType = useWatch({name: 'donationType'});

        const cardholderDefault = [firstName ?? '', lastName ?? ''].filter((x) => x).join(' ');
        const [_cardholderName, setCardholderName] = useState(null);

        useEffect(() => {
            cardholderName = _cardholderName ?? cardholderDefault;
        });

        // Do not render hosted fields if disabled in admin settings.
        if( -1 === payPalDonationsSettings.sdkOptions['components'].indexOf('hosted-fields') ){
                        return;
        }

        /**
         * Hosted fields are not supported for subscriptions at this time.
         */
        const supportsHostedFields = donationType !== 'subscription';

        return (

                <PayPalHostedFieldsProvider
                    notEligibleError={<div>Your account is not eligible</div>}
                    createOrder={createOrderHandler}
                >
                    <div style={{ display: supportsHostedFields ? 'initial' : 'none'}}>
                    <Divider label={__('Or pay with card', 'give')} style={{padding: '30px 0'}} />

                    <TextControl
                        className="givewp-fields"
                        label={__('Cardholder Name', 'give')}
                        hideLabelFromVision={true}
                        placeholder={'Cardholder Name'}
                        value={_cardholderName ?? cardholderDefault}
                        onChange={(value) => setCardholderName(value)}
                    />

                    <PayPalHostedField
                        id="card-number"
                        className="card-field"
                        style={CUSTOM_FIELD_STYLE}
                        hostedFieldType="number"
                        options={{
                            selector: '#card-number',
                            placeholder: '4111 1111 1111 1111',
                        }}
                    />

                    <Flex gap="10px">
                        <PayPalHostedField
                            id="expiration-date"
                            className="givewp-fields"
                            style={CUSTOM_FIELD_STYLE}
                            hostedFieldType="expirationDate"
                            options={{
                                selector: '#expiration-date',
                                placeholder: 'MM/YYYY',
                            }}
                        />
                        <PayPalHostedField
                            id="cvv"
                            className="card-field"
                            style={CUSTOM_FIELD_STYLE}
                            hostedFieldType="cvv"
                            options={{
                                selector: '#cvv',
                                placeholder: 'CVV',
                                maskInput: true,
                            }}
                        />
                    </Flex>
                    <div style={{display: 'flex', gap: '10px'}}></div>

                    <HoistHostedFieldContext />

                    </div>
                </PayPalHostedFieldsProvider>

        );
    };

    function PaymentMethodsWrapper() {

        const {useWatch} = window.givewp.form.hooks;
        const currency = useWatch({name: 'currency'});
        const donationType = useWatch({name: 'donationType'});

        const [{options}, dispatch] = usePayPalScriptReducer();

        useEffect(() => {
            dispatch({
                type: 'resetOptions',
                value: {
                    ...options,
                    currency: currency,
                    vault: donationType === 'subscription',
                    intent: donationType === 'subscription' ? 'subscription' : 'capture',
                },
            });
        }, [currency, donationType]);

        return (
            <>
                <SmartButtonsContainer />
                <HostedFieldsContainer />
            </>
        );
    }

    const payPalCommerceGateway: Gateway = {
        id: 'paypal-commerce',
        initialize() {
            payPalDonationsSettings = this.settings;
        },
        beforeCreatePayment: async function (values): Promise<object> {
            if (payPalOrderId) {
                // If order ID already set by payment buttons then return early.
                return {
                    payPalOrderId: payPalOrderId,
                };
            }

            if(payPalSubscriptionId) {
                return {
                    payPalSubscriptionId: payPalSubscriptionId,
                }
            }

            if (!validateHostedFields()) {
                throw new Error('Invalid hosted fields');
            }

            return hostedField.cardFields
                .submit({cardholderName: cardholderName})
                .then(async (data) => {
                    await fetch(
                        `${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_approve_order&order=` +
                            data.orderId,
                        {
                            method: 'POST',
                            body: getFormData(),
                        }
                    );
                    return {...data, payPalOrderId: data.orderId};
                })
                .catch((err) => {
                    console.log('paypal commerce error', err);
                    throw new Error('paypal commerce error');
                });
        },
        Fields() {
            return (
                <FormFieldsProvider>
                    <PayPalScriptProvider
                        deferLoading={true}
                        options={payPalDonationsSettings.sdkOptions}
                    >
                        <PaymentMethodsWrapper />
                    </PayPalScriptProvider>
                </FormFieldsProvider>
            );
        },
    };

    window.givewp.gateways.register(payPalCommerceGateway);
})();
