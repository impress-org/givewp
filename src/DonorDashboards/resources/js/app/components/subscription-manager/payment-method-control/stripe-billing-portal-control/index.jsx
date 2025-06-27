export default function StripeBillingPortalControl({gateway, currency, forwardedRef}) {
    return (
        <div>
            <p>
                You can update your payment method by clicking the button below to open the billing portal.
            </p>
            <a
                href={gateway.billingPortalUrl}
                target="_parent"
            >
                Open Billing Portal
            </a>
        </div>
    );
}
