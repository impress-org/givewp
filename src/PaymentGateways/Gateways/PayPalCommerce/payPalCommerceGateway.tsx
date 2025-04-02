import {
    DISPATCH_ACTION,
    PayPalButtons,
    PayPalButtonsComponentProps,
    PayPalCardFieldsForm,
    PayPalCardFieldsProvider,
    PayPalScriptProvider,
    usePayPalCardFields,
    usePayPalScriptReducer,
} from '@paypal/react-paypal-js';
import {__} from '@wordpress/i18n';
import {useEffect} from 'react';
import {PayPalCommerceGateway, PayPalSubscriber} from './types';
import handleValidationRequest from '@givewp/forms/app/utilities/handleValidationRequest';
import createOrder from './resources/js/createOrder';
import type {
    CreateSubscriptionRequestBody,
    PayPalButtonsComponentOptions,
    PayPalCardFieldsComponent,
    PayPalCardFieldsComponentBasics,
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

    let payPalCardFieldsForm: PayPalCardFieldsComponent = null;

    const showOrHideDonateButton = (showOrHide: 'show' | 'hide') => {
        if (submitButton) {
            submitButton.style.display = showOrHide === 'hide' ? 'none' : '';
        }
    };

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
        if (isSubscription && -1 !== paypalScriptOptions.components.indexOf('card-fields')) {
            paypalScriptOptions.components = paypalScriptOptions.components
                .split(',')
                .filter((component) => component !== 'card-fields')
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

        //const {isRecurring} = window.givewp.form.hooks.useFormData();

        //console.log('isRecurring: ', isRecurring);

        formData.append('give-form-id', payPalDonationsSettings.donationFormId);
        formData.append('give-form-hash', payPalDonationsSettings.donationFormNonce);

        formData.append('give_payment_mode', 'paypal-commerce');

        /*const eventTicketsTotalAmount = eventTickets ? getEventTicketsTotalAmount(JSON.parse(eventTickets)) : 0;
        const isSubscription = subscriptionPeriod ? subscriptionPeriod !== 'one-time' : false;
        if (!isSubscription) {
            formData.append('give-amount', getAmount() + eventTicketsTotalAmount);
        } else {
            formData.append('give-amount', getAmount()); // We don't want to charge the event tickets for each subscription renewal
        }

        formData.append('give-event-tickets-total-amount', String(eventTicketsTotalAmount));*/

        formData.append('give-amount', amount.toString());

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

    const smartButtonsCreateOrderHandler: PayPalButtonsComponentOptions['createOrder'] = async (): Promise<string> => {
        return await createOrder(
            `${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_create_order`,
            payPalCommerceGateway,
            getFormData()
        );
    };

    /**
     * @unreleased
     */
    const cardFieldsOnErrorHandler: PayPalCardFieldsComponentBasics['onError'] = (error) => {
        console.error('PayPal Card Fields Error:', error);

        throw new Error(__('PayPal Card Fields Error:', 'give') + error.message);
    };

    /**
     * @unreleased
     */
    const cardFieldsCreateOrderHandler = async () => {
        return await createOrder(
            `${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_create_order`,
            payPalCommerceGateway,
            getFormData()
        );
    };

    /**
     * @unreleased
     */
    const cardFieldsOnApproveHandler: PayPalCardFieldsComponentBasics['onApprove'] = async (data) => {
        payPalOrderId = data.orderID;
        return;
    };

    const smartButtonsCreateSubscriptionHandler: PayPalButtonsComponentOptions['createSubscription'] = async (
        data,
        actions
    ) => {
        const {planId, userAction} = await createSubscriptionPlan(
            `${payPalDonationsSettings.ajaxUrl}?action=give_paypal_commerce_create_plan_id`,
            payPalCommerceGateway,
            getFormData()
        );

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
                ...createSubscriptionPayload.application_context,
                user_action: userAction as 'CONTINUE' | 'SUBSCRIBE_NOW',
            };
        }

        return actions.subscription.create(createSubscriptionPayload).then((orderId) => {
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

    const FormFieldsProvider = ({children}) => {
        const {useWatch} = window.givewp.form.hooks;

        const formData = window.givewp.form.hooks.useFormData();

        amount = formData.amount;
        console.log('amount: ', amount);
        feeRecovery = formData.feeRecovery;
        firstName = formData.firstName;
        lastName = formData.lastName;
        email = formData.email;

        subscriptionFrequency = formData.subscriptionFrequency;
        subscriptionInstallments = formData.subscriptionInstallments;
        subscriptionPeriod = formData.subscriptionPeriod;

        addressLine1 = formData.billingAddress.addressLine1;
        addressLine2 = formData.billingAddress.addressLine2;
        city = formData.billingAddress.city;
        state = formData.billingAddress.state;
        postalCode = formData.billingAddress.postalCode;
        country = formData.billingAddress.country;

        currency = formData.currency;

        eventTickets = useWatch({name: 'event-tickets'});

        /*amount = useWatch({name: 'amount'});
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

        eventTickets = useWatch({name: 'event-tickets'});*/

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
            <PayPalButtons {...props} createSubscription={smartButtonsCreateSubscriptionHandler} />
        ) : (
            <PayPalButtons {...props} createOrder={smartButtonsCreateOrderHandler} />
        );
    };

    /**
     * @unreleased
     */
    const PayPalGatewayCardFieldsForm = () => {
        const {cardFieldsForm} = usePayPalCardFields();
        payPalCardFieldsForm = cardFieldsForm;
        payPalCommerceGateway.payPalCardFieldsForm = cardFieldsForm;

        return <PayPalCardFieldsForm />;
    };

    const CardFieldsContainer = () => {
        showOrHideDonateButton('show');

        return (
            <PayPalCardFieldsProvider
                createOrder={cardFieldsCreateOrderHandler}
                onApprove={cardFieldsOnApproveHandler}
                onError={cardFieldsOnErrorHandler}
            >
                <>
                    <Divider label={__('Or pay with card', 'give')} style={{padding: '30px 0'}} />
                    <PayPalGatewayCardFieldsForm />
                </>
            </PayPalCardFieldsProvider>
        );
    };

    function PaymentMethodsWrapper() {
        const {useWatch} = window.givewp.form.hooks;
        const donationType = useWatch({name: 'donationType'});
        const [{options}, dispatch] = usePayPalScriptReducer();
        const shouldShowCardFields = -1 !== options.components.indexOf('card-fields');

        useEffect(() => {
            const isSubscription = donationType === 'subscription';

            const options = getPayPalScriptOptions({isSubscription});

            dispatch({
                type: DISPATCH_ACTION.RESET_OPTIONS,
                value: {
                    ...options,
                    currency: currency,
                    vault: donationType === 'subscription',
                    intent: donationType === 'subscription' ? 'subscription' : options.intent,
                },
            });
        }, [currency, donationType]);

        useEffect(() => {
            // hide donate buttons if card fields are not expected to be shown
            if (!shouldShowCardFields) {
                showOrHideDonateButton('hide');
            }
        }, [shouldShowCardFields]);

        return (
            <>
                <SmartButtonsContainer />
                {shouldShowCardFields && <CardFieldsContainer />}
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
                    payPalOrderId: payPalOrderId,
                };
            }

            if (!payPalCardFieldsForm) {
                throw new Error(__('PayPal Card Fields are not available.', 'give'));
            }

            const cardFormState = await payPalCardFieldsForm.getState();

            if (!cardFormState.isFormValid) {
                throw new Error(__('PayPal Card Fields are invalid', 'give'));
            }

            const submitButtonDefaultText = submitButton.textContent;
            submitButton.textContent = __('Waiting for PayPal...', 'give');
            submitButton.disabled = true;

            await payPalCardFieldsForm.submit();

            submitButton.textContent = submitButtonDefaultText;

            if (!payPalOrderId) {
                submitButton.disabled = false;

                throw new Error(__('Missing PayPal Order ID.', 'give'));
            }

            return {
                payPalOrderId,
            };
        },

        /**
         * @since 3.17.1 Hide submit button when PayPal Commerce is selected.
         */
        Fields() {
            const {useWatch} = window.givewp.form.hooks;
            const donationType = useWatch({name: 'donationType'});
            const isSubscription = donationType === 'subscription';
            submitButton = window.givewp.form.hooks.useFormSubmitButton();

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
