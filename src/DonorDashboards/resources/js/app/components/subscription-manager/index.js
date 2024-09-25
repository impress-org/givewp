import {Fragment, useMemo, useRef, useState} from 'react';
import FieldRow from '../field-row';
import {FieldContent} from './field-content';
import Button from '../button';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {__} from '@wordpress/i18n';
import AmountControl from './amount-control';
import PaymentMethodControl from './payment-method-control';
import ModalDialog from '@givewp/components/AdminUI/ModalDialog';
import {managePausingSubscriptionWithAPI, updateSubscriptionWithAPI} from './utils';
import PauseDurationDropdown from './pause-duration-dropdown';
import DashboardLoadingSpinner from '../dashboard-loading-spinner';

import './style.scss';

/**
 * Normalize an amount
 *
 * @param {string} float
 * @param {number} decimals
 * @return {string|NaN}
 */
const normalizeAmount = (float, decimals) => Number.parseFloat(float).toFixed(decimals);

const SubscriptionManager = ({id, subscription}) => {
    const gatewayRef = useRef();
    const [isOpen, setIsOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    const [amount, setAmount] = useState(() =>
        normalizeAmount(subscription.payment.amount.raw, subscription.payment.currency.numberDecimals)
    );
    const [isUpdating, setIsUpdating] = useState(false);
    const [updated, setUpdated] = useState(false);

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

    const handlePause = async (pauseDuration) => {
        setLoading(true);
        await managePausingSubscriptionWithAPI({
            id,
            intervalInMonths: pauseDuration,
        });
        setLoading(false);
    };

    const handleResume = async () => {
        setLoading(true);
        await managePausingSubscriptionWithAPI({
            id,
            action: 'resume',
        });
        setLoading(false);
    };

    const toggleModal = () => {
        setIsOpen(!isOpen);
    };

    return (
        <Fragment>
            <AmountControl
                currency={subscription.payment.currency}
                options={options}
                max={max}
                min={min}
                value={amount}
                onChange={setAmount}
            />
            <PaymentMethodControl
                forwardedRef={gatewayRef}
                label={__('Payment Method', 'give')}
                gateway={subscription.gateway}
            />
            <FieldContent classNames={'give-donor-dashboard__subscription-manager'}>
                <FieldRow>
                    <div>
                        <Button onClick={handleUpdate}>
                            {updated ? (
                                <Fragment>
                                    {__('Updated', 'give')} <FontAwesomeIcon icon="check" fixedWidth />
                                </Fragment>
                            ) : (
                                <Fragment>
                                    {__('Update Subscription', 'give')}{' '}
                                    <FontAwesomeIcon
                                        className={
                                            isUpdating ? 'give-donor-dashboard__subscription-manager-spinner' : ''
                                        }
                                        icon={isUpdating ? 'spinner' : 'save'}
                                        fixedWidth
                                    />
                                </Fragment>
                            )}
                        </Button>
                    </div>
                </FieldRow>

                {loading && <DashboardLoadingSpinner />}

                {showPausingControls && (
                    <FieldRow>
                        <div className={'give-donor-dashboard__subscription-manager-pause-content'}>
                            <p className={'give-donor-dashboard__subscription-manager-resume-header'}>
                                {__('Subscription Renewal', 'give')}
                            </p>
                            <ModalDialog
                                wrapperClassName={'give-donor-dashboard__subscription-manager-modal'}
                                title={__('Pause Subscription', 'give')}
                                showHeader={true}
                                isOpen={isOpen}
                                handleClose={toggleModal}
                            >
                                <PauseDurationDropdown handlePause={handlePause} closeModal={toggleModal} />
                            </ModalDialog>
                            {subscription.payment.status.id === 'active' ? (
                                <div className={'give-donor-dashboard__subscription-manager-pause-container'}>
                                    <Button variant onClick={toggleModal}>
                                        {__('Pause', 'give')}
                                    </Button>
                                </div>
                            ) : (
                                <>
                                    <Button variant onClick={handleResume}>
                                        {__('Resume', 'give')}{' '}
                                    </Button>
                                    <span className={'give-donor-dashboard__subscription-manager-resume-description'}>
                                        {__(
                                            'When you resume, your donations will resume on the next scheduled renewal date.',
                                            'give'
                                        )}
                                    </span>
                                </>
                            )}
                        </div>
                    </FieldRow>
                )}
            </FieldContent>
        </Fragment>
    );
};
export default SubscriptionManager;
