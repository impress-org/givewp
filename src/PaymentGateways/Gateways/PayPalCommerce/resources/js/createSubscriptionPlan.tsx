import type {PayPalCommerceGateway} from '../../types';

/**
 * @since 4.0.0
 */
export default async function createSubscriptionPlan(url: string, gateway: PayPalCommerceGateway, formData: FormData) {
    const response = await fetch(url, {
        method: 'POST',
        body: formData,
    });

    const responseJson = await response.json();

    if (!responseJson.success) {
        throw responseJson.data.error;
    }

    const planId = responseJson.data.id;

    gateway.payPalPlanId = planId;

    return {
        planId,
        userAction: responseJson.data?.user_action,
    };
}
