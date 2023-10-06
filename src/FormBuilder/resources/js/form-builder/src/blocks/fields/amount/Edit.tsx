import {__} from '@wordpress/i18n';

import LevelGrid from './level-grid';
import LevelButton from './level-buttons';
import Inspector from './inspector';
import periodLookup from './period-lookup';
import {CurrencyControl, formatCurrencyAmount} from '@givewp/form-builder/components/CurrencyControl';
import {BaseControl, RadioControl} from '@wordpress/components';
import {OneTimeAmountMessage, RecurringAmountMessage} from '@givewp/forms/shared/AmountMessages';
import Notice from './notice';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {DonationAmountAttributes} from '@givewp/form-builder/blocks/fields/amount/types';

const DonationLevels = ({levels, defaultLevel}: {levels: DonationAmountAttributes['levels']; defaultLevel: DonationAmountAttributes['defaultLevel']}) => (
    <LevelGrid>
        {levels.map((level, index) => {
            const levelAmount = formatCurrencyAmount(level.toString());

            return (
                <LevelButton selected={level === defaultLevel} key={index}>
                    {levelAmount}
                </LevelButton>
            );
        })}
    </LevelGrid>
);

const CustomAmount = ({amount}: {amount: DonationAmountAttributes['setPrice']}) => (
    <CurrencyControl value={amount} label={__('Custom amount', 'give')} hideLabelFromVision />
);

const FixedPriceMessage = ({amount}: {amount: string}) => (
    <Notice>
        <OneTimeAmountMessage amount={amount} />
    </Notice>
);

const BillingPeriodControl = ({options, defaultSelected}: {options: string[]; defaultSelected?: string}) => {
    return (
        <RadioControl
            className={'give-billing-period-control'}
            label={__('Billing Period', 'give')}
            hideLabelFromVision={true}
            selected={defaultSelected ?? options[0]}
            options={options.map((option) => {
                return {
                    label: 'one-time' === option ? __('One Time', 'give') : periodLookup[option].adjective,
                    value: option,
                };
            })}
            onChange={(value) => null}
        />
    );
};

const Edit = ({attributes, setAttributes}) => {
    const {
        label = __('Donation Amount', 'give'),
        levels,
        defaultLevel,
        priceOption,
        setPrice,
        customAmount,
        recurringEnabled,
        recurringBillingInterval,
        recurringBillingPeriodOptions,
        recurringLengthOfTime,
        recurringOptInDefaultBillingPeriod,
        recurringEnableOneTimeDonations,
    } = attributes as DonationAmountAttributes;

    const {gateways} = getFormBuilderWindowData();

    const isRecurringSupported = gateways.some((gateway) => gateway.enabled && gateway.supportsSubscriptions);
    const isRecurring = isRecurringSupported && recurringEnabled;
    const isMultiLevel = priceOption === 'multi';
    const isFixedAmount = priceOption === 'set';
    const isRecurringDonor =
        isRecurring && (recurringBillingPeriodOptions.length > 1 || recurringEnableOneTimeDonations);
    const isRecurringAdmin = isRecurring && !isRecurringDonor;
    const displayFixedMessage = isFixedAmount && !customAmount;
    const displayFixedRecurringMessage =
        isRecurring &&
        (displayFixedMessage ||
            isRecurringAdmin ||
            Number(recurringLengthOfTime) > 0 ||
            Number(recurringBillingInterval) > 1);
    const displayFixedPriceMessage = displayFixedMessage && !displayFixedRecurringMessage;
    const amountFormatted = formatCurrencyAmount(setPrice.toString());

    return (
        <BaseControl id="amount-field" label={label}>
            <div className="give-donation-block">
                {isRecurringDonor && (
                    <BillingPeriodControl
                        options={
                            recurringEnableOneTimeDonations
                                ? ['one-time'].concat(recurringBillingPeriodOptions)
                                : recurringBillingPeriodOptions
                        }
                        defaultSelected={recurringOptInDefaultBillingPeriod}
                    />
                )}

                {isMultiLevel && <DonationLevels levels={levels} defaultLevel={defaultLevel} />}

                {customAmount && <CustomAmount amount={setPrice} />}

                {displayFixedRecurringMessage && (
                    <Notice>
                        <RecurringAmountMessage
                            isFixedAmount={isFixedAmount}
                            fixedAmount={amountFormatted}
                            period={recurringBillingPeriodOptions[0]}
                            frequency={parseInt(recurringBillingInterval)}
                            installments={parseInt(recurringLengthOfTime)}
                        />
                    </Notice>
                )}

                {displayFixedPriceMessage && <FixedPriceMessage amount={amountFormatted} />}

                <Inspector attributes={attributes} setAttributes={setAttributes} />
            </div>
        </BaseControl>
    );
};

export default Edit;
