import {__} from '@wordpress/i18n';

import LevelGrid from './level-grid';
import LevelButton from './level-buttons';
import Inspector from './inspector';
import {CurrencyControl, formatCurrencyAmount} from '../../../common/currency';
import {createInterpolateElement} from '@wordpress/element';
import {BaseControl} from "@wordpress/components";

const Edit = ({attributes, setAttributes}) => {
    const {
        label = __('Donation Amount', 'give'),
        levels,
        priceOption,
        setPrice,
        customAmount
    } = attributes;

    const isMultiLevel = priceOption === 'multi';
    const isFixedAmount = priceOption === 'set';

    const FixedPriceMessage = () => {
        const amount = formatCurrencyAmount(setPrice);

        return createInterpolateElement(__('This donation is set to <amount/> for this form.', 'give'), {
            amount: <strong>{amount}</strong>,
        });
    };

    return (
        <>
            <BaseControl id="amount-field" label={label}>
                <div style={{display: 'flex', flexDirection: 'column', gap: '20px'}}>
                    {!!isFixedAmount && !customAmount && (
                        <div style={{backgroundColor: 'var(--givewp-gray-20)', padding: '12px 16px', borderRadius: '5px'}}>
                            <FixedPriceMessage />
                        </div>
                    )}
                    {!!isMultiLevel && levels.length > 0 && (
                        <LevelGrid>
                            {levels.map((level, index) => {
                                const levelAmount = formatCurrencyAmount(level);

                                return <LevelButton key={index}>{levelAmount}</LevelButton>;
                            })}
                        </LevelGrid>
                    )}
                    {!!customAmount && (
                        <div>
                            <CurrencyControl value={setPrice} label={__('Custom amount', 'give')} hideLabelFromVision />
                        </div>
                    )}
                </div>
            </BaseControl>

            <Inspector attributes={attributes} setAttributes={setAttributes} />
        </>
    );
};

export default Edit;
