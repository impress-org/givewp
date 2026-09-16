import {useEffect, useState} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import IframeResizer from 'iframe-resizer-react';
import EmbedSkeleton, {SkeletonData, hasSkeleton} from './EmbedSkeleton';
import './styles.scss';

/**
 * How long the embed waits for the form to announce itself before it starts offering a link to
 * the standalone form alongside the placeholder. The iframe keeps loading either way.
 *
 * @since TBD
 */
export const LOAD_TIMEOUT_MS = 10000;

/**
 * @since TBD
 */
type EmbedFrameState = 'loading' | 'slow' | 'ready';

/**
 * @since TBD
 */
type EmbedFrameProps = {
    src: string;
    embedId: string;
    fallbackUrl: string;
    /** When given for a design the skeleton knows, a sketch of the form holds the space instead of a spinner. */
    skeleton?: SkeletonData | null;
    onReady?: () => void;
    onSlow?: () => void;
};

/**
 * The iframe every on-site embed renders the form into, wrapped in a loading state.
 *
 * Browsers fire `load` for error pages too, so the signal that the form is actually running is the
 * iframe-resizer handshake (`onInit`). Until it arrives a placeholder holds the space. There is no
 * reliable failure signal, so a slow handshake is never treated as one: the iframe stays mounted and
 * after LOAD_TIMEOUT_MS a link to the standalone form fades in under the placeholder, in case the
 * form never arrives.
 *
 * @since TBD
 */
export default function EmbedFrame({src, embedId, fallbackUrl, skeleton, onReady, onSlow}: EmbedFrameProps) {
    const [state, setState] = useState<EmbedFrameState>('loading');
    const showSkeleton = hasSkeleton(skeleton);

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
                        <EmbedSkeleton data={skeleton} />
                    ) : (
                        <span className="givewp-embed-frame__spinner" />
                    )}
                </div>
            )}
            {state === 'slow' && (
                <p className="givewp-embed-frame__fallback">
                    {__('The form is taking longer than usual to load.', 'give')}{' '}
                    <a
                        className="givewp-donation-form-link"
                        href={fallbackUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {__('Open donation form', 'give')}
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
