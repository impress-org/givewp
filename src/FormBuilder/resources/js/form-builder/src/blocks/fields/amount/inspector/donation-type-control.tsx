import {BaseControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import cx from 'classnames';
import './styles.scss';

type DonationTypeControlProps = {
    priceOption: string;
    setAttributes: any;
    attributes: any;
};

export default function DonationTypeControl({priceOption, setAttributes, attributes}: DonationTypeControlProps) {
    const handleTypeChange = (value: string) => {
        setAttributes({priceOption: value});
    };

    return (
        <BaseControl
            id={'givewp-donation-type-controls'}
            label={__('Donation Type', 'give')}
            help={
                'multi' === priceOption
                    ? __('Set multiple price donations for this form.', 'give')
                    : __('The donation amount is fixed to the following amount:', 'give')
            }
        >
            <div className={'givewp-donation-type-control'}>
                <label
                    className={cx('givewp-donation-type-control__multi', {
                        ['givewp-donation-type-control__multi--selected']: priceOption === 'multi',
                    })}
                >
                    {__('Multi-level', 'give')}
                    <input
                        className={'givewp-donation-type-control__multi__input'}
                        type={'checkbox'}
                        onChange={() => handleTypeChange('multi')}
                    />
                </label>
                <label
                    className={cx('givewp-donation-type-control__set', {
                        ['givewp-donation-type-control__set--selected']: priceOption === 'set',
                    })}
                >
                    {__('Fixed', 'give')}
                    <input
                        className={'givewp-donation-type-control__set__input'}
                        type={'checkbox'}
                        onChange={() => handleTypeChange('set')}
                    />
                </label>
            </div>
        </BaseControl>
    );
}
