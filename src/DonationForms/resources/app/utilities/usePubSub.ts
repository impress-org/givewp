import {RefObject, useEffect} from 'react';

/**
 * @unreleased
 */
export default function usePubSub() {

    const events = {};

    useEffect(() => {
        addEventListener('message', eventListener, false);
        return () => removeEventListener('message', eventListener);
    }, []);

    const getMessage = (message: MessageEvent) => {
        // handle IframeResizer messages
        if (typeof message.data === 'string') {
            if (message.data.includes('[iFrameSizer]message:')) {
                return JSON.parse(message.data.replace('[iFrameSizer]message:', ''));
            }
        }

        return message.data
    }

    const eventListener = (message: MessageEvent) => {
        const {event, data} = getMessage(message);

        if (events[event]) {
            events[event].forEach((callback: (data: any) => void) => {
                callback(data);
            });
        }
    }

    const subscribe = (event: string, callback: Function) => {
        if (!events[event]) {
            events[event] = [];
        }

        events[event].push(callback);
    }

    const publish = (event: string, data: any, iframeRef?: RefObject<any>) => {
        const message = {
            event,
            data
        }

        if (iframeRef) {
            // IframeResizer
            if (iframeRef.current?.sendMessage) {
                iframeRef.current.sendMessage(message);
            } else {
                iframeRef.current?.contentWindow?.postMessage(message);
            }
        } else {
            postMessage(message);
        }
    }

    return {
        publish,
        subscribe
    }
}
