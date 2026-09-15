import {useEffect, useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import EmbedSkeleton, {EmbedShape, hasSkeleton} from './EmbedSkeleton';
import './styles.scss';

/**
 * How long the embed waits for the form to announce itself before giving up and
 * offering a link to the standalone form instead. Shared with the external embed
 * script so every embed context degrades on the same schedule.
 *
 * @since TBD
 */
export const LOAD_TIMEOUT_MS = 10000;

/**
 * @since TBD
 */
type EmbedFrameState = 'loading' | 'ready' | 'failed';

/**
 * @since TBD
 */
type EmbedFrameProps = {
    src: string;
    embedId: string;
    fallbackUrl: string;
    /** When given for a design the skeleton knows, a sketch of the form holds the space instead of a spinner. */
    shape?: EmbedShape | null;
    onReady?: () => void;
    onFail?: () => void;
};

/**
 * The iframe every on-site embed renders the form into, wrapped in a loading state.
 *
 * Browsers fire `load` for error pages too, so the signal that the form is actually running is the
 * iframe-resizer handshake (`onInit`). Until it arrives a spinner holds the space; if it never
 * arrives, the frame degrades to a link that opens the form on its own page.
 *
 * @since TBD
 */
export default function EmbedFrame({src, embedId, fallbackUrl, shape, onReady, onFail}: EmbedFrameProps) {
    const [state, setState] = useState<EmbedFrameState>('loading');
    const skeleton = hasSkeleton(shape);

    useEffect(() => {
        if (state !== 'loading') {
            return;
        }

        const timeout = window.setTimeout(() => {
            setState('failed');
            onFail?.();
        }, LOAD_TIMEOUT_MS);

        return () => window.clearTimeout(timeout);
    }, [state]);

    if (state === 'failed') {
        return (
            <a
                className="givewp-donation-form-link givewp-embed-frame__fallback"
                href={fallbackUrl}
                target="_blank"
                rel="noopener noreferrer"
            >
                {__('Open donation form', 'give')}
            </a>
        );
    }

    return (
        <div className="givewp-embed-frame" data-state={state} data-placeholder={skeleton ? 'skeleton' : 'spinner'}>
            {state === 'loading' && (
                <div
                    className="givewp-embed-frame__loading"
                    role="status"
                    aria-label={__('Loading donation form', 'give')}
                >
                    {skeleton ? <EmbedSkeleton shape={shape} /> : <span className="givewp-embed-frame__spinner" />}
                </div>
            )}
            <IframeResizer
                title={__('Donation Form', 'give')}
                id={embedId}
                src={src}
                className="givewp-embed-frame__iframe"
                checkOrigin={false}
                heightCalculationMethod="taggedElement"
                onInit={(iframe) => {
                    iframe.iFrameResizer.resize();
                    setState('ready');
                    onReady?.();
                }}
            />
        </div>
    );
}
