import ReactDOM from 'react-dom';
import getWindowData from './utilities/getWindowData';
import getDefaultValuesFromFieldsCollection from './utilities/getDefaultValuesFromFieldsCollection';
import Form from './form/Form';

/**
 * Get data from the server
 */
const [attributes, form] = getWindowData('attributes', 'form');

/**
 * Prepare default values for form
 */
const defaultValues = getDefaultValuesFromFieldsCollection(form.nodes);

/**
 * @unreleased
 *
 * @returns {JSX.Element}
 * @constructor
 */
function App() {
    return <Form fields={form.nodes} defaultValues={defaultValues} />;
}

ReactDOM.render(<App />, document.getElementById('root-give-next-gen-donation-form-block'));
