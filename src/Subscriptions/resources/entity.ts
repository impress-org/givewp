import {store as coreStore} from '@wordpress/core-data';
import {dispatch} from '@wordpress/data';
import {__} from '@wordpress/i18n';

/**
 * @unreleased Added gatewaySubscriptionId to baseURLParams
 * @unreleased added _embed
 * @since 4.8.0
 */
//@ts-ignore
dispatch(coreStore).addEntities([
    {
        name: 'subscription',
        kind: 'givewp',
        baseURL: '/givewp/v3/subscriptions',
        baseURLParams: {_embed: true, includeSensitiveData: true},
        plural: 'subscriptions',
        label: __('Subscription', 'give'),
        supportsPagination: true,
    },
]);
