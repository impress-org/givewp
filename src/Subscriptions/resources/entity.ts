import {store as coreStore} from '@wordpress/core-data';
import {dispatch} from '@wordpress/data';
import {__} from '@wordpress/i18n';

/**
 * @since 4.13.0 added anonymousDonors to baseURLParams
 * @since 4.11.0 Added gatewaySubscriptionId to baseURLParams
 * @since 4.10.0 added _embed
 * @since 4.8.0
 */
//@ts-ignore
dispatch(coreStore).addEntities([
    {
        name: 'subscription',
        kind: 'givewp',
        baseURL: '/givewp/v3/subscriptions',
        baseURLParams: {_embed: true, includeSensitiveData: true, anonymousDonors: 'include'},
        plural: 'subscriptions',
        label: __('Subscription', 'give'),
        supportsPagination: true,
    },
]);
