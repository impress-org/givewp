import {useEffect, useId, useRef, useState} from 'react';
import {Button, Popover} from '@wordpress/components';
import {help} from '@wordpress/icons';
import './styles.scss';

/**
 * @since TBD
 */
export interface HelpTipProps {
    /** Names the button, since it is only an icon. */
    label: string;
    /** The help text shown in the popover. */
    text: string;
}

// Long enough for the pointer to cross the gap between the button and the popover.
const CLOSE_DELAY_MS = 150;

/**
 * An icon button that shows a help text in a popover. The text opens on hover and on keyboard focus,
 * stays open while the pointer is over the button or the text, and closes on Esc or blur
 * (WCAG 1.4.13: dismissible, hoverable, persistent).
 *
 * @since TBD
 */
export default function HelpTip({label, text}: HelpTipProps) {
    const tipId = useId();
    const [anchor, setAnchor] = useState<HTMLButtonElement | null>(null);
    const [isOpen, setIsOpen] = useState(false);
    const closeTimer = useRef<number>();

    const clearCloseTimer = () => window.clearTimeout(closeTimer.current);

    const open = () => {
        clearCloseTimer();
        setIsOpen(true);
    };

    const close = () => {
        clearCloseTimer();
        setIsOpen(false);
    };

    const closeSoon = () => {
        clearCloseTimer();
        closeTimer.current = window.setTimeout(() => setIsOpen(false), CLOSE_DELAY_MS);
    };

    useEffect(() => clearCloseTimer, []);

    // Esc closes the text wherever the focus is, so a hover-only reader can dismiss it too.
    useEffect(() => {
        if (!isOpen) {
            return;
        }

        const closeOnEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                close();
            }
        };

        document.addEventListener('keydown', closeOnEscape);

        return () => document.removeEventListener('keydown', closeOnEscape);
    }, [isOpen]);

    return (
        <>
            <Button
                ref={setAnchor}
                className="givewp-help-tip__button"
                icon={help}
                aria-label={label}
                aria-expanded={isOpen}
                aria-describedby={isOpen ? tipId : undefined}
                onFocus={open}
                onBlur={close}
                onMouseEnter={open}
                onMouseLeave={closeSoon}
            />
            {isOpen && (
                <Popover
                    className="givewp-help-tip"
                    anchor={anchor}
                    placement="bottom-start"
                    focusOnMount={false}
                    noArrow
                >
                    <div
                        id={tipId}
                        role="tooltip"
                        className="givewp-help-tip__content"
                        onMouseEnter={open}
                        onMouseLeave={closeSoon}
                    >
                        {text}
                    </div>
                </Popover>
            )}
        </>
    );
}
