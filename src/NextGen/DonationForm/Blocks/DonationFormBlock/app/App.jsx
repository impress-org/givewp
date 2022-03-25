import ReactDOM from 'react-dom';
import getWindowData from './utilities/getWindowData';

const [attributes] = getWindowData('attributes');

export default function App() {
    return <p>FormId</p>;
}

ReactDOM.render(<App />, document.getElementById('root-give-next-gen-donation-form-block'));
