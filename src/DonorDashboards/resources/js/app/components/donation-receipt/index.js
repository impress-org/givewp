import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {Fragment} from 'react';

import './style.scss';

const DonationReceipt = ({donation}) => {
    if (donation === undefined) {
        return null;
    }

    const {receipt} = donation;

    return receipt.map((section, sectionIndex) => {
        const lineItems = section.lineItems.map((item, itemIndex) => {
            const value =
                typeof item.value === 'object' && item.value.color ? (
                    <Fragment>
                        <div
                            className="give-donor-dashboard-donation-receipt__status-indicator"
                            style={{background: item.value.color}}
                        />
                        {item.value.label}
                    </Fragment>
                ) : (
                    item.value
                );

            return (
                <div
                    className={`give-donor-dashboard-donation-receipt__row${
                        item.class.includes('total') ? ' give-donor-dashboard-donation-receipt__row--footer' : ''
                    }`}
                    key={itemIndex}
                >
                    <div className="give-donor-dashboard-donation-receipt__detail">
                        {item.icon && <FontAwesomeIcon icon={item.icon} fixedWidth={true} />} {item.label}
                    </div>
                    <div className="give-donor-dashboard-donation-receipt__value">{value}</div>
                </div>
            );
        });
        return (
            <div className="give-donor-dashboard-donation-receipt__table" key={sectionIndex}>
                {lineItems}
            </div>
        );
    });
};
export default DonationReceipt;
