import {render} from 'react-dom';
import {AddonsAdminPage} from './components/AddonsAdminPage';

const searchParams = new URLSearchParams(window.location.search);
const startingTab = parseInt(searchParams.get('tab')) || 0;

render(<AddonsAdminPage startingTab={startingTab} />, document.getElementById(window.GiveAddons.containerId));
