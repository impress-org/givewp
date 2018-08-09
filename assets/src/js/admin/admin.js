// Plugins
import 'chosen-js';
import 'accounting';
import 'flot';
import 'flot-orderbars/js/jquery.flot.orderBars';
import 'flot/jquery.flot.time';

// Give plugins.
import '../plugins/give-ajaxify-fields';
import '../plugins/selector-cache';
import '../plugins/repeatable-fields';
import '../plugins/give-hint.css';
import GiveAPI from '../plugins/give-api/api';

import * as Modals from '../plugins/modal.js';

// Give core.
import './admin-forms';
import './admin-settings';
import './admin-export';
import './admin-widgets';
import './admin-scripts';
import './admin-importer';
import './shortcode-button';

GiveAPI.modal = Modals;
export const { init, fn, cache, modal } = GiveAPI;
