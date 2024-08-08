// Entry point for Donor Profile app

// Vendor dependencies
import {HashRouter as Router} from 'react-router-dom';
import {createRoot} from 'react-dom/client';
import React from 'react';
import {Provider} from 'react-redux';

import {library} from '@fortawesome/fontawesome-svg-core';
import {fas} from '@fortawesome/free-solid-svg-icons';

window.React = React;

library.add(fas);

// Store dependencies
import {store} from './store';

// Internal dependencies
import {registerDefaultTabs} from './tabs';
import {registerTab} from './utils';

// DonorDashboards app
import App from './components/app';

import './style.scss';

window.giveDonorDashboard = {
    store,
    utils: {
        registerTab,
    },
};

/**
 * @since 2.11.1 Load after DOM Content is loaded so that wp.i18n available to parse translatable strings.
 * @link https://github.com/impress-org/givewp/pull/5842
 */
window.addEventListener('DOMContentLoaded', (event) => {
    registerDefaultTabs();

    const root = createRoot(document.getElementById('give-donor-dashboard'));

    root.render(
        <Provider store={store}>
            <Router>
                <App />
            </Router>
        </Provider>,
    );
});
