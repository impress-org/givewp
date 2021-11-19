import {useLocation, Link} from 'react-router-dom';
import {Fragment} from 'react';

import {__} from '@wordpress/i18n';

import Heading from '../../components/heading';
import Divider from '../../components/divider';
import SubscriptionReceipt from '../../components/subscription-receipt';
import SubscriptionManager from '../../components/subscription-manager';
import SubscriptionTable from '../../components/subscription-table';

import {useSelector} from './hooks';

import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';

import './style.scss';

const Content = () => {
    const subscriptions = useSelector((state) => state.subscriptions);
    const querying = useSelector((state) => state.querying);
    const error = useSelector((state) => state.error);

    const location = useLocation();
    const route = location ? location.pathname.split('/')[2] : null;
    const id = location ? location.pathname.split('/')[3] : null;

    const getSubscriptionById = (subscriptionId) => {
        const filter = subscriptions.filter((subscription) =>
            parseInt(subscription.id) === parseInt(subscriptionId) ? true : false
        );
        if (filter.length) {
            return filter[0];
        }
        return null;
    };

    if (error) {
        return (
            <Fragment>
                <Heading icon="exclamation-triangle">{__('Error', 'give')}</Heading>
                <p style={{color: '#6b6b6b'}}>{error}</p>
            </Fragment>
        );
    }

    if (id) {
        switch (route) {
            case 'receipt': {
                return querying ? (
                    <Fragment>
                        <Heading>{__('Loading...', 'give')}</Heading>
                        <div className="give-donor-dashboard__recurring-donations-link">
                            <Link to="/recurring-donations">
                                <FontAwesomeIcon icon="arrow-left" /> {__('Back to Recurring Donations', 'give')}
                            </Link>
                        </div>
                    </Fragment>
                ) : (
                    <Fragment>
                        <Heading>
                            {__('Subscription', 'give')} #{getSubscriptionById(id).payment.serialCode}
                        </Heading>
                        <SubscriptionReceipt subscription={getSubscriptionById(id)} />
                        <div className="give-donor-dashboard__recurring-donations-link">
                            <Link to="/recurring-donations">
                                <FontAwesomeIcon icon="arrow-left" /> {__('Back to Recurring Donations', 'give')}
                            </Link>
                        </div>
                    </Fragment>
                );
            }
            case 'manage': {
                return querying && subscriptions === null ? (
                    <Fragment>
                        <Heading>{__('Loading...', 'give')}</Heading>
                        <div className="give-donor-dashboard__recurring-donations-link">
                            <Link to="/recurring-donations">
                                <FontAwesomeIcon icon="arrow-left" /> {__('Back to Recurring Donations', 'give')}
                            </Link>
                        </div>
                    </Fragment>
                ) : (
                    <Fragment>
                        <Heading>{__('Manage Subscription', 'give')}</Heading>
                        <Divider />
                        <SubscriptionManager id={id} subscription={getSubscriptionById(id)} />
                        <Divider />
                        <div className="give-donor-dashboard__recurring-donations-link" style={{marginTop: '18px'}}>
                            <Link to="/recurring-donations">
                                <FontAwesomeIcon icon="arrow-left" /> {__('Back to Recurring Donations', 'give')}
                            </Link>
                        </div>
                    </Fragment>
                );
            }
        }
    }

    if (querying && !subscriptions) {
        return (
            <Fragment>
                <Heading>{__('Loading...', 'give')}</Heading>
                <SubscriptionTable />
            </Fragment>
        );
    } else if (subscriptions) {
        return (
            <Fragment>
                <Heading>{`${Object.entries(subscriptions).length} ${__('Total Subscriptions', 'give')}`}</Heading>
                <SubscriptionTable subscriptions={subscriptions} perPage={5} />
            </Fragment>
        );
    } else {
        return (
            <Fragment>
                <Heading icon="exclamation-triangle">{__('No Subscriptions', 'give')}</Heading>
            </Fragment>
        );
    }
};
export default Content;
