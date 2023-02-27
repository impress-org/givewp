import {__} from '@wordpress/i18n';
import {ReceiptDetail} from '@givewp/forms/types';
import {DonationReceiptProps} from '@givewp/forms/propTypes';

/**
 * @since 0.1.0
 */
const SecureBadge = () => {
    return (
        <aside className="givewp-form-secure-badge">
            <i className="fa-regular fa-circle-check givewp-form-secure-badge-icon"></i>
            {__('Success!', 'give')}
        </aside>
    );
};

/**
 *
 * @since 0.1.0
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
 * @since 0.1.0
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
