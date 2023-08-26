import App from './app/app';
import {StrictMode} from 'react';
import ReactDOM from 'react-dom';

type windowData = {
    assets: string;
    action: string;
    nonce: string;
    root: string;
};

declare const window: {
    WelcomeBanner: windowData;
} & Window;

export default function getWindowData(): windowData {
    return window.WelcomeBanner;
}

const root = document.getElementById('givewp-welcome-banner');

const RenderApp = () => (
    <StrictMode>
        <App />
    </StrictMode>
);

if (root) {
    const pluginHeader = document.querySelector('.wp-header-end');
    pluginHeader.insertAdjacentElement('afterend', root);

    ReactDOM.render(<RenderApp />, root);
}
