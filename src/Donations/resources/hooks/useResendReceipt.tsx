import { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';

/**
 * @since 4.6.0
 */
export default function useResendReceipt() {
    const [loading, setLoading] = useState<boolean>(false);
    const [message, setMessage] = useState<string | null>(__('Resend Receipt', 'give'));
    const [hasResolved, setHasResolved] = useState<boolean>(false);

    const urlParams = new URLSearchParams(window.location.search);
    const donationId = urlParams.get('id');
    const dispatch = useDispatch('givewp/admin-details-page-notifications');

    const handleResendReceipt = async () => {
        if (!donationId) {
            setMessage(__('Donation ID not found.', 'give'));
            setHasResolved(true);
            return;
        }

        setLoading(true);
        setMessage(__('Resending', 'give'));
        setHasResolved(false);

        try {
            const response = await fetch('/wp-json/give-api/v2/admin/donations/resendEmailReceipt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': (window as any).wpApiSettings?.nonce || '',
                },
                body: JSON.stringify({ ids: donationId }),
            });

            if (!response.ok) {
                throw new Error(await response.text());
            }
        } catch (error: any) {
            dispatch.addSnackbarNotice({
                id: 'resend-receipt-error',
                content: __('Failed to resend receipt', 'give'),
            });
        } finally {
            setLoading(false);
            setHasResolved(true);
            dispatch.addSnackbarNotice({
                id: 'resend-receipt',
                content: __('Receipt has been resent successfully', 'give'),
            });
        }
    };

    return { loading, message, hasResolved, handleResendReceipt };
}
