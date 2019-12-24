import App from './app/index.js'
import { HashRouter as Router } from "react-router-dom";
import React from 'react';
import ReactDOM from 'react-dom'


ReactDOM.render(
    <Router>
        <App/>
    </Router>,
    document.getElementById('reports-app')
);