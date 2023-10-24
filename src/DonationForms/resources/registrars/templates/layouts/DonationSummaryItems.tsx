import {__} from '@wordpress/i18n';
import type {ReactElement} from 'react';
import cx from 'classnames';

/**
 * @since 3.0.0
 */
export type LineItem = {
    id: string;
    label: string;
    value: string | ReactElement;
    description?: string | ReactElement;
    className?: string;
};

/**
 * @since 3.0.0
 */
const LineItem = ({id, label, value, description, className}: LineItem) => {
    const itemClasses = cx('givewp-elements-donationSummary__list__item', className);

    return (
        <li id={id} className={itemClasses}>
            <div className="givewp-elements-donationSummary__list__item__label-container">
                <div className="givewp-elements-donationSummary__list__item__label">{label}</div>
                {description && (
                    <div className="givewp-elements-donationSummary__list__item__description">{description}</div>
                )}
            </div>
            <div className="givewp-elements-donationSummary__list__item__value">{value}</div>
        </li>
    );
};

export default function DonationSummaryItems({items, total}) {
    return (
        <ul className="givewp-elements-donationSummary__list">
            {items.map(({id, label, value, description}, index) => {
                return <LineItem id={id} label={label} value={value} description={description} key={index} />;
            })}

            <LineItem
                id={'total'}
                label={__('Donation Total', 'give')}
                value={total}
                className="givewp-elements-donationSummary__list__item--total"
            />
        </ul>
    );
}
