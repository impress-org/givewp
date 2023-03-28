import {DonationMethodProps} from './types';

/**
 *
 * @unreleased
 */
export default function DonationMethod({gatewayLabel}: DonationMethodProps) {
    return <span>{gatewayLabel}</span>;
}
