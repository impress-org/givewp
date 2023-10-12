import type {Gateway} from '@givewp/forms/types';
import {Markup} from "interweave";

let settings: { markup: string };
const gateway: Gateway = {
    id: 'offline',
    initialize() {
        settings = this.settings;
    },
    Fields() {
        return <Markup content={settings.markup}/>;
    }
}

window.givewp.gateways.register(gateway);
