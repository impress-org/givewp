import {__} from '@wordpress/i18n';
import defaultSettings from '../settings';
import {FieldBlock} from '@givewp/form-builder/types';
import Edit from './Edit';
import {Icon} from '@wordpress/icons';
import {Path, SVG} from '@wordpress/components';

const settings: FieldBlock['settings'] = {
    ...defaultSettings,
    title: __('Billing Address', 'give'),
    description: __('Collects the donor billing address with display options.', 'give'),
    supports: {
        multiple: false,
    },
    attributes: {
        groupLabel: {
            type: 'string',
            source: 'attribute',
            selector: 'p',
            default: 'Billing Address',
        },
        country: {
            type: 'array',
            source: 'attribute',
            default: [{value: 'sample', label: __('A full country list will be displayed here...', 'give')}],
        },
        countryLabel: {
            type: 'string',
            source: 'attribute',
            default: __('Country', 'give'),
        },
        address1Label: {
            type: 'string',
            source: 'attribute',
            default: __('Address Line 1', 'give'),
        },
        address1Placeholder: {
            type: 'string',
            source: 'attribute',
            default: __('Address Line 1', 'give'),
        },
        address2Label: {
            type: 'string',
            source: 'attribute',
            default: __('Address Line 2', 'give'),
        },
        address2Placeholder: {
            type: 'string',
            source: 'attribute',
            default: __('Address Line 2', 'give'),
        },
        requireAddress2: {
            type: 'boolean',
            default: false,
        },
        cityLabel: {
            type: 'string',
            source: 'attribute',
            default: __('City', 'give'),
        },
        cityPlaceholder: {
            type: 'string',
            source: 'attribute',
            default: __('City', 'give'),
        },
        stateLabel: {
            type: 'string',
            source: 'attribute',
            default: __('State/Province/Country', 'give'),
        },
        statePlaceholder: {
            type: 'string',
            source: 'attribute',
            default: __('This changes by country selection...', 'give'),
        },
        zipLabel: {
            type: 'string',
            source: 'attribute',
            default: __('Zip/Postal Code', 'give'),
        },
        zipPlaceholder: {
            type: 'string',
            source: 'attribute',
            default: __('Zip/Postal Code', 'give'),
        },
    },
    edit: Edit,
    icon: () => (
        <Icon
            icon={
                <SVG width="19" height="20" viewBox="0 0 19 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <Path
                        d="M12.9166 11.3247C13.5076 10.8719 13.942 10.2452 14.1585 9.5328C14.3751 8.82039 14.3629 8.05804 14.1239 7.35287C13.8848 6.6477 13.4308 6.03517 12.8256 5.60135C12.2205 5.16752 11.4946 4.93422 10.75 4.93422C10.0054 4.93422 9.27953 5.16752 8.67437 5.60135C8.06922 6.03517 7.6152 6.6477 7.37613 7.35287C7.13705 8.05804 7.12494 8.82039 7.34149 9.5328C7.55804 10.2452 7.99237 10.8719 8.58344 11.3247C7.47438 11.7389 6.50807 12.4637 5.8 13.4125C5.71049 13.5318 5.67206 13.6819 5.69315 13.8295C5.71425 13.9772 5.79315 14.1105 5.9125 14.2C6.03185 14.2895 6.18186 14.3279 6.32955 14.3068C6.47723 14.2857 6.61049 14.2068 6.7 14.0875C7.17156 13.4588 7.78303 12.9484 8.48598 12.597C9.18894 12.2455 9.96407 12.0625 10.75 12.0625C11.5359 12.0625 12.3111 12.2455 13.014 12.597C13.717 12.9484 14.3284 13.4588 14.8 14.0875C14.8895 14.2068 15.0228 14.2857 15.1705 14.3068C15.3181 14.3279 15.4682 14.2895 15.5875 14.2C15.7068 14.1105 15.7857 13.9772 15.8068 13.8295C15.8279 13.6819 15.7895 13.5318 15.7 13.4125C14.9919 12.4637 14.0256 11.7389 12.9166 11.3247ZM8.3125 8.5C8.3125 8.01791 8.45546 7.54664 8.72329 7.1458C8.99113 6.74495 9.37181 6.43253 9.81721 6.24804C10.2626 6.06356 10.7527 6.01528 11.2255 6.10934C11.6984 6.20339 12.1327 6.43554 12.4736 6.77643C12.8145 7.11732 13.0466 7.55164 13.1407 8.02447C13.2347 8.4973 13.1864 8.9874 13.002 9.43279C12.8175 9.87819 12.505 10.2589 12.1042 10.5267C11.7034 10.7945 11.2321 10.9375 10.75 10.9375C10.1035 10.9375 9.48355 10.6807 9.02643 10.2236C8.56931 9.76645 8.3125 9.14647 8.3125 8.5ZM17.5 0.4375H4C3.6519 0.4375 3.31806 0.575781 3.07192 0.821922C2.82578 1.06806 2.6875 1.4019 2.6875 1.75V4.1875H1C0.850816 4.1875 0.707742 4.24676 0.602252 4.35225C0.496763 4.45774 0.4375 4.60082 0.4375 4.75C0.4375 4.89918 0.496763 5.04226 0.602252 5.14775C0.707742 5.25324 0.850816 5.3125 1 5.3125H2.6875V9.4375H1C0.850816 9.4375 0.707742 9.49676 0.602252 9.60225C0.496763 9.70774 0.4375 9.85082 0.4375 10C0.4375 10.1492 0.496763 10.2923 0.602252 10.3977C0.707742 10.5032 0.850816 10.5625 1 10.5625H2.6875V14.6875H1C0.850816 14.6875 0.707742 14.7468 0.602252 14.8523C0.496763 14.9577 0.4375 15.1008 0.4375 15.25C0.4375 15.3992 0.496763 15.5423 0.602252 15.6477C0.707742 15.7532 0.850816 15.8125 1 15.8125H2.6875V18.25C2.6875 18.5981 2.82578 18.9319 3.07192 19.1781C3.31806 19.4242 3.6519 19.5625 4 19.5625H17.5C17.8481 19.5625 18.1819 19.4242 18.4281 19.1781C18.6742 18.9319 18.8125 18.5981 18.8125 18.25V1.75C18.8125 1.4019 18.6742 1.06806 18.4281 0.821922C18.1819 0.575781 17.8481 0.4375 17.5 0.4375ZM17.6875 18.25C17.6875 18.2997 17.6677 18.3474 17.6326 18.3826C17.5974 18.4177 17.5497 18.4375 17.5 18.4375H4C3.95027 18.4375 3.90258 18.4177 3.86742 18.3826C3.83225 18.3474 3.8125 18.2997 3.8125 18.25V1.75C3.8125 1.70027 3.83225 1.65258 3.86742 1.61742C3.90258 1.58225 3.95027 1.5625 4 1.5625H17.5C17.5497 1.5625 17.5974 1.58225 17.6326 1.61742C17.6677 1.65258 17.6875 1.70027 17.6875 1.75V18.25Z"
                        fill="currentColor"
                    />
                </SVG>
            }
        />
    ),
};

export default settings;
