import {useCallback} from 'react';
import PaymentGateway from '../value-objects/PaymentGateway';
import TestGatewayFields from '../fields/TestGatewayFields';

export default function usePaymentGatewayFields(gatewayId: PaymentGateway) {
    return useCallback(() => {
        switch (gatewayId) {
            case PaymentGateway.TEST_GATEWAY:
            case PaymentGateway.TEST_GATEWAY_NEXT_GEN:
                return <TestGatewayFields />;
            default:
                return <div>Fields for {gatewayId}</div>;
        }
    }, [gatewayId]);
}
