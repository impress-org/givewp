import {useEffect} from "react";

export default function useIframeMessages() {

    const events = {};

    useEffect(() => {
        addEventListener('message', eventListener, false);
        return () => removeEventListener('message', eventListener);
    }, []);

    const eventListener = (e) => {
        const {event, data} = e.data;

        if (events[event]) {
            events[event].forEach(callback => {
                callback(data);
            });
        }
    }

    const subscribe = (event, callback) => {
        events[event] = events[event] || [];
        events[event].push(callback);
    }

    const sendToParent = (event, data) => {
        window.parent.postMessage({
            event,
            data
        });
    }

    const sendToIframe = (iframeId, event, data) => {
        // todo: use refs?
        const iframe = document.getElementById(iframeId)?.contentWindow;

        iframe?.postMessage({
            event,
            data
        });
    }

    return {
        subscribe,
        sendToParent,
        sendToIframe
    }
}






