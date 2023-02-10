import {__} from '@wordpress/i18n';
import useCurrencyFormatter from "@givewp/forms/app/hooks/useCurrencyFormatter";

/**
 * @since 0.1.0
 */
export default function DonationSummary() {
    const {useWatch} = window.givewp.form.hooks;
    const currency = useWatch({name: 'currency'});
    const amount = useWatch({name: 'amount'});
    const donationType = useWatch({name: 'donationType'});
    const period = useWatch({name: 'period'});
    const givingFrequency = donationType !== 'subscription' ? __('One time', 'give') : `${period}ly`;
    const formatter = useCurrencyFormatter(currency, {});

    return (
        <ul className="givewp-elements-donationSummary__list">
            <LineItem label={__('Payment Amount', 'give')} value={formatter.format(Number(amount))}/>
            <LineItem label={__('Giving Frequency', 'give')} value={givingFrequency}/>
            <LineItem label={__('Donation Total', 'give')} value={formatter.format(Number(amount))}/>
        </ul>
    );
}

/**
 * @since 0.1.0
 */
const LineItem = ({label, value}: { label: string; value: string }) => {
    return (
        <li className="givewp-elements-donationSummary__list-item">
            <div>{label}</div>
            <div>{value}</div>
        </li>
    );
};
