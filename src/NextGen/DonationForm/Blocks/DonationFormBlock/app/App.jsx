import ReactDOM from 'react-dom';
import getWindowData from './utilities/getWindowData';

const [{formId}] = getWindowData('attributes');

export default function App() {
    return <p>FormId: {formId}</p>;
}

ReactDOM.render(<App />, document.getElementById('root-give-next-gen-donation-form-block'));
