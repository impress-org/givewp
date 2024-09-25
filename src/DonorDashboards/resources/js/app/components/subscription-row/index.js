import {useState} from 'react';
import {Link} from 'react-router-dom';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';

import {useWindowSize} from '../../hooks';
import {cancelSubscriptionWithAPI} from '../subscription-cancel-modal/utils';

import SubscriptionCancel from '../subscription-cancel-modal';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import DashboardLoadingSpinner from '../dashboard-loading-spinner';

const SubscriptionRow = ({subscription}) => {
    const [cancelModalOpen, setCancelModalOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    const {width} = useWindowSize();
    const {id, payment, form, gateway} = subscription;

    const toggleModal = () => {
        setCancelModalOpen(!cancelModalOpen);
    };

    const handleCancel = async () => {
        setLoading(true);
        await cancelSubscriptionWithAPI(id);
        toggleModal();
        setLoading(false);
    };

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
                {gateway.can_update && (
                    <div className="give-donor-dashboard-table__donation-receipt">
                        <Link to={`/recurring-donations/manage/${id}`}>
                            {__('Manage Subscription', 'give')} <FontAwesomeIcon icon="arrow-right" />
                        </Link>
                    </div>
                )}
                {gateway.can_cancel && (
                    <>
                        <ModalDialog
                            wrapperClassName={'give-donor-dashboard-cancel-modal'}
                            title={__('Cancel Subscription', 'give')}
                            showHeader={true}
                            isOpen={cancelModalOpen}
                            handleClose={toggleModal}
                        >
                            <SubscriptionCancel
                                onRequestClose={toggleModal}
                                handleCancel={handleCancel}
                                cancelling={loading}
                            />
                        </ModalDialog>
                        {loading && <DashboardLoadingSpinner />}
                        <div className="give-donor-dashboard-table__donation-receipt">
                            <a onClick={toggleModal}>{__('Cancel Subscription', 'give')}</a>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default SubscriptionRow;
