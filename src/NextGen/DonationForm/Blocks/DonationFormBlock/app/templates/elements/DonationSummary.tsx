import {useMemo} from "react";
import {__} from "@wordpress/i18n";

export default function DonationSummary() {
    const {useWatch} = window.givewp.form;
    const currency = useWatch({name: 'currency'});
    const amount = useWatch({name: 'amount'});
    const formatter = useMemo(
        () =>
            new Intl.NumberFormat(navigator.language, {
                style: 'currency',
                currency: currency,
            }),
        [currency, navigator.language]
    )

    return (
        <ul className="givewp-elements-donationSummary__list">
            <LineItem label={__('Payment Amount', 'give')} value={formatter.format(Number(amount))}/>
            <LineItem label={__('Giving Frequency', 'give')} value={__('One time', 'give')}/>
            <LineItem label={__('Donation Total', 'give')} value={formatter.format(Number(amount))}/>
        </ul>
    );

}

const LineItem = ({label, value}: { label: string, value: string }) => {
    return (
        <li className="givewp-elements-donationSummary__list-item">
            <div>{label}</div>
            <div>{value}</div>
        </li>
    )
}
