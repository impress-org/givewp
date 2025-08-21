import { __ } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

//@ts-ignore
dispatch(coreStore).addEntities([
    {
        name: 'subscription',
        kind: 'givewp',
        baseURL: '/givewp/v3/subscriptions',
        baseURLParams: {},
        plural: 'subscriptions',
        label: __('Subscription', 'give'),
        supportsPagination: true,
    },
]);
