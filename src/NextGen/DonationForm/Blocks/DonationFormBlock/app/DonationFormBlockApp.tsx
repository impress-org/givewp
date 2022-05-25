import {render} from '@wordpress/element';
import getDefaultValuesFromFieldsCollection from './utilities/getDefaultValuesFromFieldsCollection';
import Form from './form/Form';
import {GiveDonationFormStoreProvider} from './store';
import getWindowData from './utilities/getWindowData';
import type {giveNextGenExports} from './types/giveNextGenExports';
import type {Gateway} from "./types/Gateway";

declare global {
    interface Window {
        giveNextGenExports: giveNextGenExports;
        givewp: {
            gateways: {
                getAll(): Gateway[]
                register(gateway: object): void
            },
        }
    }
}

/**
 * Get data from the server
 */
const {attributes, form} = getWindowData();

/**
 * Prepare default values for form
 */
const defaultValues = getDefaultValuesFromFieldsCollection(form.nodes);
//const gateways = getPaymentGateways(form.nodes.find(({name}) => name === 'paymentDetails').nodes);

const initialState = {
    gateways: window.givewp.gateways.getAll()
}

function App() {
    return (
        <GiveDonationFormStoreProvider initialState={initialState}>
            <Form fields={form.nodes} defaultValues={defaultValues}/>
        </GiveDonationFormStoreProvider>
    );
}

render(<App/>, document.getElementById('root-give-next-gen-donation-form-block'));
