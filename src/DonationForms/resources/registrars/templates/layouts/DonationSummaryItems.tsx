import {__} from '@wordpress/i18n';
import {ReactElement} from 'react';

/**
 * @since 0.4.0
 */
export type LineItem = {
    id: string;
    label: string;
    value: string | ReactElement;
    description?: string | ReactElement;
};

/**
 * @since 0.1.0
 */
const LineItem = ({id, label, value, description}: LineItem) => {
    return (
        <li id={id} className="givewp-elements-donationSummary__list-item">
            <div className="givewp-elements-donationSummary__list-item-label">{label}</div>
            <div className="givewp-elements-donationSummary__list-item-value">{value}</div>
            <div className="givewp-elements-donationSummary__list-item-description">{description}</div>
        </li>
    );
};

export default function DonationSummaryItems({items, total}) {
    return (
        <ul className="givewp-elements-donationSummary__list">
            {items.map(({id, label, value, description}, index) => {
                return <LineItem id={id} label={label} value={value} description={description} key={index} />;
            })}

            <LineItem id={'total'} label={__('Donation Total', 'give')} value={total} />
        </ul>
    );
}
