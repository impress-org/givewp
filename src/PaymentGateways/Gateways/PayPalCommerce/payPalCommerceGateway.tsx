import {
    PayPalButtons,
    PayPalHostedField,
    PayPalHostedFieldsProvider,
    PayPalScriptProvider,
    usePayPalHostedFields,
    usePayPalScriptReducer,
} from '@paypal/react-paypal-js';
import type {Gateway} from '@givewp/forms/types';
import {__, sprintf} from '@wordpress/i18n';
import {debounce} from 'react-ace/lib/editorOptions';
import {Flex, TextControl} from '@wordpress/components';
import {CSSProperties, useEffect, useState} from 'react';
import {PayPalSubscriber} from "./types";

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

    let country;
    let state;
    let city;
    let addressLine1;
    let addressLine2;
    let postalCode;

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

    /**
     * Get PayPal script options.
     *
     * This function return the paypal script options on basis of context.
     *  - If donation type is subscription then remove hosted fields from components.
     *
     *  @return {object} PayPal script options.
     */
    const getPayPalScriptOptions = ({isSubscription}) => {
        let paypalScriptOptions = {...payPalDonationsSettings.sdkOptions};

        // Remove hosted fields from components if subscription.
        if( isSubscription  && -1 !== paypalScriptOptions.components.indexOf('hosted-fields') ){
            paypalScriptOptions.components = paypalScriptOptions.components.split(',')
                .filter((component) => component !== 'hosted-fields')
                .join(',');
        }

        return paypalScriptOptions;
    }

    const getFormData = () => {
        const formData = new FormData();

        formData.append('give-form-id', payPalDonationsSettings.donationFormId);
        formData.append('give-form-hash', payPalDonationsSettings.donationFormNonce);

        formData.append('give_payment_mode', 'paypal-commerce');

        formData.append('give-amount', amount);

        formData.append('give-recurring-period', subscriptionPeriod);
        formData.append('period', subscriptionPeriod);
        formData.append('frequency', subscriptionFrequency);
        formData.append('times', subscriptionInstallments);

        formData.append('give_first', firstName);
        formData.append('give_last', lastName);
        formData.append('give_email', email);

        if( country ) {
            formData.append('card_address', addressLine1);
            formData.append('card_address_2', addressLine2);
            formData.append('card_city', city);
            formData.append('card_state', state);
            formData.append('card_zip', postalCode);
            formData.append('billing_country', country);
        }

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

        const subscriberData: PayPalSubscriber = {
            "name": {
                "given_name": firstName,
                "surname": lastName
            },
            "email_address": email,
        };

        if (country) {
            subscriberData.shipping_address = {
                name: {
                    "full_name": `${firstName} ${lastName}`.trim()
                },
                address: {
                    "address_line_1": addressLine1,
                    "address_line_2": addressLine2,
                    "admin_area_2": city,
                    "admin_area_1": state,
                    "postal_code": postalCode,
                    "country_code": country
                }
            };
        }

        return actions.subscription.create({
            "plan_id": responseJson.data.id,
            "subscriber": subscriberData
        }).then((orderId) => {
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

        addressLine1 = useWatch({name: 'address1'});
        addressLine2 = useWatch({name: 'address2'});
        city = useWatch({name: 'city'});
        state = useWatch({name: 'state'});
        postalCode = useWatch({name: 'zip'});
        country = useWatch({name: 'country'});

        return children;
    };

    const SmartButtonsContainer = () => {
        const {useWatch, useFormState} = window.givewp.form.hooks;
        const currency = useWatch({name: 'currency'});
        const donationType = useWatch({name: 'donationType'});
        const {isSubmitting, isSubmitSuccessful} = useFormState();
        const {useFormContext} = window.givewp.form.hooks;
        const {getFieldState, setFocus, getValues, formState: {errors}, trigger, setError} = useFormContext();
        const gateway = window.givewp.gateways.get('paypal-commerce');

        const props = {
            style: buttonsStyle,
            disabled: isSubmitting || isSubmitSuccessful,
            forceReRender: debounce(() => [amount, firstName, lastName, email, currency], 500),
            onClick: async (data, actions) => {
                // Validate whether payment gateway support subscriptions.
                if (donationType === 'subscription' && !gateway.supportsSubscriptions) {
                    setError('FORM_ERROR', {
                        message: __(
                            'This payment gateway does not support recurring payments, please try selecting another payment gateway.',
                            'give'
                        )
                    },
                        {shouldFocus: true}
                    );

                    // Scroll to the top of the form.
                    // Add this moment we do not have a way to scroll to the error message.
                    // In the future we can add a way to scroll to the error message and remove this code.
                    document.querySelector('#give-next-gen button[type="submit"]')
                        .scrollIntoView({behavior: 'smooth'});

                    return actions.reject();

                }

                // Validate the form values before proceeding.
                const result = await trigger();
                if(result === false){
                    // Set focus on first invalid field.
                                       for (const fieldName in getValues()) {
                        if(getFieldState(fieldName).invalid){
                            setFocus(fieldName);
                        }
                                       }
                    return actions.reject();
                }

                return actions.resolve();
            },
            onApprove: async (data, actions) => {
                const donationFormWithSubmitButton = Array.from(document.forms).pop();
                const submitButton = donationFormWithSubmitButton.querySelector('[type="submit"]');

                if(donationType === 'subscription') {
                    // @ts-ignore
                    submitButton.click();
                    return;
                }

                return actions.order.capture().then((details) => {
                    // @ts-ignore
                    submitButton.click();
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

        const cardholderDefault = [firstName ?? '', lastName ?? ''].filter((x) => x).join(' ');
        const [_cardholderName, setCardholderName] = useState(null);

        useEffect(() => {
            cardholderName = _cardholderName ?? cardholderDefault;
        });

        return (

                <PayPalHostedFieldsProvider createOrder={createOrderHandler}>
                    <div>
                    <Divider label={__('Or pay with card', 'give')} style={{padding: '30px 0'}} />

                    <TextControl
                        className="givewp-fields"
                        label={__('Cardholder Name', 'give')}
                        hideLabelFromVision={true}
                        placeholder={__('Cardholder Name', 'give')}
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
                                placeholder: __('MM/YYYY', 'give'),
                            }}
                        />
                        <PayPalHostedField
                            id="cvv"
                            className="card-field"
                            style={CUSTOM_FIELD_STYLE}
                            hostedFieldType="cvv"
                            options={{
                                selector: '#cvv',
                                placeholder: __('CVV', 'give'),
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
            const isSubscription = donationType === 'subscription';

            dispatch({
                type: 'resetOptions',
                value: {
                    ...getPayPalScriptOptions({isSubscription}),
                    currency: currency,
                    vault: donationType === 'subscription',
                    intent: donationType === 'subscription' ? 'subscription' : 'capture',
                },
            });
        }, [currency, donationType]);

        return (
            <>
                <SmartButtonsContainer />
                { -1 !== options.components.indexOf('hosted-fields')  && <HostedFieldsContainer /> }
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

            const approveOrderCallback = async (data) => {
                await fetch(
                    `${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_approve_order&order=` +
                    data.orderId,
                    {
                        method: 'POST',
                        body: getFormData(),
                    }
                );
                return {...data, payPalOrderId: data.orderId};
            };

            try{
                const result = await hostedField.cardFields
                    .submit({
                        // Trigger 3D Secure authentication
                        contingencies: [ 'SCA_WHEN_REQUIRED' ],
                        cardholderName: cardholderName
                    });


                if (
                    ! result // Check whether get result from paypal gateway server.
                    || (
                        [ 'NO', 'POSSIBLE' ].includes( result.liabilityShift ) // Check whether card required 3D secure validation.
                        && !  (result.liabilityShifted && 'POSSIBLE' === result.liabilityShift) // Check whether card passed 3D secure validation.
                    )
                ) {
                    throw new Error(__(
                        'There was a problem authenticating your payment method. Please try again. If the problem persists, please try another payment method.',
                        'give'
                    ));
                }

                return await approveOrderCallback(result);
            } catch (err) {
                console.log('paypal donations error', err);

                // Handle PayPal error.
                const isPayPalDonationError = err.hasOwnProperty('details');
                if( isPayPalDonationError ){
                                        throw new Error(err.details[0].description);
                }

                throw new Error(
                    sprintf(
                        __('Paypal Donations Error: %s', 'give'),
                        err.message
                    )
                );
            }
        },
        Fields() {
            const {useWatch} = window.givewp.form.hooks;
            const donationType = useWatch({name: 'donationType'});
            const isSubscription = donationType === 'subscription';

            return (
                <FormFieldsProvider>
                    <PayPalScriptProvider
                        deferLoading={true}
                        options={getPayPalScriptOptions({isSubscription})}
                    >
                        <PaymentMethodsWrapper />
                    </PayPalScriptProvider>
                </FormFieldsProvider>
            );
        },
    };

    window.givewp.gateways.register(payPalCommerceGateway);
})();
