import ReactDOM from 'react-dom';
import getWindowData from './utilities/getWindowData';
import getDefaultValuesFromFieldsCollection from './utilities/getDefaultValuesFromFieldsCollection';
import Form from './form/Form';
import getPaymentGateways from './utilities/getPaymentGateways';

/**
 * Get data from the server
 */
const [attributes, form] = getWindowData('attributes', 'form');

/**
 * Prepare default values for form
 */
const defaultValues = getDefaultValuesFromFieldsCollection(form.nodes);
const gateways = getPaymentGateways(form.nodes.find(({name}) => name === 'paymentDetails').nodes);

function App() {
    return <Form fields={form.nodes} defaultValues={defaultValues} gateways={gateways} />;
}

ReactDOM.render(<App />, document.getElementById('root-give-next-gen-donation-form-block'));
