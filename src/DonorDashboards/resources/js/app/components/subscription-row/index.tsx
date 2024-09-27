import {useState} from 'react';
import {Link} from 'react-router-dom';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';

import {useWindowSize} from '../../hooks';
import {cancelSubscriptionWithAPI} from '../subscription-cancel/utils';

import SubscriptionCancel from '../subscription-cancel';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import DashboardLoadingSpinner from '../dashboard-loading-spinner';
import PauseDurationDropdown from '../subscription-manager/pause-duration-dropdown';
import usePauseSubscription, {pauseDuration} from '../subscription-manager/hooks/pause-subscription';

const SubscriptionRow = ({subscription}) => {
    const [isCancelModalOpen, setIsCancelModalOpen] = useState<boolean>(false);
    const [isPauseResumeContent, setIsPauseResumeContent] = useState<boolean>(false);
    const {handlePause, loading, setLoading} = usePauseSubscription(subscription.id);

    const {width} = useWindowSize();
    const {id, payment, form, gateway} = subscription;

    const showPausingControls =
        subscription.payment.status.id === 'active' &&
        subscription.gateway.can_pause &&
        !['Quarterly', 'Yearly'].includes(subscription.payment.frequency);

    const handleCancel = async () => {
        setLoading(true);
        await cancelSubscriptionWithAPI(id);
        setIsCancelModalOpen(false);
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
                            title={
                                isPauseResumeContent && showPausingControls
                                    ? __('Pause Subscription', 'give')
                                    : __('Cancel Subscription', 'give')
                            }
                            showHeader={true}
                            isOpen={isCancelModalOpen}
                            handleClose={() => setIsCancelModalOpen(false)}
                        >
                            {isPauseResumeContent && showPausingControls ? (
                                <PauseDurationDropdown
                                    handlePause={handlePause}
                                    closeModal={() => setIsCancelModalOpen(false)}
                                />
                            ) : (
                                <SubscriptionCancel
                                    showPausingControls={showPausingControls}
                                    subscription={subscription}
                                    handlePauseRequest={() => setIsPauseResumeContent(true)}
                                    closeModal={() => setIsCancelModalOpen(false)}
                                    handleCancel={handleCancel}
                                    cancelling={loading}
                                />
                            )}
                        </ModalDialog>
                        {loading && <DashboardLoadingSpinner />}
                        <div className="give-donor-dashboard-table__donation-receipt">
                            <a onClick={() => setIsCancelModalOpen(true)}>{__('Cancel Subscription', 'give')}</a>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default SubscriptionRow;
