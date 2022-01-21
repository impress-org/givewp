import {useCallback, useEffect, useRef, useState} from 'react';
import type {MouseEventHandler} from 'react';

export enum CopyTextStatus {
    Idle,
    Copied,
    Error,
}

export type CopyText = {
    // The copy text event handler function.
    handleCopyText: MouseEventHandler;
    // Is the functionality supported by the browser?
    isSupported: boolean;
    // The status of the result of event handler
    status: CopyTextStatus;
};

/**
 * A progressively enhanced hook for copying text
 */
export default function useCopyText(text: string, resetStatusDelay: number = 2000): CopyText {
    const copiedTimeoutId = useRef<number>(null);
    const [status, setStatus] = useState<CopyTextStatus>(CopyTextStatus.Idle);
    const handleCopyText = useCallback(async () => {
        try {
            // Asynchronously writes text to the clipboard
            await navigator.clipboard.writeText(text);

            setStatus(CopyTextStatus.Copied);
        } catch {
            setStatus(CopyTextStatus.Error);
        }
    }, [text]);

    useEffect(() => {
        // Set a window timeout for resetting the status when copied
        if (status === CopyTextStatus.Copied) {
            copiedTimeoutId.current = window.setTimeout(() => {
                setStatus(CopyTextStatus.Idle);
            }, resetStatusDelay);
        }

        return () => {
            // Clear the window timeout when unmounting
            if (copiedTimeoutId.current) {
                window.clearTimeout(copiedTimeoutId.current);
            }
        };
    }, [copiedTimeoutId, status, resetStatusDelay]);

    return {
        handleCopyText,
        isSupported: 'clipboard' in navigator,
        status,
    };
}
