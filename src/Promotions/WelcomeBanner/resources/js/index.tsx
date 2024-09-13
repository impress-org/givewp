import App from './app/app';
import {StrictMode} from 'react';
import {createRoot} from 'react-dom/client';

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

/**
 * @since 3.0.0
 */
const RenderApp = () => (
    <StrictMode>
        <App />
    </StrictMode>
);

if (root) {
    const pluginHeader = document.querySelector('.wp-header-end');
    // Place banner underneath Plugin header
    pluginHeader.insertAdjacentElement('afterend', root);

    createRoot(root).render(<RenderApp />);
}
