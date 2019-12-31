// Entry point for dashboard widget

// Vendor dependencies
import React from 'react';
import ReactDOM from 'react-dom'

// Reports widget
import Widget from './widget/index.js'

ReactDOM.render(
    <Widget/>,
    document.getElementById('givewp-reports-widget')
);