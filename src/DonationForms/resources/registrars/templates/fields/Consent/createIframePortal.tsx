import {createPortal, render} from 'react-dom';
import {useEffect, useRef} from 'react';

import './styles.scss';

/**
 * @since 3.14.0
 * Creates a portal to the Top Level document, rendering children elements within an iframe.
 */
export default function createIframePortal(children, targetElement = window.top.document.body) {
    const iframeRef = useRef<HTMLIFrameElement | null>(null);

    useEffect(() => {
        const iframe = iframeRef.current;
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

        if (iframe) {
            // Clear existing content.
            iframeDoc.head.innerHTML = '';
            iframeDoc.body.innerHTML = '';

            async function renderContent() {
                try {
                    await fetchStylesheets(iframeDoc);
                    render(children, iframeDoc.body);
                } catch (error) {
                    console.error('Error loading stylesheets:', error);
                }
            }

            renderContent();
        }
    }, []);

    return createPortal(
        <iframe
            ref={iframeRef}
            id={'givewp-fields-consent-iframe-portal'}
            style={{
                position: 'fixed',
                border: 'none',
                top: '0',
                left: '0',
                minHeight: '100%',
                minWidth: '100%',
                // Required to be visible in the Visual Form Builder.
                zIndex: '9999999999',
            }}
        />,
        targetElement
    );
}

/**
 * @since 3.14.0
 * Fetches stylesheets from the originating document and injects them into the new iframe document's head.
 * This allows user provided styles to be applied to the iframe content.
 * Returns a promise that resolves when all stylesheets are loaded.
 */
export async function fetchStylesheets(iframeDoc: Document) {
    const styleSheets = Array.from(document.styleSheets);

    // Promisify the loading of each stylesheet
    const loadStylesheet = (styleSheet) => {
        return new Promise<void>((resolve, reject) => {
            try {
                if (styleSheet.href) {
                    // For external stylesheets
                    const newLink = document.createElement('link');
                    newLink.rel = 'stylesheet';
                    newLink.href = styleSheet.href;
                    newLink.onload = () => resolve();
                    newLink.onerror = reject;
                    iframeDoc.head.appendChild(newLink);
                } else if (styleSheet.cssRules) {
                    // For <style/> tags
                    const newStyle = document.createElement('style');
                    Array.from(styleSheet.cssRules).forEach((rule: {cssText: string}) => {
                        newStyle.appendChild(document.createTextNode(rule.cssText));
                    });
                    iframeDoc.head.appendChild(newStyle);
                    resolve();
                }
            } catch (error) {
                reject(error);
            }
        });
    };

    const promises = styleSheets.map(loadStylesheet);
    return await Promise.all(promises);
}
