import React from 'react';

import AuthorizeControl from './authorize-control';
import SquareControl from './square-control';
import StripeControl from './stripe-control';
import CardControl from './card-control';

import './style.scss';

/**
 * @since 3.19.0 Add controller for Blink payment method.
 */
const PaymentMethodControl = (props) => {
    switch (props.gateway.id) {
        case 'stripe':
        case 'stripe_apple_pay':
        case 'stripe_becs':
        case 'stripe_checkout':
        case 'stripe_google_pay': {
            return <StripeControl {...props} />;
        }
        case 'authorize': {
            return <AuthorizeControl {...props} />;
        }
        case 'square': {
            return <SquareControl {...props} />;
        }
        case 'paypalpro': {
            return <CardControl {...props} />;
        }
        case 'blink': {
            // Donor Dashboard currently loads its own version of React so we need to pass it to the component
            const Element = wp.hooks.applyFilters('give_donor_dashboard_blink_payment_method_control', null, props);
            return Element && <Element {...props} React={React} />;
        }
        default: {
            return null;
        }
    }
};

export default PaymentMethodControl;
