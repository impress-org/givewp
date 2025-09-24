import {Subscription} from '@givewp/subscriptions/admin/components/types';

/**
 * @unreleased
 */
export default function getSubscriptionEmbeds(subscription: Subscription) {
    return {
        campaign: subscription?._embedded?.['givewp:campaign']?.[0],
        donor: subscription?._embedded?.['givewp:donor']?.[0],
        form: subscription?._embedded?.['givewp:form']?.[0],
        donations: subscription?._embedded?.['givewp:donations']?.[0],
    };
}
