import {useEffect, useRef, useState} from 'react';
import {createPortal} from 'react-dom';
import {__} from '@wordpress/i18n';
import {speak} from '@wordpress/a11y';
import {Button} from '@wordpress/components';
import './styles.scss';

/**
 * @since TBD
 */
export interface ListTableViewResetProps {
    /** True while the table shows the screen's default layout. The button is disabled then. */
    isDefault: boolean;
    /** Puts the layout back to the defaults. */
    onReset: () => void;
}

/**
 * Stand-in for the `onReset` button of newer DataViews (remove after the DataViews bump, see
 * "Stand-ins for newer DataViews" in docs/architecture/list-tables.md). DataViews 10.1.7 has no way to
 * add a button to the View options popover, so this finds the stock "View options" button in the same
 * toolbar, waits until it is open, and puts a stock "Reset view" button into the popover's first
 * heading ("Appearance"). If the stock markup changes, the button is missing; nothing else breaks.
 * The button is disabled, not hidden, while the view is the default, as in newer DataViews.
 *
 * @since TBD
 */
export default function ListTableViewReset({isDefault, onReset}: ListTableViewResetProps) {
    const anchorRef = useRef<HTMLSpanElement>(null);
    const [slot, setSlot] = useState<HTMLElement | null>(null);

    useEffect(() => {
        const toggle = anchorRef.current?.parentElement?.querySelector<HTMLElement>(
            'button[aria-controls^="dataviews-view-config-dropdown"]'
        );
        if (!toggle) {
            return undefined;
        }

        const container = document.createElement('div');
        container.className = 'givewp-view-config-reset';
        let frame = 0;
        let tries = 0;

        const remove = () => {
            cancelAnimationFrame(frame);
            container.remove();
            setSlot(null);
        };

        // The popover's content can show a frame after the toggle says it is open, so look again until it is there.
        const place = () => {
            const sidebar = document
                .getElementById(toggle.getAttribute('aria-controls') ?? '')
                ?.querySelector('.dataviews-settings-section__sidebar');
            if (!sidebar) {
                // Give up after about 30 frames, so a renamed class cannot keep this looping.
                if (tries++ < 30) {
                    frame = requestAnimationFrame(place);
                }
                return;
            }
            sidebar.appendChild(container);
            setSlot(container);
        };

        const sync = () => {
            remove();
            tries = 0;
            if (toggle.getAttribute('aria-expanded') === 'true') {
                place();
            }
        };

        const observer = new MutationObserver(sync);
        observer.observe(toggle, {attributes: true, attributeFilter: ['aria-expanded']});
        sync();

        return () => {
            observer.disconnect();
            remove();
        };
    }, []);

    const reset = () => {
        onReset();
        speak(__('The table view is back to its defaults.', 'give'), 'polite');
    };

    return (
        <>
            <span ref={anchorRef} hidden />
            {slot &&
                createPortal(
                    <Button
                        // 40 px, as the controls in the popover. The installed types do not list this prop.
                        {...{__next40pxDefaultSize: true}}
                        variant="secondary"
                        disabled={isDefault}
                        __experimentalIsFocusable
                        onClick={isDefault ? undefined : reset}
                    >
                        {__('Reset view', 'give')}
                    </Button>,
                    slot
                )}
        </>
    );
}
