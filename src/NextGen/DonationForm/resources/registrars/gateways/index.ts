import {Gateway} from '@givewp/forms/types';

const {gatewaySettings} = window.giveNextGenExports;

/**
 * @since 0.1.0
 */
interface GatewayRegistrar {
    register(gateway: Gateway): void;

    getAll(): Gateway[];

    get(id: string): Gateway | undefined;
}

/**
 * @since 0.1.0
 */
export default class Registrar implements GatewayRegistrar {
    /**
     * @since 0.1.0
     */
    private gateways: Gateway[] = [];

    /**
     * @since 0.1.0
     */
    public get(id: string): Gateway | undefined {
        return this.gateways.find((gateway) => gateway.id === id);
    }

    /**
     * @since 0.1.0
     */
    public getAll(): Gateway[] {
        return this.gateways;
    }

    /**
     * @since 0.1.0
     */
    public register(gateway: Gateway): void {
        gateway.settings = gatewaySettings[gateway.id];

        if (gateway.hasOwnProperty('initialize')) {
            try {
                gateway.initialize();
            } catch (e) {
                console.error(`Error initializing ${gateway.id} gateway:`, e);
                // TODO: decide what to do if a gateway fails to initialize
                // we can hide the fields from the list or display an error message.
                // for now we will just display the error message, but in the future
                // it might be better to hide the fields all together by returning early here.
                //return;
            }
        }

        this.gateways.push(gateway);
    }
}
