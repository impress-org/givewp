import {Gateway, RegisteredGateway} from '@givewp/forms/types';

const {registeredGateways} = window.givewpDonationFormExports;

/**
 * @since 3.0.0
 */
interface GatewayRegistrar {
    register(gateway: Gateway): void;

    getAll(): Gateway[];

    get(id: string): Gateway | undefined;
}

/**
 * @since 3.0.0
 */
export default class Registrar implements GatewayRegistrar {
    /**
     * @since 3.0.0
     */
    private gateways: Gateway[] = [];

    /**
     * @since 3.0.0
     */
    public get(id: string): Gateway | undefined {
        return this.gateways.find((gateway) => gateway.id === id);
    }

    /**
     * @since 3.0.0
     */
    public getAll(): Gateway[] {
        return this.gateways;
    }

    /**
     * @since 3.0.0
     */
    public register(gateway: Gateway): void {
        const registeredGateway = registeredGateways?.find(({id}) => id === gateway.id);

        this.mapRegisteredGatewayToClientGateway(registeredGateway, gateway);

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

    /**
     * @since 3.0.0
     */
    private mapRegisteredGatewayToClientGateway(registeredGateway: RegisteredGateway, clientGateway: Gateway): void {
        for (const [key, value] of Object.entries(registeredGateway)) {
            clientGateway[key] = value;
        }
    }
}
