import {Fragment, useMemo, useRef, useState} from 'react';
import FieldRow from '../field-row';
import Button from '../button';
import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';

import {__} from '@wordpress/i18n';

import AmountControl from './amount-control';
import PaymentMethodControl from './payment-method-control';

import {updateSubscriptionWithAPI} from './utils';

import './style.scss';

/**
 * Normalize an amount
 *
 * @param {string} float
 * @param {number} decimals
 * @return {string|NaN}
 */
const normalizeAmount = (float, decimals) => Number.parseFloat(float).toFixed(decimals);

// There is no error handling whatsoever, that will be necessary.
const SubscriptionManager = ({id, subscription}) => {
    const gatewayRef = useRef();

    const [amount, setAmount] = useState(() =>
        normalizeAmount(subscription.payment.amount.raw, subscription.payment.currency.numberDecimals)
    );
    const [isUpdating, setIsUpdating] = useState(false);
    const [updated, setUpdated] = useState(false);

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

        const paymentMethod = gatewayRef.current ?
            await gatewayRef.current.getPaymentMethod() :
            {};

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
            <FieldRow>
                <div>
                    <Button onClick={handleUpdate}>
                        {updated ? (
                            <Fragment>
                                {__('Updated', 'give')} <FontAwesomeIcon icon="check" fixedWidth/>
                            </Fragment>
                        ) : (
                            <Fragment>
                                {__('Update Subscription', 'give')}{' '}
                                <FontAwesomeIcon
                                    className={isUpdating ? 'give-donor-dashboard__subscription-manager-spinner' : ''}
                                    icon={isUpdating ? 'spinner' : 'save'}
                                    fixedWidth
                                />
                            </Fragment>
                        )}
                    </Button>
                </div>
            </FieldRow>
        </Fragment>
    );
};
export default SubscriptionManager;
