import {__} from '@wordpress/i18n';

import LevelGrid from './level-grid';
import LevelButton from './level-buttons';
import Inspector from './inspector';
import periodLookup from './period-lookup';
import {CurrencyControl, formatCurrencyAmount} from '../../../common/currency';
import {BaseControl, RadioControl} from '@wordpress/components';
import {OneTimeAmountMessage, RecurringAmountMessage} from '@givewp/forms/shared/AmountMessages';
import Notice from './notice';

import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const Edit = ({attributes, setAttributes}) => {
    const {
        label = __('Donation Amount', 'give'),
        levels,
        priceOption,
        setPrice,
        customAmount,
        recurringEnabled,
        recurringDonationChoice,
        recurringBillingInterval,
        recurringBillingPeriod,
        recurringBillingPeriodOptions,
        recurringLengthOfTime,
        recurringOptInDefaultBillingPeriod,
    } = attributes;

    const {gateways} = getFormBuilderWindowData();

    const isRecurringSupported = gateways.some((gateway) => gateway.enabled && gateway.supportsSubscriptions);
    const isRecurring = isRecurringSupported && recurringEnabled;
    const isMultiLevel = priceOption === 'multi';
    const isFixedAmount = priceOption === 'set';
    const isRecurringAdmin = isRecurring && 'admin' === recurringDonationChoice;
    const isRecurringDonor = isRecurring && 'donor' === recurringDonationChoice;

    const amountFormatted = formatCurrencyAmount(setPrice.toString());

    const DonationLevels = () => (
        <LevelGrid>
            {levels.map((level: string, index: number) => {
                const levelAmount = formatCurrencyAmount(level);

                return <LevelButton key={index}>{levelAmount}</LevelButton>;
            })}
        </LevelGrid>
    );

    const CustomAmount = () => (
        <CurrencyControl value={setPrice} label={__('Custom amount', 'give')} hideLabelFromVision />
    );

    const FixedPriceMessage = () => (
        <Notice>
            <OneTimeAmountMessage amount={amountFormatted} />
        </Notice>
    );

    const FixedRecurringMessage = () => {
        const installments = parseInt(recurringLengthOfTime);
        const frequency = parseInt(recurringBillingInterval);

        return (
            <Notice>
                <RecurringAmountMessage
                    isFixedAmount={isFixedAmount}
                    fixedAmount={amountFormatted}
                    period={recurringBillingPeriod}
                    frequency={frequency}
                    installments={installments}
                />
            </Notice>
        );
    };

    const BillingPeriodControl = ({options}) => {
        return (
            <RadioControl
                className={'give-billing-period-control'}
                label={__('Billing Period', 'give')}
                hideLabelFromVision={true}
                selected={recurringOptInDefaultBillingPeriod ?? options[0]}
                options={['one-time'].concat(options).map((option) => {
                    return {
                        label: 'one-time' === option ? __('One Time', 'give') : periodLookup[option].adjective,
                        value: option,
                    };
                })}
                onChange={(value) => null}
            />
        );
    };

    const displayFixedMessage = isFixedAmount && !customAmount;

    const displayFixedRecurringMessage =
        isRecurring &&
        (displayFixedMessage || isRecurringAdmin || recurringLengthOfTime > 0 || recurringBillingInterval > 1);

    const displayFixedPriceMessage = displayFixedMessage && !displayFixedRecurringMessage;

    return (
        <BaseControl id="amount-field" label={label}>
            <div style={{display: 'flex', flexDirection: 'column', gap: '24px'}}>
                {isRecurringDonor && <BillingPeriodControl options={recurringBillingPeriodOptions} />}

                {isMultiLevel && <DonationLevels />}

                {customAmount && <CustomAmount />}

                {displayFixedRecurringMessage && <FixedRecurringMessage />}

                {displayFixedPriceMessage && <FixedPriceMessage />}

                <Inspector attributes={attributes} setAttributes={setAttributes} />
            </div>
        </BaseControl>
    );
};

export default Edit;
