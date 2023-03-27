import {DonationMethodProps} from './types';
import {paymentMethodList} from './paymentMethodList';

/**
 *
 * @unreleased
 */

function GatewayLogo({gatewayId}: {gatewayId: string}) {
    for (const prop in paymentMethodList) {
        if (prop === gatewayId) {
            return paymentMethodList[prop];
        }
    }
    return null; // Return null if the gatewayId is not found in the paymentMethodList
}
/**
 *
 * @unreleased
 */
export default function DonationMethod({gatewayLabel, gatewayId}: DonationMethodProps) {
    return (
        <div>
            <GatewayLogo gatewayId={gatewayId} />
            <span>{gatewayLabel}</span>
        </div>
    );
}
