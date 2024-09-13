// Entry point for Reports page app

// Vendor dependencies
import { HashRouter as Router } from 'react-router-dom';
import React from 'react';
import {createRoot} from 'react-dom/client';

// Reports app
import App from './app/index.js';

const element = document.getElementById('reports-app');
if (element) {
    createRoot(element).render(
        <Router>
            <App />
        </Router>
    );
}
