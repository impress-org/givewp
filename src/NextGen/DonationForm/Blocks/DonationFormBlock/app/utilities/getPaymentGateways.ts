import type {Gateway} from '../types/Gateway';

export default function getPaymentGateways(gateways: object[]) {
    return gateways.map(({name, label}: {name: string; label: string}) => {
        return <Gateway>{
            id: name,
            label,
        };
    });
}
