import App from './app/index.js'
import { HashRouter as Router } from "react-router-dom";

wp.element.render(
    <Router>
        <App />
    </Router>,
    document.getElementById('reports-app')
);