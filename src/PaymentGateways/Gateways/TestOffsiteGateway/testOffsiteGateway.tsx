import {Gateway} from '@givewp/forms/types';

interface TestOffsiteGateway extends Gateway {
    settings?: {
        label: string;
        message: string;
    };
}

/**
 * @since 3.0.0
 */
const testOffsiteGateway: TestOffsiteGateway = {
    id: 'test-offsite-gateway',
    Fields() {
        return <span>{testOffsiteGateway.settings.message}</span>;
    }
};

window.givewp.gateways.register(testOffsiteGateway);
