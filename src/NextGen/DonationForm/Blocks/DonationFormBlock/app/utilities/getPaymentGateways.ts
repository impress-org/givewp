import Gateway from '../types/Gateway';

export default function getPaymentGateways(gateways: object[]) {
    return gateways.map(({name, label}: Gateway) => {
        return {
            name,
            label,
        };
    });
}
