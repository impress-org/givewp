import apiFetch from '@wordpress/api-fetch';
import { useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';

/**
 * @unreleased
 */
export default function useDonationRefund(donationId: number) {
    const [isRefunding, setIsRefunding] = useState(false);
    const [isRefunded, setIsRefunded] = useState(false);
    const dispatch = useDispatch('givewp/admin-details-page-notifications');

    const refund = async () => {
        setIsRefunding(true);
        const response = await apiFetch({path: `/givewp/v3/donations/${donationId}/refund`, method: 'POST'}) as Response;

        if (response.ok) {
            const data = await response.json();
            console.log(data);
            setIsRefunded(true);
            dispatch.addSnackbarNotice({
                id: 'refund-donation',
                content: __('Refund completed successfully', 'give'),
            });
        } else {
            console.error(response);
        }

        setIsRefunding(false);
    };

    return {
        isRefunding,
        refund,
        isRefunded,
    };
}
