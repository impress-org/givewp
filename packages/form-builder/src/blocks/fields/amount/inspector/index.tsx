import {PanelBody, PanelRow, SelectControl, ToggleControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {InspectorControls} from '@wordpress/block-editor';
import DeleteButton from './delete-button';
import AddButton from './add-button';
import {CurrencyControl} from '@givewp/form-builder/common/currency';
import Label from "@givewp/form-builder/blocks/fields/settings/Label";

const Inspector = ({attributes, setAttributes}) => {
    const {
        label = __('Donation Amount', 'give'),
        levels,
        priceOption,
        setPrice,
        customAmount,
        customAmountMin,
        customAmountMax
    } = attributes;

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
                        onValueChange={(setPrice) => setAttributes({setPrice})}
                    />
                )}
            </PanelBody>
            <PanelBody title={__('Custom Amount', 'give')} initialOpen={false}>
                <ToggleControl
                    label={__('Custom Amount', 'give')}
                    checked={!!customAmount}
                    onChange={() => setAttributes({customAmount: !customAmount})}
                />
                {!!customAmount && (
                    <>
                        <CurrencyControl
                            label={__('Minimum', 'give')}
                            value={customAmountMin}
                            onValueChange={(value) => setAttributes({customAmountMin: value})}
                        />
                        <CurrencyControl
                            label={__('Maximum', 'give')}
                            value={customAmountMax}
                            onValueChange={(value) => setAttributes({customAmountMax: value})}
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
                                            alignItems: 'center',
                                        }}
                                        className={'givewp-donation-level-control'}
                                    >
                                        <CurrencyControl
                                            value={amount}
                                            onValueChange={(value) => {
                                                const newLevels = [...levels];

                                                newLevels[index] = value;
                                                setAttributes({levels: newLevels});
                                            }}
                                            label={__('amount level', 'give')}
                                            hideLabelFromVision
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
                            newLevels.push('');
                            setAttributes({levels: newLevels});
                        }}
                    />
                </PanelBody>
            )}
        </InspectorControls>
    );
};

export default Inspector;
