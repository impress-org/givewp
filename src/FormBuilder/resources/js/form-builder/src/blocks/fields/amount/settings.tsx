import {FieldBlock} from '@givewp/form-builder/types';
import {__} from '@wordpress/i18n';
import {Icon} from '@wordpress/icons';
import defaultSettings from '../settings';
import Edit from './Edit';

import getDefaultBlockAttributes from '@givewp/form-builder/common/getDefaultBlockAttributes';
import {Path, SVG} from '@wordpress/components';

const {
    recurringBillingInterval,
    recurringBillingPeriodOptions,
    recurringLengthOfTime,
    recurringEnabled,
    recurringEnableOneTimeDonations,
    customAmountMin,
    customAmount,
    setPrice,
    priceOption,
    levels,
} = getDefaultBlockAttributes('givewp/donation-amount');

const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Donation Amount and Levels', 'give'),
    description: __('The interface for donors to specify the amount they want to donate.', 'give'),
    supports: {
        multiple: false,
        html: false, // Removes support for an HTML mode.
    },
    attributes: {
        label: {
            type: 'string',
            source: 'attribute',
            default: __('Donation Amount', 'give'),
        },
        levels: {
            type: 'array',
            default: levels,
        },
        priceOption: {
            type: 'string',
            default: priceOption,
        },
        setPrice: {
            type: 'number',
            default: setPrice,
        },
        customAmount: {
            type: 'boolean',
            default: customAmount,
        },
        customAmountMin: {
            type: 'number',
            default: customAmountMin,
        },
        customAmountMax: {
            type: 'number',
        },
        recurringEnabled: {
            type: 'boolean',
            default: recurringEnabled,
        },
        recurringBillingInterval: {
            type: 'number',
            default: recurringBillingInterval,
        },
        recurringBillingPeriodOptions: {
            type: 'array',
            default: recurringBillingPeriodOptions,
        },
        recurringLengthOfTime: {
            type: 'string',
            default: recurringLengthOfTime, // ongoing
        },
        recurringEnableOneTimeDonations: {
            type: 'boolean',
            default: recurringEnableOneTimeDonations,
        },
    },
    edit: Edit,
    icon: () => (
        <Icon
            icon={
                <SVG width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        d="M11.5789 4.44118H20M4 7.52941L7.36842 4M7.36842 4V19M11.5789 8.85294H18.3158M11.5789 13.2647H16.6316M11.5789 17.6765H14.9474"
                        stroke="currentColor"
                        strokeWidth="1.5"
                        strokeLinecap="round"
                        strokeLinejoin="round"
                    />
                </SVG>
            }
        />
    ),
};

export default settings;
