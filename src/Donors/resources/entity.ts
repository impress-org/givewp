import {__} from '@wordpress/i18n';
import {dispatch} from '@wordpress/data';
import {store} from '@wordpress/core-data';

//@ts-ignore
dispatch(store).addEntities([
    {
        name: 'donor',
        kind: 'givewp',
        baseURL: '/givewp/v3/donors',
        baseURLParams: {},
        plural: 'donors',
        label: __('Donor', 'give'),
        supportsPagination: true,
    },
]);
