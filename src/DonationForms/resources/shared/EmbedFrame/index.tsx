import {useEffect, useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import {Interweave} from 'interweave';
import './styles.scss';

/**
 * How long the embed waits for the form to announce itself before it starts offering a link to
 * the standalone form alongside the placeholder. The iframe keeps loading either way.
 *
 * @since 4.17.0
 */
export const LOAD_TIMEOUT_MS = 10000;

/**
 * @since 4.17.0
 */
type EmbedFrameState = 'loading' | 'slow' | 'ready';

/**
 * @since 4.17.0
 */
type EmbedFrameProps = {
    src: string;
    embedId: string;
    fallbackUrl: string;
    /**
     * The skeleton RenderFormSkeleton printed on the server, with its inline styles. When present a
     * sketch of the form holds the space instead of a spinner.
     */
    skeletonHtml?: string;
    onReady?: () => void;
    onSlow?: () => void;
};

/**
 * The iframe every on-site embed renders the form into, wrapped in a loading state.
 *
 * Browsers fire `load` for error pages too, so the signal that the form is actually running is the
 * iframe-resizer handshake (`onInit`). Until it arrives a placeholder holds the space. There is no
 * reliable failure signal, so a slow handshake is never treated as one: the iframe stays mounted and
 * after LOAD_TIMEOUT_MS a link to the standalone form fades in over the placeholder, in case the
 * form never arrives. It overlays the top of the frame rather than sitting under it, because a
 * skeleton can run past the fold.
 *
 * @since 4.17.0
 */
export default function EmbedFrame({src, embedId, fallbackUrl, skeletonHtml, onReady, onSlow}: EmbedFrameProps) {
    const [state, setState] = useState<EmbedFrameState>('loading');
    const showSkeleton = Boolean(skeletonHtml);

    useEffect(() => {
        if (state !== 'loading') {
            return;
        }

        const timeout = window.setTimeout(() => {
            setState('slow');
            onSlow?.();
        }, LOAD_TIMEOUT_MS);

        return () => window.clearTimeout(timeout);
    }, [state]);

    return (
        <div className="givewp-embed-frame" data-state={state} data-placeholder={showSkeleton ? 'skeleton' : 'spinner'}>
            {state !== 'ready' && (
                <div
                    className="givewp-embed-frame__loading"
                    role="status"
                    aria-label={__('Loading donation form', 'give')}
                >
                    {showSkeleton ? (
                        /*
                         * The server's markup, read back from the root it was printed into. Interweave
                         * drops the inline style element that came with it; by now the block stylesheet,
                         * which carries the same rules, has loaded.
                         */
                        <Interweave content={skeletonHtml} noWrap disableLineBreaks />
                    ) : (
                        <span className="givewp-embed-frame__spinner" />
                    )}
                </div>
            )}
            {state === 'slow' && (
                <p className="givewp-embed-frame__fallback" role="status">
                    {__('The form is taking longer than usual to load.', 'give')}
                    <a
                        className="givewp-donation-form-link"
                        href={fallbackUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {__('Open the form on its own page', 'give')}
                    </a>
                </p>
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
