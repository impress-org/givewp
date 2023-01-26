import PropTypes from 'prop-types';
import {useImperativeHandle} from 'react';
import {CreditCard, PaymentForm} from 'react-square-web-payments-sdk';

import './style.scss';

const cardTokenizeResponse = {};

const SquareControl = ({label, value, forwardedRef, gateway}) => {
    console.log('gateway: ', gateway);

    const applicationID = gateway.applicationID;
    const locationID = gateway.locationID;

    useImperativeHandle(
        forwardedRef,
        () => ({
            async getPaymentMethod() {

                const squarePayButton = document.querySelector('#rswp-card-button');
                squarePayButton.click();

                while (!cardTokenizeResponse.hasOwnProperty("token")) {
                    if (document.querySelector('.sq-card-message-error')) {
                        return {
                            error: true,
                        };
                    }
                    console.log("waiting for token... ", cardTokenizeResponse);
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
                console.log('cardTokenizeResponse:', cardTokenizeResponse);

                return {
                    'square-card-nonce': (cardTokenizeResponse.token.token) ? cardTokenizeResponse.token.token : '',
                    'square-verified-buyer': (cardTokenizeResponse.verifiedBuyer) ? response.opaqueData.verifiedBuyer : '',
                };
            },
        }),
        [],
    );

    return (
        <div className="give-donor-dashboard-card-control">
            <label className="give-donor-dashboard-card-control__label">{label}</label>
            <PaymentForm
                applicationId={applicationID}
                cardTokenizeResponseReceived={(token, verifiedBuyer) => {
                    console.log('E Aí?');
                    cardTokenizeResponse.token = token;
                    cardTokenizeResponse.verifiedBuyer = verifiedBuyer;
                }}
                locationId={locationID}
            >
                <CreditCard
                    buttonProps={{
                        isLoading: false,
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
