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
 * stays open while the pointer or the focus is on the button, or the pointer is over the text, and closes on Esc
 * (WCAG 1.4.13: dismissible, hoverable, persistent).
 *
 * @since TBD
 */
export default function HelpTip({label, text}: HelpTipProps) {
    const tipId = useId();
    const [anchor, setAnchor] = useState<HTMLButtonElement | null>(null);
    const [isHovered, setIsHovered] = useState(false);
    const [isFocused, setIsFocused] = useState(false);
    const [isDismissed, setIsDismissed] = useState(false);
    const closeTimer = useRef<number>();

    // Open while the pointer or the focus is on the button, or the pointer is on the text. Esc hides it until
    // the next hover or focus.
    const isOpen = (isHovered || isFocused) && !isDismissed;

    const clearCloseTimer = () => window.clearTimeout(closeTimer.current);

    const hover = () => {
        clearCloseTimer();
        setIsDismissed(false);
        setIsHovered(true);
    };

    const leave = () => {
        clearCloseTimer();
        closeTimer.current = window.setTimeout(() => setIsHovered(false), CLOSE_DELAY_MS);
    };

    const focus = () => {
        setIsDismissed(false);
        setIsFocused(true);
    };

    useEffect(() => clearCloseTimer, []);

    // Esc closes the text wherever the focus is, so a hover-only reader can dismiss it too.
    useEffect(() => {
        if (!isOpen) {
            return;
        }

        const closeOnEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                // Clear hover too: the text unmounts without a `mouseleave`, so it would stay set.
                clearCloseTimer();
                setIsHovered(false);
                setIsDismissed(true);
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
                onFocus={focus}
                onBlur={() => setIsFocused(false)}
                onMouseEnter={hover}
                onMouseLeave={leave}
            />
            {isOpen && (
                <Popover
                    className="givewp-help-tip"
                    anchor={anchor}
                    placement="bottom-start"
                    focusOnMount={false}
                    resize={false}
                    noArrow
                >
                    <div
                        id={tipId}
                        role="tooltip"
                        className="givewp-help-tip__content"
                        onMouseEnter={hover}
                        onMouseLeave={leave}
                    >
                        {text}
                    </div>
                </Popover>
            )}
        </>
    );
}
