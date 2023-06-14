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
import DeleteButton from './delete-button';
import AddButton from './add-button';
import {CurrencyControl} from '@givewp/form-builder/common/currency';
import periodLookup from '../period-lookup';
import RecurringDonationsPromo from '@givewp/form-builder/promos/recurring-donations';
import {getFormBuilderData} from '@givewp/form-builder/common/getWindowData';
import {useCallback} from '@wordpress/element';

const compareBillingPeriods = (val1: string, val2: string): number => {
    const index1 = Object.keys(periodLookup).indexOf(val1);
    const index2 = Object.keys(periodLookup).indexOf(val2);

    return index1 - index2;
};

const Inspector = ({attributes, setAttributes}) => {
    const {
        label = __('Donation Amount', 'give'),
        levels,
        priceOption,
        setPrice,
        customAmount,
        customAmountMin,
        customAmountMax,
        recurringEnabled,
        recurringDonationChoice,
        recurringBillingInterval,
        recurringBillingPeriod,
        recurringBillingPeriodOptions,
        recurringLengthOfTime,
        recurringOptInDefaultBillingPeriod,
    } = attributes;

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

    const {gateways, recurringAddonData, gatewaySettingsUrl} = getFormBuilderData();
    const enabledGateways = gateways.filter((gateway) => gateway.enabled);
    const recurringGateways = gateways.filter((gateway) => gateway.supportsSubscriptions);
    const isRecurringSupported = enabledGateways.some((gateway) => gateway.supportsSubscriptions);
    const isRecurring = isRecurringSupported && recurringEnabled;

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
                        onBlur={() => setPrice || setAttributes({setPrice: 25})}
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
                    {levels.length > 0 && (
                        <ul
                            style={{
                                listStyleType: 'none',
                                padding: 0,
                                display: 'flex',
                                flexDirection: 'column',
                                gap: '16px',
                            }}
                        >
                            {levels.map((amount, index) => {
                                return (
                                    <li
                                        key={'level-option-inspector-' + index}
                                        style={{
                                            display: 'flex',
                                            gap: '16px',
                                            justifyContent: 'space-between',
                                            alignItems: 'flex-end',
                                        }}
                                        className={'givewp-donation-level-control'}
                                    >
                                        <CurrencyControl
                                            label={__('Donation amount level', 'give')}
                                            hideLabelFromVision
                                            value={amount}
                                            onValueChange={(value) => {
                                                const newLevels = [...levels];

                                                newLevels[index] = value;
                                                setAttributes({levels: newLevels});
                                            }}
                                        />
                                        <DeleteButton
                                            onClick={() => {
                                                levels.splice(index, 1);
                                                setAttributes({levels: levels.slice()});
                                            }}
                                        />
                                    </li>
                                );
                            })}
                        </ul>
                    )}
                    <AddButton
                        onClick={() => {
                            const newLevels = [...levels];
                            const lastLevel = newLevels[newLevels.length - 1];
                            const nextLevel = lastLevel ? lastLevel * 2 : 10;

                            newLevels.push(nextLevel.toString());
                            setAttributes({levels: newLevels});
                        }}
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
                    <PanelRow>
                        <ToggleControl
                            label={__('Enable recurring donation', 'give')}
                            checked={recurringEnabled}
                            onChange={() => setAttributes({recurringEnabled: !recurringEnabled})}
                        />
                    </PanelRow>
                )}
                {!!isRecurring && (
                    <PanelRow>
                        <SelectControl
                            label={__('Donation choice', 'give')}
                            options={[
                                {label: __('Admin', 'give'), value: 'admin'},
                                {label: __('Donor', 'give'), value: 'donor'},
                            ]}
                            value={recurringDonationChoice}
                            onChange={(recurringDonationChoice) => setAttributes({recurringDonationChoice})}
                        />
                    </PanelRow>
                )}
                {!!isRecurring && (
                    <PanelRow>
                        <SelectControl
                            label={__('Billing interval', 'give')}
                            options={[
                                {label: __('Every', 'give'), value: '1'},
                                {label: __('Every 2nd', 'give'), value: '2'},
                                {label: __('Every 3rd', 'give'), value: '3'},
                                {label: __('Every 4th', 'give'), value: '4'},
                                {label: __('Every 5th', 'give'), value: '5'},
                                {label: __('Every 6th', 'give'), value: '6'},
                            ]}
                            value={recurringBillingInterval}
                            onChange={(recurringBillingInterval) => setAttributes({recurringBillingInterval})}
                        />
                    </PanelRow>
                )}
                {!!isRecurring && (
                    <PanelRow>
                        {'admin' === recurringDonationChoice && (
                            <SelectControl
                                label={__('Billing period', 'give')}
                                options={[
                                    {label: __('Day', 'give'), value: 'day'},
                                    {label: __('Week', 'give'), value: 'week'},
                                    {label: __('Month', 'give'), value: 'month'},
                                    {label: __('Quarter', 'give'), value: 'quarter'},
                                    {label: __('Year', 'give'), value: 'year'},
                                ]}
                                value={recurringBillingPeriod}
                                onChange={(recurringBillingPeriod) => setAttributes({recurringBillingPeriod})}
                            />
                        )}
                        {'donor' === recurringDonationChoice && (
                            <BaseControl id={'recurringBillingPeriodOptions'} label={__('Billing period', 'give')}>
                                <div
                                    style={{
                                        width: '100%',
                                        display: 'grid',
                                        gridTemplateColumns: '1fr 1fr',
                                    }}
                                >
                                    {[
                                        {label: __('Day', 'give'), value: 'day'},
                                        {label: __('Week', 'give'), value: 'week'},
                                        {label: __('Month', 'give'), value: 'month'},
                                        {label: __('Quarter', 'give'), value: 'quarter'},
                                        {label: __('Year', 'give'), value: 'year'},
                                    ].map((option) => (
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
                                            //@ts-ignore
                                            __nextHasNoMarginBottom={true}
                                        />
                                    ))}
                                </div>
                            </BaseControl>
                        )}
                    </PanelRow>
                )}
                {isRecurring && 'donor' === recurringDonationChoice && (
                    <PanelRow>
                        <SelectControl
                            label={__('Default billing period', 'give')}
                            value={recurringOptInDefaultBillingPeriod ?? 'month'}
                            options={['one-time'].concat(recurringBillingPeriodOptions).map((value) => ({
                                label: periodLookup[value].singular
                                    .toLowerCase()
                                    .replace(/\w/, (firstLetter) => firstLetter.toUpperCase()),
                                value: value,
                            }))}
                            onChange={(recurringOptInDefaultBillingPeriod) =>
                                setAttributes({recurringOptInDefaultBillingPeriod})
                            }
                        />
                    </PanelRow>
                )}
                {isRecurring && (
                    <PanelRow>
                        <SelectControl
                            label={__('Number of Payments', 'give')}
                            //@ts-ignore
                            options={[{label: __('Ongoing', 'give'), value: 0}].concat(
                                [...Array(24 + 1).keys()].slice(2).map((value) => ({
                                    label: sprintf(__('%d payments', 'give'), value),
                                    value: value,
                                }))
                            )}
                            value={recurringLengthOfTime}
                            onChange={(recurringLengthOfTime) => setAttributes({recurringLengthOfTime})}
                        />
                    </PanelRow>
                )}
            </PanelBody>
        </InspectorControls>
    );
};

export default Inspector;
