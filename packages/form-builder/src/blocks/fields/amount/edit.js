import {__} from '@wordpress/i18n';

import LevelGrid from './level-grid';
import LevelButton from './level-buttons';
import Inspector from './inspector';
import {Currency, CurrencyControl} from '../../../common/currency';
import {createInterpolateElement} from '@wordpress/element';

const Edit = ({attributes, setAttributes}) => {
    const {levels, priceOption, setPrice, customAmount} = attributes;

    const isMultiLevel = priceOption === 'multi';
    const isFixedAmount = priceOption === 'set';

    const FixedPriceMessage = () => {
        return createInterpolateElement(__('This donation is set to <amount/> for this form.', 'give'), {
            amount: (
                <strong>
                    <Currency amount={setPrice} />
                </strong>
            ),
        });
    };

    return (
        <>
            <div style={{display: 'flex', flexDirection: 'column', gap: '20px'}}>
                {!!isFixedAmount && !customAmount && (
                    <div style={{backgroundColor: 'var(--givewp-gray-20)', padding: '12px 16px', borderRadius: '5px'}}>
                        <FixedPriceMessage />
                    </div>
                )}
                {!!isMultiLevel && levels.length > 0 && (
                    <LevelGrid>
                        {levels.map((level, index) => (
                            <LevelButton key={index}>
                                <Currency amount={level} />
                            </LevelButton>
                        ))}
                    </LevelGrid>
                )}
                {!!customAmount && (
                    <div>
                        <CurrencyControl value={setPrice} label={__('Custom amount', 'give')} hideLabelFromVision />
                    </div>
                )}
            </div>

            <Inspector attributes={attributes} setAttributes={setAttributes} />
        </>
    );
};

export default Edit;
