import PropTypes from 'prop-types';
import {useImperativeHandle} from 'react';
import {CreditCard, PaymentForm} from 'react-square-web-payments-sdk';

import './style.scss';

const cardTokenizeResponse = {};
let cardBrand = 'unknown';

const SquareControl = ({label, value, forwardedRef, gateway}) => {

    const {applicationID, locationID} = gateway;

    useImperativeHandle(
        forwardedRef,
        () => ({
            async getPaymentMethod() {

                if (cardBrand === 'unknown') {
                    return {};
                }

                const squarePayButton = document.querySelector('#rswp-card-button');
                squarePayButton.click();

                while (!cardTokenizeResponse.hasOwnProperty("token")) {
                    if (document.querySelector('.sq-card-message-error')) {
                        return {
                            error: true,
                        };
                    }
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }

                return {
                    'square-card-nonce': (cardTokenizeResponse.token.token) ? cardTokenizeResponse.token.token : '',
                    'square-verified-buyer': (cardTokenizeResponse.verifiedBuyer) ? response.opaqueData.verifiedBuyer : '',
                };
            },
        }),
        [],
    );

    return (
        <div className="give-donor-dashboard-square-card-control">
            <label className="give-donor-dashboard-card-control__label">{label}</label>
            <PaymentForm
                applicationId={applicationID}
                cardTokenizeResponseReceived={(token, verifiedBuyer) => {
                    cardTokenizeResponse.token = token;
                    cardTokenizeResponse.verifiedBuyer = verifiedBuyer;
                }}
                locationId={locationID}
            >
                <CreditCard
                    buttonProps={{
                        css: {
                            display: "none",
                        },
                    }}
                    callbacks={{
                        focusClassAdded(event) {
                            cardBrand = event.detail.cardBrand;
                        },
                        focusClassRemoved(event) {
                            cardBrand = event.detail.cardBrand;
                        },
                    }}
                />
            </PaymentForm>
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
