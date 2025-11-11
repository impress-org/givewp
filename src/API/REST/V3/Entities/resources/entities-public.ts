import {__} from '@wordpress/i18n';
import {dispatch} from '@wordpress/data';
import {store as coreStore} from '@wordpress/core-data';

//@ts-ignore
dispatch(coreStore).addEntities([
    {
        name: 'campaign',
        kind: 'givewp',
        baseURL: '/givewp/v3/campaigns',
        baseURLParams: {},
        plural: 'campaigns',
        label: __('Campaign', 'give'),
        supportsPagination: true,
    },
    {
        name: 'donation',
        kind: 'givewp',
        baseURL: '/givewp/v3/donations',
        baseURLParams: {includeSensitiveData: false, anonymousDonations: 'redact', _embed: true},
        plural: 'donations',
        label: __('Donation', 'give'),
        supportsPagination: true,
    },
    {
        name: 'donor',
        kind: 'givewp',
        baseURL: '/givewp/v3/donors',
        baseURLParams: {includeSensitiveData: false, anonymousDonors: 'redact'},
        plural: 'donors',
        label: __('Donor', 'give'),
        supportsPagination: true,
    },
    {
        name: 'subscription',
        kind: 'givewp',
        baseURL: '/givewp/v3/subscriptions',
        baseURLParams: {_embed: true, includeSensitiveData: false, anonymousDonors: 'redact'},
        plural: 'subscriptions',
        label: __('Subscription', 'give'),
        supportsPagination: true,
    },
    {
        name: 'form',
        kind: 'givewp',
        baseURL: '/givewp/v3/forms',
        baseURLParams: {},
        plural: 'forms',
        label: __('Donation Form', 'give'),
        supportsPagination: true,
    },
]);
