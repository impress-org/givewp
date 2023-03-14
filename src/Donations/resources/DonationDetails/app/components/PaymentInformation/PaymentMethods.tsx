import styles from './style.module.scss';
import PaypalIcon from '@givewp/components/AdminUI/Icons/PaypalIcon';

interface PaymentMethod {
    gateway: string;
    gatewayId: string;
}

function renderGatewayIcon(gatewayId) {
    switch (gatewayId) {
        case 'paypal':
            return <PaypalIcon />;
        case 'stripe':
            return <span>stripe test</span>;
        case 'test-gateway-next-gen':
            return <span>next gen test</span>;
        case 'offline-donation':
            return <span>offline-donation test</span>;
        default:
            return '';
    }
}

export default function PaymentMethod({gateway, gatewayId}: PaymentMethod) {
    return (
        <div className={styles.paymentMethod}>
            {renderGatewayIcon(gatewayId)}
            {gateway}
        </div>
    );
}
