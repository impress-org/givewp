import {useState} from 'react';
import {Link} from 'react-router-dom';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';

import {useWindowSize} from '../../hooks';
import SubscriptionCancelModal from '../subscription-cancel-modal';

import './style.scss';

const SubscriptionRow = ({subscription}) => {
    const [isCancelModalOpen, setIsCancelModalOpen] = useState<boolean>(false);

    const {width} = useWindowSize();
    const {id, payment, form, gateway} = subscription;

    const gatewayCanUpdateSubscription = gateway.can_update || gateway.can_update_payment_method;

    return (
        <div className="give-donor-dashboard-table__row">
            <div className="give-donor-dashboard-table__column">
                {width < 920 && <div className="give-donor-dashboard-table__mobile-header">{__('Amount', 'give')}</div>}
                <div className="give-donor-dashboard-table__donation-amount">
                    {payment.amount.formatted} / {payment.frequency}
                </div>
                {form.title}
            </div>
            <div className="give-donor-dashboard-table__column">
                {width < 920 && <div className="give-donor-dashboard-table__mobile-header">{__('Status', 'give')}</div>}
                <div className="give-donor-dashboard-table__donation-status">
                    <div
                        className="give-donor-dashboard-table__donation-status-indicator"
                        style={{background: payment.status.color}}
                    />
                    <div className="give-donor-dashboard-table__donation-status-label">{payment.status.label}</div>
                </div>
            </div>
            <div className="give-donor-dashboard-table__column">
                {width < 920 && (
                    <div className="give-donor-dashboard-table__mobile-header">{__('Next Renewal', 'give')}</div>
                )}
                {payment.renewalDate}
            </div>
            <div className="give-donor-dashboard-table__column">
                {width < 920 && (
                    <div className="give-donor-dashboard-table__mobile-header">{__('Progress', 'give')}</div>
                )}
                {payment.progress}
            </div>
            <div className="give-donor-dashboard-table__pill">
                <div className="give-donor-dashboard-table__donation-id">ID: {payment.serialCode}</div>
                <div className="give-donor-dashboard-table__donation-receipt">
                    <Link to={`/recurring-donations/receipt/${id}`}>
                        {__('View Subscription', 'give')} <FontAwesomeIcon icon="arrow-right" />
                    </Link>
                </div>
                {gatewayCanUpdateSubscription && (
                    <div className="give-donor-dashboard-table__donation-receipt">
                        <Link to={`/recurring-donations/manage/${id}`}>
                            {__('Manage Subscription', 'give')} <FontAwesomeIcon icon="arrow-right" />
                        </Link>
                    </div>
                )}
                {gateway.can_cancel && !gatewayCanUpdateSubscription && (
                    <>
                        {isCancelModalOpen && (
                            <SubscriptionCancelModal
                                id={id}
                                isOpen={isCancelModalOpen}
                                toggleModal={() => setIsCancelModalOpen(!isCancelModalOpen)}
                            />
                        )}
                        <div className="give-donor-dashboard-table__donation-receipt">
                            <a
                                className={'give-donor-dashboard-table__donation-receipt__cancel'}
                                onClick={() => setIsCancelModalOpen(true)}
                            >
                                {__('Cancel Subscription', 'give')}
                            </a>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default SubscriptionRow;
