// Plugins
import 'jquery.payment';
import 'accounting';
import 'uiblocker';
import 'magnific-popup';
import 'iframe-resizer';

// Give plugins.
import '../plugins/give-hint.css';
import { initializeIframeResize } from '../plugins/form-template/utils';

// Give API.
import GiveAPI from '../plugins/give-api/api';

// Give core.
import './give-donations';
import './give-ajax';
import './give-misc';
import './give-donor-wall';
import iFrameResizer from '../plugins/form-template/iframe-content';
import '../plugins/form-template/parent-page';

const { init, fn, form, notice, cache, donor, util } = GiveAPI;
window.Give = { init, fn, form, notice, cache, donor, util, initializeIframeResize };
window.iFrameResizer = iFrameResizer;
