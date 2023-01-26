import PropTypes from 'prop-types';
import {useImperativeHandle, useState} from 'react';
import {PaymentInputsWrapper, usePaymentInputs} from 'react-payment-inputs';
import images from 'react-payment-inputs/images';
import {useAccentColor} from '../../../../hooks';
import {useAcceptJs} from 'react-acceptjs';
import {CreditCard, PaymentForm} from 'react-square-web-payments-sdk';

import './style.scss';

const cardTokenizeResponse = {};

const SquareControl = ({label, value, forwardedRef, gateway}) => {
    console.log('gateway: ', gateway);
    const [cardNumber, setCardNumber] = useState(value ? value.card_number : '');
    const [cardExpiryDate, setCardExpiryDate] = useState(
        value ? `${value.card_exp_month} \ ${value.card_exp_year}` : '',
    );
    const [cardCVC, setCardCVC] = useState(value ? value.card_cvc : '');
    const [cardZIP, setCardZIP] = useState(value ? value.card_zip : '');
    const accentColor = useAccentColor();
    const cardFullExpiryYear = cardExpiryDate.substr(5)
        ? new Date().getFullYear().toString().substr(0, 2) + cardExpiryDate.substr(5)
        : '';

    const environment = gateway.environment;
    const authData = {
        apiLoginID: gateway.apiLoginID,
        clientKey: gateway.clientKey,
    };

    const {dispatchData, loading, error} = useAcceptJs({environment, authData});

    const {
        wrapperProps,
        getCardImageProps,
        getCardNumberProps,
        getExpiryDateProps,
        getCVCProps,
        getZIPProps,
        meta: cardInputMeta,
    } = usePaymentInputs();


    useImperativeHandle(
        forwardedRef,
        () => ({
            async getPaymentMethod() {

                const squarePayButton = document.querySelector('#rswp-card-button');
                console.log('squarePayButton: ', squarePayButton);
                squarePayButton.click();

                while (!cardTokenizeResponse.hasOwnProperty("token")) { // define the condition as you like

                    let tokenError = document.querySelector('.sq-card-message-error');
                    console.log('tokenError: ', tokenError);
                    if (tokenError) {
                        return {
                            error: true,
                        };
                    }
                    console.log("waiting for token... ", cardTokenizeResponse);
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
                //console.log("token is defined");
                console.log('cardTokenizeResponse:', cardTokenizeResponse);

                console.log('TOKEN:', cardTokenizeResponse.token.token);
                console.log('verifiedBuyer:', cardTokenizeResponse.verifiedBuyer);

                return {
                    'square-card-nonce': (cardTokenizeResponse.token.token) ? cardTokenizeResponse.token.token : '',
                    'square-verified-buyer': (cardTokenizeResponse.verifiedBuyer) ? response.opaqueData.verifiedBuyer : '',
                };


                const {error} = cardInputMeta;

                if (error) {
                    return {
                        error: true,
                    };
                }

                const cardData = {};
                cardData.cardNumber = cardNumber.replace(/\s+/g, '');
                cardData.month = cardExpiryDate.substr(0, 2);
                cardData.year = cardFullExpiryYear.slice(-2);
                cardData.cardCode = cardCVC;

                const isCardDataFilled = Object.values(cardData).every(x => x !== null && x !== '');

                if (!isCardDataFilled) {
                    return {};
                }

                // Dispatch CC data to Authorize.net and receive payment nonce for use on your server
                const response = await dispatchData({cardData});

                if (response.messages.resultCode === "Error") {
                    var i = 0;
                    while (i < response.messages.message.length) {
                        console.log(
                            response.messages.message[i].code + ": " +
                            response.messages.message[i].text,
                        );
                        i = i + 1;
                    }
                }

                return {
                    give_authorize_data_descriptor: (response.opaqueData.dataDescriptor) ? response.opaqueData.dataDescriptor : '',
                    give_authorize_data_value: (response.opaqueData.dataValue) ? response.opaqueData.dataValue : '',
                };
            },
        }),
        [cardNumber, cardExpiryDate, cardCVC, cardZIP],
    );

    const applicationID = gateway.applicationID;
    const locationID = gateway.locationID;

    const inputProps = {
        ...wrapperProps,
        styles: {
            fieldWrapper: {
                base: {
                    width: '100%',
                },
            },
            inputWrapper: {
                base: {
                    display: 'flex',
                    alignItems: 'center',
                    marginTop: '8px',
                    border: '1px solid #b8b8b8',
                    overflow: 'hidden',
                    padding: '4px 14px',
                    boxShadow: `0 0 0 0 ${accentColor}`,
                    transition: 'box-shadow 0.1s ease',
                    borderRadius: '4px',
                },
                errored: {
                    border: '1px solid #b8b8b8',
                    boxShadow: '0 0 0 1px #c9444d',
                },
                focused: {
                    border: '1px solid #b8b8b8',
                    boxShadow: `0 0 0 1px ${accentColor}`,
                },
            },
            input: {
                base: {
                    fontSize: '14px',
                    fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
                    fontWeight: '500',
                    color: '#828382',
                    lineHeight: '1.2',
                },
                //   errored: css | Object,
                cardNumber: {
                    flex: '1',
                },
                //   expiryDate: css | Object,
                //   cvc: css | Object
            },
            // errorText: {
            //   base: css | Object
            // }
        },
    };

    return (
        <div className="give-donor-dashboard-card-control">
            <PaymentForm
                applicationId={applicationID}
                cardTokenizeResponseReceived={(token, verifiedBuyer) => {
                    console.log('E Aí?');
                    cardTokenizeResponse.token = token;
                    cardTokenizeResponse.verifiedBuyer = verifiedBuyer;
                    //console.log("cardTokenizeResponse: ", cardTokenizeResponse);
                }}
                locationId={locationID}
            >
                <CreditCard
                    buttonProps={{
                        isLoading: false,
                    }}
                />
            </PaymentForm>
            <label className="give-donor-dashboard-card-control__label">{label}</label>
            <PaymentInputsWrapper {...inputProps}>
                <svg {...getCardImageProps({images})} />
                <input {...getCardNumberProps({onChange: (e) => setCardNumber(e.target.value), value: cardNumber})} />
                <input
                    {...getExpiryDateProps({onChange: (e) => setCardExpiryDate(e.target.value), value: cardExpiryDate})}
                />
                <input {...getCVCProps({onChange: (e) => setCardCVC(e.target.value), value: cardCVC})} />
                <input {...getZIPProps({onChange: (e) => setCardZIP(e.target.value), value: cardZIP})} />
            </PaymentInputsWrapper>
        </div>
    );
};

SquareControl.propTypes = {
    label: PropTypes.string,
    value: PropTypes.object,
    onChange: PropTypes.func,
};

SquareControl.defaultProps = {
    label: null,
    value: null,
    onChange: null,
};

export default SquareControl;
