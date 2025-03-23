import {
    PayPalButtons,
    PayPalHostedField,
    PayPalHostedFieldsProvider,
    PayPalScriptProvider,
    usePayPalHostedFields,
    usePayPalScriptReducer
} from '@paypal/react-paypal-js';
import type {PayPalButtonsComponentProps, PayPalHostedFieldsComponentProps} from '@paypal/react-paypal-js';
import {__, sprintf} from '@wordpress/i18n';
import {Flex, TextControl} from '@wordpress/components';
import {CSSProperties, useEffect, useState} from 'react';
import {PayPalCommerceGateway, PayPalSubscriber} from './types';
import handleValidationRequest from '@givewp/forms/app/utilities/handleValidationRequest';
import createOrder from './resources/js/createOrder';
import type {
    CreateSubscriptionRequestBody,
    PayPalButtonsComponentOptions
} from '@paypal/paypal-js';
import createSubscriptionPlan from './resources/js/createSubscriptionPlan';

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
    let feeRecovery;
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

    let currency;
    let eventTickets;
    let submitButton;

    /**
     * @since 3.12.2
     */
    const getEventTicketsTotalAmount = (
        eventTickets: Array<{
            ticketId: number;
            quantity: number;
            amount: number;
        }>
    ) => {
        const totalAmount = eventTickets.reduce((accumulator, eventTicket) => accumulator + eventTicket.amount, 0);
        if (totalAmount > 0) {
            return totalAmount / 100;
        } else {
            return 0;
        }
    };

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
        if (isSubscription && -1 !== paypalScriptOptions.components.indexOf('hosted-fields')) {
            paypalScriptOptions.components = paypalScriptOptions.components
                .split(',')
                .filter((component) => component !== 'hosted-fields')
                .join(',');
        }

        return paypalScriptOptions;
    };

    /**
     * Get amount with fee (if any).
     *
     * @since 3.6.1 Append 'give-cs-form-currency' to formData
     * @since 3.2.0
     * @return {number} Amount with fee.
     */
    const getAmount = () => {
        const feeAmount = feeRecovery ? feeRecovery : 0;
        let amountWithFee = amount + feeAmount;
        amountWithFee = Math.round(amountWithFee * 100) / 100;

        return amountWithFee;
    };

    const getFormData = () => {
        const formData = new FormData();

        formData.append('give-form-id', payPalDonationsSettings.donationFormId);
        formData.append('give-form-hash', payPalDonationsSettings.donationFormNonce);

        formData.append('give_payment_mode', 'paypal-commerce');

        const eventTicketsTotalAmount = eventTickets ? getEventTicketsTotalAmount(JSON.parse(eventTickets)) : 0;
        const isSubscription = subscriptionPeriod ? subscriptionPeriod !== 'one-time' : false;
        if (!isSubscription) {
            formData.append('give-amount', getAmount() + eventTicketsTotalAmount);
        } else {
            formData.append('give-amount', getAmount()); // We don't want to charge the event tickets for each subscription renewal
        }

        formData.append('give-event-tickets-total-amount', String(eventTicketsTotalAmount));

        formData.append('give-recurring-period', subscriptionPeriod);
        formData.append('period', subscriptionPeriod);
        formData.append('frequency', subscriptionFrequency);
        formData.append('times', subscriptionInstallments);

        formData.append('give_first', firstName);
        formData.append('give_last', lastName);
        formData.append('give_email', email);

        if (country) {
            formData.append('card_address', addressLine1);
            formData.append('card_address_2', addressLine2);
            formData.append('card_city', city);
            formData.append('card_state', state);
            formData.append('card_zip', postalCode);
            formData.append('billing_country', country);
        }

        /**
         * Ensure the proper currency will be used when using the Currency Switcher add-on.
         */
        formData.append('give-cs-form-currency', currency);

        return formData;
    };

    const validateHostedFields = () => {
        return Object.values(hostedField.cardFields.getState().fields).some(
            (field: {isValid: boolean}) => field.isValid
        );
    };

    const createOrderHandler: PayPalHostedFieldsComponentProps['createOrder'] = async (): Promise<string> => {
        return await createOrder(`${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_create_order`, payPalCommerceGateway, getFormData());
    };

    const createSubscriptionHandler: PayPalButtonsComponentOptions['createSubscription'] = async (data, actions) => {
        const {planId, userAction} = await createSubscriptionPlan(`${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_create_plan_id`, payPalCommerceGateway, getFormData());

        const subscriberData: PayPalSubscriber = {
            name: {
                given_name: firstName,
                surname: lastName,
            },
            email_address: email,
        };

        if (country) {
            subscriberData.shipping_address = {
                name: {
                    full_name: `${firstName} ${lastName}`.trim(),
                },
                address: {
                    address_line_1: addressLine1,
                    address_line_2: addressLine2,
                    admin_area_2: city,
                    admin_area_1: state,
                    postal_code: postalCode,
                    country_code: country,
                },
            };
        }

        const createSubscriptionPayload: CreateSubscriptionRequestBody = {
            plan_id: planId,
            subscriber: subscriberData,
        };

        if (userAction) {
            createSubscriptionPayload.application_context = {
                user_action: userAction
            }
        }

        return actions.subscription
            .create(createSubscriptionPayload)
            .then((orderId) => {
                return (payPalSubscriptionId = orderId);
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
        feeRecovery = useWatch({name: 'feeRecovery'});
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

        currency = useWatch({name: 'currency'});

        eventTickets = useWatch({name: 'event-tickets'});

        return children;
    };

    const SmartButtonsContainer = () => {
        const {useWatch, useFormState} = window.givewp.form.hooks;
        const donationType = useWatch({name: 'donationType'});
        const {isSubmitting, isSubmitSuccessful} = useFormState();
        const {useFormContext} = window.givewp.form.hooks;
        const {
            getFieldState,
            setFocus,
            getValues,
            formState: {errors},
            trigger,
            setError,
        } = useFormContext();
        const gateway = window.givewp.gateways.get('paypal-commerce') as PayPalCommerceGateway;

        const props: PayPalButtonsComponentProps = {
            style: buttonsStyle,
            disabled: isSubmitting || isSubmitSuccessful,
            forceReRender: [donationType, amount, feeRecovery, firstName, lastName, email, currency],
            onClick: async (data, actions) => {
                // Validate whether payment gateway support subscriptions.
                if (donationType === 'subscription' && !gateway.supportsSubscriptions) {
                    setError(
                        'FORM_ERROR',
                        {
                            message: __(
                                'This payment gateway does not support recurring payments, please try selecting another payment gateway.',
                                'give'
                            ),
                        },
                        {shouldFocus: true}
                    );

                    // Scroll to the top of the form.
                    // Add this moment we do not have a way to scroll to the error message.
                    // In the future we can add a way to scroll to the error message and remove this code.
                    submitButton.scrollIntoView({behavior: 'smooth'});

                    return actions.reject();
                }

                // Validate the form values in the client side before proceeding.
                const isClientValidationSuccessful = await trigger();
                if (!isClientValidationSuccessful) {
                    // Set focus on first invalid field.
                    for (const fieldName in getValues()) {
                        if (getFieldState(fieldName).invalid) {
                            setFocus(fieldName);
                        }
                    }
                    return actions.reject();
                }

                /**
                 * Validate the form values in the server side before proceeding - this is important to prevent problems with some blocks.
                 *
                 * Ideally, the client-side validations should be enough. However, in some cases, these validations are reached
                 * later when the donation is already created on the PayPal side. This way, we need the request below to check
                 * it earlier and prevent the donation creation on the PayPal side if the required fields are missing.
                 *
                 * Know cases:
                 *
                 * #1 - Billing Address Block: depending on the selected country, the city, state, and zip fields
                 * can be required or not and there are custom validation rules on the server side that check it.
                 *
                 * #2 - Gift Aid Block: when users opt-in to the gift aid checkbox, it will display some
                 * required fields that should be filled, but as this block is not a group and even so has
                 * "children" fields, the validation rules for it live only on the server.
                 */
                const isServerValidationSuccessful = await handleValidationRequest(
                    payPalDonationsSettings.validateUrl,
                    getValues(),
                    setError,
                    gateway
                );

                if (!isServerValidationSuccessful) {
                    return actions.reject();
                }

                return actions.resolve();
            },
            onApprove: async (data, actions) => {
                const orderId = data.orderID;
                const subscriptionId = data?.subscriptionID;

                const submitButtonDefaultText = submitButton.textContent;
                submitButton.textContent = __('Waiting for PayPal...', 'give');
                submitButton.disabled = true;

                if (subscriptionId && donationType === 'subscription') {
                    payPalSubscriptionId = subscriptionId;

                    submitButton.disabled = false;
                    submitButton.textContent = submitButtonDefaultText;
                    submitButton.click();
                    return;
                }

                if (orderId) {
                    payPalOrderId = orderId;
                }

                submitButton.disabled = false;
                submitButton.textContent = submitButtonDefaultText;
                submitButton.click();
                return;
            },
        };

        return donationType === 'subscription' ? (
            <PayPalButtons {...props} createSubscription={createSubscriptionHandler} />
        ) : (
            <PayPalButtons {...props} createOrder={createOrderHandler} />
        );
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
        const donationType = useWatch({name: 'donationType'});
        const [{options}, dispatch] = usePayPalScriptReducer();

        useEffect(() => {
            const isSubscription = donationType === 'subscription';

            const options = getPayPalScriptOptions({isSubscription});

            dispatch({
                type: 'resetOptions',
                value: {
                    ...options,
                    currency: currency,
                    vault: donationType === 'subscription',
                    intent: donationType === 'subscription' ? 'subscription' : options.intent,
                },
            });
        }, [currency, donationType]);

        return (
            <>
                <SmartButtonsContainer />
                {-1 !== options.components.indexOf('hosted-fields') && <HostedFieldsContainer />}
            </>
        );
    }

    const payPalCommerceGateway: PayPalCommerceGateway = {
        id: 'paypal-commerce',
        initialize() {
            payPalDonationsSettings = this.settings;
        },
        /**
         * Before create payment.
         * @since 3.2.0 Handle error response in approveOrderCallback.
         * @param {Object} values
         */
        beforeCreatePayment: async function (values): Promise<object> {
             if (payPalSubscriptionId) {
                return {
                    payPalSubscriptionId: payPalSubscriptionId,
                };
            }

            if (payPalOrderId) {
                // If order ID already set by payment buttons then return early.
                return {
                    payPalOrderId: payPalOrderId
                };
            }

            if (!validateHostedFields()) {
                throw new Error('Invalid PayPal card fields');
            }

            try {
                const result = await hostedField.cardFields.submit({
                    // Trigger 3D Secure authentication
                    contingencies: ['SCA_WHEN_REQUIRED'],
                    cardholderName: cardholderName,
                });

                if (
                    !result || // Check whether get result from paypal gateway server.
                    (['NO', 'POSSIBLE'].includes(result.liabilityShift) && // Check whether card required 3D secure validation.
                        !(result.liabilityShifted && 'POSSIBLE' === result.liabilityShift)) // Check whether card passed 3D secure validation.
                ) {
                    throw new Error(
                        __(
                            'There was a problem authenticating your payment method. Please try again. If the problem persists, please try another payment method.',
                            'give'
                        )
                    );
                }

                return {
                    ...result,
                    payPalOrderId: result.orderId
                }
            } catch (err) {
                console.log('paypal donations error', err);

                // Handle PayPal error.
                const isPayPalDonationError = err.hasOwnProperty('details');
                if (isPayPalDonationError) {
                    throw new Error(err.details[0].description);
                }

                throw new Error(sprintf(__('Paypal Donations Error: %s', 'give'), err.message));
            }
        },

        /**
         * @since 3.17.1 Hide submit button when PayPal Commerce is selected.
         */
        Fields() {
            const {useWatch} = window.givewp.form.hooks;
            const donationType = useWatch({name: 'donationType'});
            const isSubscription = donationType === 'subscription';
            submitButton = window.givewp.form.hooks.useFormSubmitButton();

            useEffect(() => {
                if (submitButton && !hostedField) {
                    submitButton.style.display = 'none';
                }

                return () => {
                    if (submitButton) {
                        submitButton.style.display = '';
                    }
                };
            }, []);
            return (
                <FormFieldsProvider>
                    <PayPalScriptProvider deferLoading={true} options={getPayPalScriptOptions({isSubscription})}>
                        <PaymentMethodsWrapper />
                    </PayPalScriptProvider>
                </FormFieldsProvider>
            );
        },
    };

    window.givewp.gateways.register(payPalCommerceGateway);
})();
