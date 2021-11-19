import {Fragment, useState} from 'react';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';

import Table from '../table';
import SubscriptionRow from '../subscription-row';

import './style.scss';

const SubscriptionTable = ({subscriptions, perPage}) => {
    const [page, setPage] = useState(1);

    const getStartIndex = () => {
        return (page - 1) * perPage;
    };

    const getEndIndex = () => {
        return start + perPage <= subscriptionsArray.length ? start + perPage : subscriptionsArray.length;
    };

    const getSubscriptionRows = () => {
        return subscriptionsArray.reduce((rows, subscription, index) => {
            if (index >= start && index < end) {
                rows.push(<SubscriptionRow subscription={subscription} key={index} />);
            }
            return rows;
        }, []);
    };

    let subscriptionRows = [];
    const subscriptionsArray = [];
    let start = 0;
    let end = perPage;
    let lastPage = 1;

    if (subscriptions) {
        Object.entries(subscriptions).forEach((subscription) => {
            subscriptionsArray[subscription[0]] = subscription[1];
        });
        start = getStartIndex();
        end = getEndIndex();
        lastPage = Math.ceil(subscriptionsArray.length / perPage) - 1;
        subscriptionRows = getSubscriptionRows();
    }

    return (
        <Table
            header={
                <Fragment>
                    <div className="give-donor-dashboard-table__column">{__('Subscription', 'give')}</div>
                    <div className="give-donor-dashboard-table__column">{__('Status', 'give')}</div>
                    <div className="give-donor-dashboard-table__column">{__('Next Renewal', 'give')}</div>
                    <div className="give-donor-dashboard-table__column">{__('Progress', 'give')}</div>
                </Fragment>
            }
            rows={<Fragment>{subscriptionRows}</Fragment>}
            footer={
                <Fragment>
                    <div className="give-donor-dashboard-table__footer-text">
                        {subscriptions &&
                            `${__('Showing', 'give')} ${start + 1} - ${end} ${__('of', 'give')} ${
                                subscriptionsArray.length
                            } ${__('Subscriptions', 'give')}`}
                    </div>
                    <div className="give-donor-dashboard-table__footer-nav">
                        {page - 1 >= 1 && (
                            <a onClick={() => setPage(page - 1)}>
                                <FontAwesomeIcon icon="chevron-left" />
                            </a>
                        )}
                        {page <= lastPage && (
                            <a onClick={() => setPage(page + 1)}>
                                <FontAwesomeIcon icon="chevron-right" />
                            </a>
                        )}
                    </div>
                </Fragment>
            }
        />
    );
};

export default SubscriptionTable;
