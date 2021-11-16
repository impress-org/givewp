// Plugins
import 'chosen-js';
import 'flot';
import 'flot-orderbars/js/jquery.flot.orderBars';
import 'flot/jquery.flot.time';

// Give plugins.
import '../plugins/give-ajaxify-fields';
import '../plugins/selector-cache';
import '../plugins/repeatable-fields';
import '../plugins/give-hint.css';
import '../plugins/notice';
import GiveAPI from '../plugins/give-api/api';
import { initializeIframeResize } from '../plugins/form-template/utils';

import * as Modals from '../plugins/modal.js';

// Give core.
import './admin-migrations';
import './admin-forms';
import './admin-settings';
import './admin-export';
import './admin-scripts';
import './admin-importer';
import './shortcode-button';

// Form template.
import './form-template/edit-form';

// Stripe core.
import './stripe-admin';

// PayPal donations
import './paypal-commerce';

import '../../../../src/DonorDashboards/resources/js/admin';

GiveAPI.modal = Modals;
const { init, fn, cache, modal, notice } = GiveAPI;
window.Give = { init, fn, cache, modal, notice, initializeIframeResize };
