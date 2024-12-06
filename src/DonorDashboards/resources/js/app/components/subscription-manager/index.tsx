import {Fragment, useMemo, useRef, useState} from 'react';
import FieldRow from '../field-row';
import Button from '../button';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';
import AmountControl from './amount-control';
import PaymentMethodControl from './payment-method-control';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {updateSubscriptionWithAPI} from './utils';
import PauseDurationDropdown from './pause-duration-dropdown';
import DashboardLoadingSpinner from '../dashboard-loading-spinner';
import usePauseSubscription from './hooks/pause-subscription';

import './style.scss';
import SubscriptionCancelModal from '../subscription-cancel-modal';

/**
 * Normalize an amount
 *
 * @param {string} float
 * @param {number} decimals
 * @return {string|NaN}
 */
const normalizeAmount = (float, decimals) => Number.parseFloat(float).toFixed(decimals);

/**
 * @since 3.19.0 Add support for hiding amount controls via filter
 */
const SubscriptionManager = ({id, subscription}) => {
    const gatewayRef = useRef();
    const [isPauseModalOpen, setIsPauseModalOpen] = useState(false);
    const [isCancelModalOpen, setIsCancelModalOpen] = useState(false);

    const [amount, setAmount] = useState(() =>
        normalizeAmount(subscription.payment.amount.raw, subscription.payment.currency.numberDecimals)
    );
    const [isUpdating, setIsUpdating] = useState(false);
    const [updated, setUpdated] = useState(false);
    const {handlePause, handleResume, loading} = usePauseSubscription(id);

    const subscriptionStatus = subscription.payment.status?.id || subscription.payment.status.label.toLowerCase();

    const showAmountControls = subscription.gateway.can_update;
    const showPaymentMethodControls = subscription.gateway.can_update_payment_method ?? showAmountControls;
    const showPausingControls =
        subscription.gateway.can_pause && !['Quarterly', 'Yearly'].includes(subscription.payment.frequency);

    // Prepare data for amount control
    const {max, min, options} = useMemo(() => {
        const {numberDecimals} = subscription.payment.currency;
        const {custom_amount} = subscription.form;

        const options = subscription.form.amounts.map((amount) => ({
            value: normalizeAmount(amount.raw, numberDecimals),
            label: amount.formatted,
        }));

        if (custom_amount) {
            options.push({
                value: 'custom_amount',
                label: __('Custom Amount', 'give'),
            });
        }

        return {
            max: normalizeAmount(custom_amount?.maximum, numberDecimals),
            min: normalizeAmount(custom_amount?.minimum, numberDecimals),
            options,
        };
    }, [subscription]);

    const handleUpdate = async () => {
        if (isUpdating) {
            return;
        }

        setIsUpdating(true);

        // @ts-ignore
        const paymentMethod = gatewayRef.current ? await gatewayRef.current.getPaymentMethod() : {};

        if ('error' in paymentMethod) {
            setIsUpdating(false);
            return;
        }

        await updateSubscriptionWithAPI({
            id,
            amount,
            paymentMethod,
        });

        setUpdated(true);
        setIsUpdating(false);
    };

    const toggleModal = () => {
        setIsPauseModalOpen(!isPauseModalOpen);
    };

    return (
        <div className={'give-donor-dashboard__subscription-manager'}>
            {showAmountControls && (
                <AmountControl
                    currency={subscription.payment.currency}
                    options={options}
                    max={max}
                    min={min}
                    value={amount}
                    onChange={setAmount}
                />
            )}
            {showPaymentMethodControls && (
                <PaymentMethodControl
                    forwardedRef={gatewayRef}
                    label={__('Payment Method', 'give')}
                    gateway={subscription.gateway}
                />
            )}

            {loading && <DashboardLoadingSpinner />}

            <FieldRow>
                {showPausingControls && (
                    <>
                        <ModalDialog
                            wrapperClassName={'give-donor-dashboard__subscription-manager-modal'}
                            title={__('Pause Subscription', 'give')}
                            showHeader={true}
                            isOpen={isPauseModalOpen}
                            handleClose={toggleModal}
                        >
                            <PauseDurationDropdown handlePause={handlePause} closeModal={toggleModal} />
                        </ModalDialog>
                        {subscriptionStatus === 'active' ? (
                            <Button variant onClick={toggleModal}>
                                {__('Pause Subscription', 'give')}
                            </Button>
                        ) : (
                            <Button variant onClick={handleResume}>
                                {__('Resume Subscription', 'give')}
                            </Button>
                        )}
                    </>
                )}

                <Button
                    disabled={subscriptionStatus !== 'active'}
                    classnames={subscriptionStatus !== 'active' && 'disabled'}
                    onClick={handleUpdate}
                >
                    {updated ? (
                        <Fragment>
                            {__('Updated', 'give')} <FontAwesomeIcon icon="check" fixedWidth />
                        </Fragment>
                    ) : (
                        <Fragment>
                            {__('Update Subscription', 'give')}
                            <FontAwesomeIcon
                                className={isUpdating ? 'give-donor-dashboard__subscription-manager-spinner' : ''}
                                icon={isUpdating ? 'spinner' : 'save'}
                                fixedWidth
                            />
                        </Fragment>
                    )}
                </Button>
            </FieldRow>
            {isCancelModalOpen && (
                <SubscriptionCancelModal
                    isOpen={isCancelModalOpen}
                    toggleModal={() => setIsCancelModalOpen(!isCancelModalOpen)}
                    id={id}
                />
            )}
            <button
                className={'give-donor-dashboard__subscription-manager__cancel'}
                onClick={() => setIsCancelModalOpen(true)}
            >
                {__('Cancel Subscription', 'give')}
            </button>
        </div>
    );
};
export default SubscriptionManager;
