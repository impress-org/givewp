import {__} from '@wordpress/i18n';
import {ReceiptDetail} from '@givewp/forms/types';
import {DonationReceiptProps} from '@givewp/forms/propTypes';
import {Interweave} from 'interweave';

/**
 * @since 3.0.0
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
 * @since 3.4.0 updated to render value using Interweave
 * @since 3.0.0
 */
const Details = ({id, heading, details}: {id: string; heading: string; details: ReceiptDetail[]}) =>
    details?.length > 0 && (
        <div className={`details details-${id}`}>
            <h3 className="headline">{heading}</h3>
            <dl className="details-table">
                {details.map(({label, value}, index) => (
                    <div key={index} className={`details-row details-row--${label.toLowerCase().replace(' ', '-')}`}>
                        <dt className="detail">{label}</dt>
                        <Interweave className="value" tagName="dd" data-value={value} content={value} />
                    </div>
                ))}
            </dl>
        </div>
    );
/**
 * @since 3.0.0
 */
export default function DonationReceipt({
    heading,
    description,
    donorDashboardUrl,
    pdfReceiptLink,
    donorDetails,
    donationDetails,
    subscriptionDetails,
    eventTicketsDetails,
    additionalDetails,
}: DonationReceiptProps) {
    return (
        <article>
            <div className="receipt-header">
                <div className="receipt-header-top-wrap">
                    <SecureBadge />
                    <Interweave tagName="h1" className="receipt-header-heading" content={heading} />
                    <Interweave tagName="p" className="receipt-header-description" content={description} />
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
                    id="event-tickets-details"
                    heading={__('Event Tickets Details', 'give')}
                    details={eventTicketsDetails}
                />
                <Details
                    id="additional-details"
                    heading={__('Additional Details', 'give')}
                    details={additionalDetails}
                />
            </div>

            <div className="receipt-footer">
                {pdfReceiptLink && <Interweave content={pdfReceiptLink} />}

                <a className="donor-dashboard-link" href={donorDashboardUrl} target="_parent">
                    {__('Go to my Donor Dashboard', 'give')}
                </a>
            </div>
        </article>
    );
}
