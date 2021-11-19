import PropTypes from 'prop-types';
import {useState, useImperativeHandle} from 'react';
import {PaymentInputsWrapper, usePaymentInputs} from 'react-payment-inputs';
import images from 'react-payment-inputs/images';
import {useAccentColor} from '../../../../hooks';

import './style.scss';

const CardControl = ({label, value, forwardedRef}) => {
    const [cardNumber, setCardNumber] = useState(value ? value.card_number : '');
    const [cardExpiryDate, setCardExpiryDate] = useState(
        value ? `${value.card_exp_month} \ ${value.card_exp_year}` : ''
    );
    const [cardCVC, setCardCVC] = useState(value ? value.card_cvc : '');
    const [cardZIP, setCardZIP] = useState(value ? value.card_zip : '');
    const accentColor = useAccentColor();
    const cardFullExpiryYear = cardExpiryDate.substr(5)
        ? new Date().getFullYear().toString().substr(0, 2) + cardExpiryDate.substr(5)
        : '';

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
                const {error} = cardInputMeta;

                if (error) {
                    return {
                        error: true,
                    };
                }

                return {
                    card_number: cardNumber.replace(/\s+/g, ''),
                    card_exp_month: cardExpiryDate.substr(0, 2),
                    card_exp_year: cardFullExpiryYear,
                    card_cvc: cardCVC,
                    card_zip: cardZIP,
                };
            },
        }),
        [cardNumber, cardExpiryDate, cardCVC, cardZIP]
    );

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

CardControl.propTypes = {
    label: PropTypes.string,
    value: PropTypes.object,
    onChange: PropTypes.func,
};

CardControl.defaultProps = {
    label: null,
    value: null,
    onChange: null,
};

export default CardControl;
