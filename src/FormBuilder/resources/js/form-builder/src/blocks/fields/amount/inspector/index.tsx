import {
    BaseControl,
    CheckboxControl,
    PanelBody,
    PanelRow,
    SelectControl,
    TextControl,
    ToggleControl,
} from '@wordpress/components';
import {__, sprintf} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import {CurrencyControl, formatCurrencyAmount} from '@givewp/form-builder/components/CurrencyControl';
import periodLookup from '../period-lookup';
import RecurringDonationsPromo from '@givewp/form-builder/promos/recurring-donations';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';
import {useCallback, useState} from '@wordpress/element';
import Options from '@givewp/form-builder/components/OptionsPanel';
import {OptionProps} from '@givewp/form-builder/components/OptionsPanel/types';
import {useEffect} from 'react';
import {DonationAmountAttributes} from '@givewp/form-builder/blocks/fields/amount/types';
import {subscriptionPeriod} from '@givewp/forms/registrars/templates/groups/DonationAmount/subscriptionPeriod';

const compareBillingPeriods = (val1: string, val2: string): number => {
    const index1 = Object.keys(periodLookup).indexOf(val1);
    const index2 = Object.keys(periodLookup).indexOf(val2);

    return index1 - index2;
};

type billingPeriodControlOption = {
    label: string;
    value: subscriptionPeriod;
};

const billingPeriodControlOptions: billingPeriodControlOption[] = [
    {label: __('Daily', 'give'), value: 'day'},
    {label: __('Weekly', 'give'), value: 'week'},
    {label: __('Monthly', 'give'), value: 'month'},
    {label: __('Quarterly', 'give'), value: 'quarter'},
    {label: __('Yearly', 'give'), value: 'year'},
];

const billingIntervalControlOptions = [
    {label: __('Every', 'give'), value: '1'},
    {label: __('Every 2nd', 'give'), value: '2'},
    {label: __('Every 3rd', 'give'), value: '3'},
    {label: __('Every 4th', 'give'), value: '4'},
    {label: __('Every 5th', 'give'), value: '5'},
    {label: __('Every 6th', 'give'), value: '6'},
];

const numberOfDonationsControlOptions = [{label: __('Ongoing', 'give'), value: '0'}].concat(
    [...Array(24 + 1).keys()].slice(2).map((value) => ({
        label: sprintf(__('%d donations', 'give'), value),
        value: value.toString(),
    }))
);

const Inspector = ({attributes, setAttributes}) => {
    const {
        label = __('Donation Amount', 'give'),
        levels,
        defaultLevel,
        priceOption,
        setPrice,
        customAmount,
        customAmountMin,
        customAmountMax,
        recurringEnabled,
        recurringBillingInterval,
        recurringBillingPeriodOptions,
        recurringLengthOfTime,
        recurringOptInDefaultBillingPeriod,
        recurringEnableOneTimeDonations = true,
    } = attributes as DonationAmountAttributes;

    const shouldShowDefaultBillingPeriod = recurringBillingPeriodOptions.length > 1 || recurringEnableOneTimeDonations;

    useEffect(() => {
        // update recurringOptInDefaultBillingPeriod based on the available options
        if (recurringOptInDefaultBillingPeriod === 'one-time' && !recurringEnableOneTimeDonations) {
            setAttributes({recurringOptInDefaultBillingPeriod: recurringBillingPeriodOptions[0]});
        } else if (!['one-time'].concat(recurringBillingPeriodOptions).includes(recurringOptInDefaultBillingPeriod)) {
            setAttributes({recurringOptInDefaultBillingPeriod: recurringBillingPeriodOptions[0]});
        }
    }, [recurringBillingPeriodOptions, recurringEnableOneTimeDonations]);

    const addBillingPeriodOption = useCallback(
        (value) => {
            const options = Array.from(new Set(recurringBillingPeriodOptions.concat([value])));

            options.sort(compareBillingPeriods);

            setAttributes({
                recurringBillingPeriodOptions: options,
            });
        },
        [recurringBillingPeriodOptions]
    );

    const removeBillingPeriodOption = useCallback(
        (value) => {
            const options = recurringBillingPeriodOptions.filter((option) => option !== value);

            if (recurringBillingPeriodOptions.length > 1) {
                options.sort(compareBillingPeriods);

                setAttributes({
                    recurringBillingPeriodOptions: options,
                });
            }
        },
        [recurringBillingPeriodOptions]
    );

    const {gateways, recurringAddonData, gatewaySettingsUrl} = getFormBuilderWindowData();
    const enabledGateways = gateways.filter((gateway) => gateway.enabled);
    const recurringGateways = gateways.filter((gateway) => gateway.supportsSubscriptions);
    const isRecurringSupported = enabledGateways.some((gateway) => gateway.supportsSubscriptions);
    const isRecurring = isRecurringSupported && recurringEnabled;

    const [donationLevels, setDonationLevels] = useState<OptionProps[]>(
        levels.map((level) => ({
            label: formatCurrencyAmount(level.toString()),
            value: level.toString(),
            checked: defaultLevel === level,
        }))
    );

    const handleLevelsChange = (options: OptionProps[]) => {
        if (options.length > 1 && options[options.length - 1].value === '') {
            const values = options.filter((option) => Number(option.value) > 0).map((option) => Number(option.value));
            options[options.length - 1].value = String(2 * Math.max(...values));
        } else if (options.length === 1 && options[0].value === '') {
            options[0].value = '10';
        }

        const checkedLevel = options.filter((option) => option.checked);

        if (!!checkedLevel && checkedLevel.length === 1) {
            setAttributes({defaultLevel: Number(checkedLevel[0].value)});
        } else if (options.length > 0) {
            options[0].checked = true;
        }

        setDonationLevels(options);
        const newLevels = options.filter((option) => option.value).map((option) => Number(option.value));

        setAttributes({levels: newLevels});
    };

    const getDefaultBillingPeriodOptions = useCallback(
        (options) => {
            if (recurringEnableOneTimeDonations) {
                options = ['one-time'].concat(options);
            }

            return options.map((value) => ({
                label: periodLookup[value].singular
                    .toLowerCase()
                    .replace(/\w/, (firstLetter) => firstLetter.toUpperCase()),
                value: value,
            }));
        },
        [recurringBillingPeriodOptions, recurringEnableOneTimeDonations]
    );

    return (
        <InspectorControls>
            <PanelBody title={__('Field Settings', 'give')} initialOpen={true}>
                <PanelRow>
                    <TextControl
                        label={__('Label', 'give')}
                        value={label}
                        onChange={(label) => setAttributes({label})}
                    />
                </PanelRow>
            </PanelBody>
            <PanelBody title={__('Donation Options', 'give')} initialOpen={true}>
                <SelectControl
                    label={__('Donation Option', 'give')}
                    onChange={(priceOption) => setAttributes({priceOption})}
                    value={priceOption}
                    options={[
                        {label: __('Multi-level Donation', 'give'), value: 'multi'},
                        {label: __('Fixed Donation', 'give'), value: 'set'},
                    ]}
                    help={
                        'multi' === priceOption
                            ? __('Set multiple price donations for this form.', 'give')
                            : __('The donation amount is fixed to the following amount:', 'give')
                    }
                />
                {priceOption === 'set' && (
                    <CurrencyControl
                        label={__('Set Donation', 'give')}
                        value={setPrice}
                        onBlur={() => !setPrice && setAttributes({setPrice: 25})}
                        onValueChange={(setPrice) => setAttributes({setPrice: setPrice ? parseInt(setPrice) : 0})}
                    />
                )}
            </PanelBody>
            <PanelBody title={__('Custom Amount', 'give')} initialOpen={false}>
                <ToggleControl
                    label={__('Custom Amount', 'give')}
                    checked={customAmount}
                    onChange={() => setAttributes({customAmount: !customAmount})}
                />
                {!!customAmount && (
                    <>
                        <CurrencyControl
                            label={__('Minimum', 'give')}
                            value={customAmountMin}
                            onValueChange={(value) => setAttributes({customAmountMin: value})}
                            help={__('Sets the minimum donation amount for all gateways.', 'give')}
                        />
                        <CurrencyControl
                            label={__('Maximum', 'give')}
                            value={customAmountMax}
                            onValueChange={(value) => setAttributes({customAmountMax: value})}
                            help={__(
                                'Sets the maximum donation amount for all gateways. Leave empty for no maximum amount.',
                                'give'
                            )}
                        />
                    </>
                )}
            </PanelBody>

            {priceOption === 'multi' && (
                <PanelBody title={__('Donation Levels', 'give')} initialOpen={false}>
                    <Options
                        currency={true}
                        multiple={false}
                        options={donationLevels}
                        setOptions={handleLevelsChange}
                        defaultControlsTooltip={__('Default Level', 'give')}
                    />
                </PanelBody>
            )}

            <PanelBody title={__('Recurring Donations', 'give')} initialOpen={false}>
                {!isRecurringSupported &&
                    (recurringAddonData.isInstalled ? (
                        <div
                            style={{
                                fontSize: '13px',
                                lineHeight: '1.3em',
                                display: 'flex',
                                flexDirection: 'column',
                                gap: '12px',
                                padding: '6px 12px 12px 0',
                            }}
                        >
                            <div>
                                {__(
                                    'None of the payment gateways currently enabled support Recurring Donations. To collect recurring donations, enable one of the following payment gateways:',
                                    'give'
                                )}
                            </div>
                            <ul style={{listStyleType: 'inherit', marginLeft: '12px'}}>
                                {recurringGateways.map((gateway) => (
                                    <li key={gateway.id}>{gateway.label}</li>
                                ))}
                            </ul>
                            <a href={gatewaySettingsUrl} target="_blank" rel="noreferrer noopener">
                                Go to Payment Gateway Settings
                            </a>
                        </div>
                    ) : (
                        <RecurringDonationsPromo />
                    ))}

                {isRecurringSupported && (
                    <>
                        <PanelRow>
                            <ToggleControl
                                label={__('Enable recurring donation', 'give')}
                                checked={recurringEnabled}
                                onChange={() => setAttributes({recurringEnabled: !recurringEnabled})}
                            />
                        </PanelRow>
                    </>
                )}
                {!!isRecurring && (
                    <>
                        <PanelRow>
                            <ToggleControl
                                label={__('Enable one-time donation', 'give')}
                                checked={recurringEnableOneTimeDonations}
                                onChange={() => {
                                    setAttributes({
                                        recurringEnableOneTimeDonations: !recurringEnableOneTimeDonations,
                                    });
                                }}
                            />
                        </PanelRow>
                        <PanelRow>
                            <BaseControl id={'recurringBillingPeriodOptions'} label={__('Frequency', 'give')}>
                                <div
                                    style={{
                                        width: '100%',
                                        display: 'grid',
                                        gridTemplateColumns: '1fr 1fr',
                                    }}
                                >
                                    {billingPeriodControlOptions.map((option) => (
                                        <CheckboxControl
                                            key={option.value}
                                            label={option.label}
                                            checked={recurringBillingPeriodOptions.includes(option.value)}
                                            onChange={(checked) =>
                                                checked
                                                    ? addBillingPeriodOption(option.value)
                                                    : removeBillingPeriodOption(option.value)
                                            }
                                            disabled={
                                                recurringBillingPeriodOptions.length === 1 &&
                                                recurringBillingPeriodOptions.includes(option.value) // This is the last checked option.
                                            }
                                            __nextHasNoMarginBottom={true}
                                        />
                                    ))}
                                </div>
                            </BaseControl>
                        </PanelRow>
                        {shouldShowDefaultBillingPeriod && (
                            <PanelRow>
                                <SelectControl
                                    label={__('Default Frequency', 'give')}
                                    value={
                                        recurringOptInDefaultBillingPeriod ??
                                        (recurringEnableOneTimeDonations
                                            ? 'one-time'
                                            : recurringBillingPeriodOptions[0])
                                    }
                                    options={getDefaultBillingPeriodOptions(recurringBillingPeriodOptions)}
                                    onChange={(recurringOptInDefaultBillingPeriod: string) =>
                                        setAttributes({recurringOptInDefaultBillingPeriod})
                                    }
                                />
                            </PanelRow>
                        )}
                        <PanelRow>
                            <SelectControl
                                label={__('Interval', 'give')}
                                options={billingIntervalControlOptions}
                                value={recurringBillingInterval}
                                onChange={(recurringBillingInterval: string) =>
                                    setAttributes({recurringBillingInterval})
                                }
                            />
                        </PanelRow>
                        <PanelRow>
                            <SelectControl
                                label={__('Number of Donations', 'give')}
                                options={numberOfDonationsControlOptions}
                                value={recurringLengthOfTime}
                                onChange={(recurringLengthOfTime: string) => setAttributes({recurringLengthOfTime})}
                            />
                        </PanelRow>
                    </>
                )}
            </PanelBody>
        </InspectorControls>
    );
};

export default Inspector;
