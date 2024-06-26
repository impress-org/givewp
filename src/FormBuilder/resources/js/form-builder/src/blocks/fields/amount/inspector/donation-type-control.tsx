import {__} from '@wordpress/i18n';
import {
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';

type DonationTypeControlProps = {
    priceOption: string;
    setAttributes: any;
    attributes: any;
};

/**
 * @since 3.12.0
 */

export default function DonationTypeControl({priceOption, setAttributes, attributes}: DonationTypeControlProps) {
    const handleTypeChange = (value: string) => {
        setAttributes({priceOption: value});
    };

    return (
        <ToggleGroupControl
            __nextHasNoMarginBottom
            isBlock
            label={__('Donation Type', 'give')}
            onChange={handleTypeChange}
            value={priceOption}
            help={
                'multi' === priceOption
                    ? __('Set multiple price donations for this form.', 'give')
                    : __('The donation amount is fixed to the following amount:', 'give')
            }
        >
            <ToggleGroupControlOption label={__('Multi-level', 'give')} value="multi" />
            <ToggleGroupControlOption label={__('Fixed', 'give')} value="set" />
        </ToggleGroupControl>
    );
}
