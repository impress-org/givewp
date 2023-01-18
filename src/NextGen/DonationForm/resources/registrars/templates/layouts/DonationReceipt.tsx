import {__} from '@wordpress/i18n';
import {ReceiptDetail} from '@givewp/forms/types';
import {DonationReceiptProps} from '@givewp/forms/propTypes';

/**
 * @unreleased
 */
const SecureBadge = () => {
    return (
        <aside className="givewp-form-secure-badge">
            <svg className="givewp-form-secure-icon" viewBox="0 0 20 20">
                <path
                    d="M20 10C20 15.5229 15.5229 20 10 20C4.47714 20 0 15.5229 0 10C0 4.47714 4.47714 0 10 0C15.5229 0 20 4.47714 20 10ZM8.84331 15.2949L16.2627 7.87556C16.5146 7.62363 16.5146 7.21512 16.2627 6.96319L15.3503 6.05081C15.0983 5.79883 14.6898 5.79883 14.4379 6.05081L8.3871 12.1015L5.56214 9.27657C5.3102 9.02464 4.90169 9.02464 4.64972 9.27657L3.73734 10.189C3.4854 10.4409 3.4854 10.8494 3.73734 11.1013L7.93089 15.2949C8.18286 15.5469 8.59133 15.5469 8.84331 15.2949Z"
                    fill="currentColor"
                />
            </svg>
            {__('Success!', 'give')}
        </aside>
    );
};

/**
 *
 * @unreleased
 */
const Details = ({id, heading, details}: { id: string; heading: string; details: ReceiptDetail[] }) =>
    details?.length > 0 && (
        <div className={`details details-${id}`}>
            <h3 className="headline">{heading}</h3>
            <dl className="details-table">
                {details.map(({label, value}, index) => (
                    <div key={index} className={`details-row details-row--${label.toLowerCase().replace(' ', '-')}`}>
                        <dt className="detail">{label}</dt>
                        <dd className="value" data-value={value}>
                            {value}
                        </dd>
                    </div>
                ))}
            </dl>
        </div>
    );
/**
 * @unreleased
 */
export default function DonationReceipt({
    heading,
    description,
    donorDashboardUrl,
    donorDetails,
    donationDetails,
    subscriptionDetails,
    additionalDetails,
}: DonationReceiptProps) {
    return (
        <article>
            <div className="receipt-header">
                <div className="receipt-header-top-wrap">
                    <SecureBadge />
                    <h1 className="receipt-header-heading">{heading}</h1>
                    <p className="receipt-header-description">{description}</p>
                </div>
            </div>

            <div className="receipt-body">
                <Details id="donor-details" heading={__('Donor Details', 'give')} details={donorDetails} />
                <Details id="donation-details" heading={__('Donation Details', 'give')} details={donationDetails} />
                <Details
                    id="subscription-details"
                    heading={__('Subscription Details', 'give')}
                    details={subscriptionDetails}
                />
                <Details
                    id="additional-details"
                    heading={__('Additional Details', 'give')}
                    details={additionalDetails}
                />
            </div>

            <div className="receipt-footer">
                <a className="donor-dashboard-link" href={donorDashboardUrl} target="_parent">
                    {__('Go to my Donor Dashboard', 'give')}
                    <i className="fas fa-long-arrow-alt-right"></i>
                </a>
            </div>
        </article>
    );
}
