// Plugins
import 'jquery.payment';
import 'accounting';
import 'uiblocker';
import 'magnific-popup';
import 'iframe-resizer';

// Give plugins.
import '../plugins/give-hint.css';
import { initializeIframeResize } from '../plugins/form-theme/utils';

// Give API.
import GiveAPI from '../plugins/give-api/api';

// Give core.
import './give-donations';
import './give-ajax';
import './give-misc';
import './give-donor-wall';
import iFrameResizer from '../plugins/form-theme/iframe-content';
import '../plugins/form-theme/parent-page';

const { init, fn, form, notice, cache, donor, util } = GiveAPI;
window.Give = { init, fn, form, notice, cache, donor, util, initializeIframeResize };
window.iFrameResizer = iFrameResizer;
