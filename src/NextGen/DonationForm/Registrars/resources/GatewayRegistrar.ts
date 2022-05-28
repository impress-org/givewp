import {Gateway, GatewaySettings} from '@givewp/forms/types';

interface GatewayRegistrar {
    register(gateway: Gateway): void;
    getAll(): Gateway[];
    get(id: string): Gateway | undefined;
}

const {gatewaySettings} = window.giveNextGenExports;

export default class Registrar implements GatewayRegistrar {
    private gateways: Gateway[] = [];

    public get(id: string): Gateway | undefined {
        return this.gateways.find((gateway) => gateway.id === id);
    }

    public getAll(): Gateway[] {
        return this.gateways;
    }

    public register(gateway: Gateway): void {
        const settings: GatewaySettings = gatewaySettings[gateway.id];
        gateway.settings = settings;

        if (gateway.initialize) {
            gateway.initialize();
        }

        this.gateways.push(gateway);
    }
}
