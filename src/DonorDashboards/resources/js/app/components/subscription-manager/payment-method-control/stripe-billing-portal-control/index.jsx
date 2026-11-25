import {__} from '@wordpress/i18n';

/**
 * @unreleased
 */
export default function StripeBillingPortalControl({gateway}) {
    const {billingPortalUrl} = gateway;

    if (!billingPortalUrl) {
        return null;
    }

    return (
        <div>
            <p>
                {__(
                    'You can update your payment method by clicking the link below to open the billing portal:',
                    'give'
                )}
            </p>

            <a href={billingPortalUrl} target="_parent">
                {__('Open Billing Portal', 'give')}
            </a>
        </div>
    );
}
