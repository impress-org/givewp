/**
 *
 * @unreleased
 */

export type DonationMethodProps = {
    gatewayLabel: string;
};

export default function DonationMethod({gatewayLabel}: DonationMethodProps) {
    return <span>{gatewayLabel}</span>;
}
