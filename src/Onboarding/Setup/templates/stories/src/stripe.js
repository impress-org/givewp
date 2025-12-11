import { __, sprintf } from '@wordpress/i18n';
import stripe from '../../../../../../../../assets/dist/images/setup-page/stripe@2x.min.png';
import template from '../../row-item.html';

export default () => {
    return template
        .replace(/{{\s*class\s*}}/gi, 'stripe')
        .replace(/{{\s*icon\s*}}/gi, stripe)
        .replace(/{{\s*title\s*}}/gi, 'Connect to Stripe')
        .replace(
            /{{\s*description\s*}}/gi,
            sprintf(
                __('Stripe is one of the most popular payment gateways, and for good reason! Receive one-time and Recurring Donations (add-on) using many of the most popular payment methods. Additional fees may apply for free users. Read our <a href="%s" target="_blank" rel="noopener noreferrer">Stripe documentation</a> for more information.', 'give'), 
                'https://docs.givewp.com/stripe-fees' 
            )
        )
        .replace(/{{\s*action\s*}}/gi, '<button><i class="fab fa-stripe-s"></i>&nbsp;&nbsp;Connect to Stripe</button>');
};
