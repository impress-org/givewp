import {useCallback} from 'react';
import {useDonationFormState} from '@givewp/forms/app/store';

/**
 * @since 3.0.0
 */
export default function useGetGatewayById() {
    const {gateways} = useDonationFormState();

    return useCallback((gatewayId: string) => gateways?.find(({id}) => id === gatewayId), [gateways]);
}