(() => {
    'use strict';
    const testOffsiteGateway = {
        id: 'test-gateway-offsite',
        Fields() {
            return givewpTestGatewayOffsiteData.message;
        },
    };

    window.givewp.gateways.register(testOffsiteGateway);
})();
