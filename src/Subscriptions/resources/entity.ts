import { __ } from '@wordpress/i18n';
import { dispatch } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

/**
 * @since 4.10.0 added _embed
 * @since 4.8.0
 */
//@ts-ignore
dispatch(coreStore).addEntities([
    {
        name: 'subscription',
        kind: 'givewp',
        baseURL: '/givewp/v3/subscriptions',
        baseURLParams: {_embed: true},
        plural: 'subscriptions',
        label: __('Subscription', 'give'),
        supportsPagination: true,
    },
]);
