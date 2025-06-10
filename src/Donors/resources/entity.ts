import {__} from '@wordpress/i18n';
import {dispatch} from '@wordpress/data';
import {store as coreStore} from '@wordpress/core-data';

//@ts-ignore
dispatch(coreStore).addEntities([
    {
        name: 'donor',
        kind: 'givewp',
        baseURL: '/givewp/v3/donors',
        baseURLParams: {includeSensitiveData: true, anonymousDonors: 'include'},
        plural: 'donors',
        label: __('Donor', 'give'),
        supportsPagination: true,
    },
    {
        name: 'donations',
        kind: 'givewp/v3',
        baseURL: '/givewp/v3/donations',
        baseURLParams: {},
        plural: 'donations',
        label: __('Donations', 'give'),
        supportsPagination: true,
    },
]);
