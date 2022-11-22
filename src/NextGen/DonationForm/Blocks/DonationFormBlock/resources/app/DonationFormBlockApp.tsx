import {render} from '@wordpress/element';
import getDefaultValuesFromSections from './utilities/getDefaultValuesFromSections';
import Form from './form/Form';
import {GiveDonationFormStoreProvider} from './store';
import getWindowData from './utilities/getWindowData';
import prepareFormData from './utilities/PrepareFormData';
import getJoiRulesForForm from './utilities/ConvertFieldAPIRulesToJoi';
import Header from './form/Header';

/**
 * Get data from the server
 */
const {form} = getWindowData();

prepareFormData(form);

/**
 * Prepare default values for form
 */
const defaultValues = getDefaultValuesFromSections(form.nodes);

const schema = getJoiRulesForForm(form);

const initialState = {
    gateways: window.givewp.gateways.getAll(),
};

function App() {
    return (
        <GiveDonationFormStoreProvider initialState={initialState}>
            <>
                <Header />
                <Form defaultValues={defaultValues} sections={form.nodes} validationSchema={schema} />
            </>
        </GiveDonationFormStoreProvider>
    );
}

render(<App />, document.getElementById('root-give-next-gen-donation-form-block'));
