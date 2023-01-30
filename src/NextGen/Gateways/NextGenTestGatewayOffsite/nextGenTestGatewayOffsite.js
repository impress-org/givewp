(() => {
    "use strict";
    const nextGenTestGatewayOffsite = {
        id: 'test-gateway-next-gen-offsite',
        Fields() {
            return nextGenTestGatewayOffsite.settings.message;
        },
    }

    window.givewp.gateways.register(nextGenTestGatewayOffsite);
})();