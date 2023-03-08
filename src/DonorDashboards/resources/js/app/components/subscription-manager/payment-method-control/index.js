import AuthorizeControl from './authorize-control';
import SquareControl from './square-control';
import StripeControl from './stripe-control';
import CardControl from './card-control';

import './style.scss';

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
        default: {
            return null;
        }
    }
};

export default PaymentMethodControl;
