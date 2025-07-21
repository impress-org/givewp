import {__} from '@wordpress/i18n';
import {dispatch} from '@wordpress/data';
import {store as coreStore} from '@wordpress/core-data';

//@ts-ignore
dispatch(coreStore).addEntities([
    {
        name: 'donation',
        kind: 'givewp',
        baseURL: '/givewp/v3/donations',
        baseURLParams: {includeSensitiveData: true, anonymousDonations: 'include'},
        plural: 'donations',
        label: __('Donation', 'give'),
        supportsPagination: true,
    },
]);
