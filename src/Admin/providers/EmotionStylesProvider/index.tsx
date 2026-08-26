import {useMemo, useRef, useState, useEffect, ReactNode} from 'react';
import {CacheProvider} from '@emotion/react';
import createCache from '@emotion/cache';

/**
 * Custom Cache Provider for react-select in WordPress 6.9+ Iframe Context
 *
 * In WordPress 6.9+, block editor content renders inside an iframe.
 * This component provides an Emotion cache configured to inject styles
 * into the correct document (the iframe's head instead of the parent's).
 *
 * This is similar to react-select's NonceProvider but adds the crucial
 * `container` option to target the iframe's document.
 */

/**
 * @since 4.13.2
 */
type EmotionStylesProviderProps = {
    children: ReactNode;
    cacheKey?: string;
};

/**
 * Provides an Emotion cache that injects styles into the current document.
 * This is essential for WordPress 6.9+ where blocks render in an iframe.
 * @since 4.13.2
 */
export default function EmotionStylesProvider({children, cacheKey = 'givewp'}: EmotionStylesProviderProps) {
    const containerRef = useRef<HTMLDivElement>(null);
    const [container, setContainer] = useState<HTMLElement | null>(null);

    // Get the correct document head after mount
    useEffect(() => {
        if (containerRef.current) {
            setContainer(containerRef.current.ownerDocument.head);
        }
    }, []);

    // Create cache with the container option to target the correct document
    const emotionCache = useMemo(() => {
        if (!container) {
            return null;
        }
        return createCache({
            key: cacheKey,
            container: container,
        });
    }, [cacheKey, container]);

    // Render a wrapper div to get the document reference
    // Use display:contents so it doesn't affect layout
    // IMPORTANT: Don't render children until cache is ready to ensure
    // Emotion styles are injected into the correct document from the start
    return (
        <div ref={containerRef} style={{display: 'contents'}}>
            {emotionCache && (
                <CacheProvider value={emotionCache}>
                    {children}
                </CacheProvider>
            )}
        </div>
    );
}
