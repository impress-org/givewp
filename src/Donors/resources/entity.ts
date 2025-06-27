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
    }
]);
