// Plugins
import 'jquery.payment';
import 'accounting';
import 'uiblocker';
import 'magnific-popup';
import 'iframe-resizer';

// Give plugins.
import '../plugins/give-hint.css';

// Give API.
import GiveAPI from '../plugins/give-api/api';

// Give core.
import './give-donations';
import './give-ajax';
import './give-misc';
import './give-donor-wall';
import './give-embed-form';

const { init, fn, form, notice, cache, donor, util } = GiveAPI;
window.Give = { init, fn, form, notice, cache, donor, util };
