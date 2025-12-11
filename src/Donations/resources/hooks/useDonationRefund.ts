import apiFetch from '@wordpress/api-fetch';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useMemo, useState } from 'react';
import { Donation } from '../admin/components/types';
import { getDonationOptionsWindowData } from '../utils';

/**
 * @since 4.6.0
 */
const canRefundDonation = (donation: Donation) => {
    const { gateways } = getDonationOptionsWindowData();

    //find the gateway in the gateways array
    const gateway = gateways.find((gateway) => gateway.id === donation.gatewayId && gateway.enabled);

    return gateway?.supportsRefund && donation.status === 'publish';
};

/**
 * @since 4.6.0
 */
const isResponseDonation = (response: unknown): response is Donation => {
    return typeof response === 'object' && response !== null && 'id' in response;
};



/**
 * @since 4.6.0
 */
export default function useDonationRefund(donation: Donation) {
    const [isRefunding, setIsRefunding] = useState(false);
    const [isRefunded, setIsRefunded] = useState(false);
    const dispatch = useDispatch('givewp/admin-details-page-notifications');
    const canRefund = useMemo(() => (donation ? canRefundDonation(donation) : false), [donation?.gatewayId, donation?.status]);
    const {invalidateResolution, invalidateResolutionForStore} = useDispatch('core');

    /**
     * TODO: This is a temporary solution to invalidate the donation cache.
     * There is most likely a better way to do this.
     */
    const invalidateDonationCache = () => {
        invalidateResolution('getEntityRecords', ['givewp', 'donation']);
        invalidateResolutionForStore();
    };

    const refund = async () => {
        setIsRefunding(true);
        const response = await apiFetch({path: `/givewp/v3/donations/${donation.id}/refund`, method: 'POST'});

        if (isResponseDonation(response) && response.status === 'refunded') {
            setIsRefunding(false);
            setIsRefunded(true);

            invalidateDonationCache();

            dispatch.addSnackbarNotice({
                id: 'refund-donation',
                content: __('Refund completed successfully', 'give'),
            });

            return response;
        } else {
            console.error('Failed to refund donation', response);
            setIsRefunding(false);
            setIsRefunded(false);

            dispatch.addSnackbarNotice({
                id: 'refund-donation',
                content: __('Failed to refund donation', 'give'),
            });

            throw new Error('Failed to refund donation');
        }
    };

    return {
        isRefunding,
        refund,
        isRefunded,
        canRefund,
    };
}
