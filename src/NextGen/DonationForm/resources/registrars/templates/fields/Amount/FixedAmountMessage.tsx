import {__} from "@wordpress/i18n";
import {createInterpolateElement} from "@wordpress/element";

/**
 * @unreleased
 */
export default function FixedAmountMessage({amount}: { amount: string }) {
    return <div className="givewp-fields-amount__fixed-message">
        {createInterpolateElement(
            __('This donation is <amount/>', 'give'),
            {
                amount: <strong>{amount}</strong>
            }
        )}
    </div>
}