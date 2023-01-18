const state = {};

const gateway = {
    id: 'test-gateway-next-gen-offsite',
    initialize() {
        state.message = this.settings.message;
    },
    Fields() {
        return state.message;
    },
};

window.givewp.gateways.register(gateway);
