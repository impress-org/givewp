import { useEffect } from "react";

export function useTriggerResize(listener) {
    useEffect(() => {
        const triggerResize = () => {
            window.dispatchEvent(new Event('resize'));
        };

        requestAnimationFrame(triggerResize);
    }, [listener]);
}
