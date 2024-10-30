/* eslint-disable no-unused-vars */

// Entry point for Onboarind Wizard app

// Vendor dependencies
import React from 'react';
import {createRoot} from 'react-dom/client';

// Onboarding Wizard app
import App from './app/index.js';

// Import styles
import './style.scss';

// Render application
const element = document.getElementById('onboarding-wizard-app');
if (element) {
    createRoot(element).render(<App />);
}
