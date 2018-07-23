// Plugins
import 'jquery.payment';
import 'accounting';
import 'uiblocker';
import 'magnific-popup';

// Give plugins.
import '../plugins/give-hint.css';

// Give API.
import GiveAPI from '../plugins/give-api/api';
const Give = GiveAPI;

// Give core.
import './give-donations';
import './give-ajax';
import './give-misc';
import './give-donor-wall';

export const { init, fn, form, notice, cache } = Give;
