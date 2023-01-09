import {__} from '@wordpress/i18n';

type Detail = {
    label: string;
    value: string;
};

type Props = {
    heading: string;
    description: string;
    donorDetails: Detail[];
    donationDetails: Detail[];
    additionalDetails: Detail[];
};

const Details = ({heading, details}: {heading: string; details: Detail[]}) => details.length > 0 && (
    <div>
        <h3>{heading}</h3>
        <dl>
            {details.map(({label, value}) => (
                <div>
                    <dt>{label}</dt>
                    <dd>{value}</dd>
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
    donorDetails,
    donationDetails,
    additionalDetails,
}: Props) {
    return (
        <article>
            <div>
                <h2>{heading}</h2>
                <p>{description}</p>
            </div>

            <Details heading={__('Donor Details', 'give')} details={donorDetails} />
            <Details heading={__('Donation Details', 'give')} details={donationDetails} />
            <Details heading={__('Additional Details', 'give')} details={additionalDetails} />
        </article>
    );
}
