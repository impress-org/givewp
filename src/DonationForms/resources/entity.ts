import {__} from '@wordpress/i18n';
import {dispatch} from '@wordpress/data';
import {store} from '@wordpress/core-data';

//@ts-ignore
dispatch(store).addEntities([
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
