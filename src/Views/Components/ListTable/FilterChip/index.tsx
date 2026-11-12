import {useEffect, useRef, type ReactNode} from 'react';
import {Button, Dropdown} from '@wordpress/components';
import {__, sprintf} from '@wordpress/i18n';
import {closeSmall} from '@wordpress/icons';
import './styles.scss';

/**
 * @since TBD Drop `fullText`: the tooltip shows `accessibleName`.
 * @since TBD Add `accessibleName`; `fullText` is now only the tooltip.
 * @since TBD
 */
export interface FilterChipProps {
    /** Short text on the chip, like "Status: 2". */
    text: string;
    /**
     * The accessible name and the tooltip. It starts with `text`, so the spoken name holds the visible
     * words. One text for both keeps newer Tooltips from reading it again as a description.
     */
    accessibleName: string;
    /** Marks a chip that holds a value. */
    hasValue: boolean;
    /** Shows the remove button. */
    canRemove: boolean;
    /** The first word of the remove button's name: "Remove" (secondary) or "Reset" (primary with a value). */
    removeLabel: string;
    /** Opens the popover on mount. */
    defaultOpen?: boolean;
    onRemove: () => void;
    /** The popover content. `close` closes the popover. */
    children: (close: () => void) => ReactNode;
}

/**
 * A filter chip: a button that opens a popover with the filter control, and an optional button that
 * removes or resets the filter. Escape closes the popover and puts focus back on the chip.
 *
 * @since TBD Name the chip and its remove button by `accessibleName`, which is also the tooltip.
 */
export default function FilterChip({
    text,
    accessibleName,
    hasValue,
    canRemove,
    removeLabel,
    defaultOpen = false,
    onRemove,
    children,
}: FilterChipProps) {
    const toggleRef = useRef<HTMLButtonElement>(null);
    const opened = useRef(false);

    // The installed Dropdown has no default-open prop in every WordPress version, so open it once on mount.
    // The ref keeps a second run of the effect (React StrictMode in development) from closing it again.
    useEffect(() => {
        if (defaultOpen && !opened.current) {
            opened.current = true;
            toggleRef.current?.click();
        }
    }, []);

    const className = ['givewp-filter-chip', hasValue && 'givewp-filter-chip--has-value'].filter(Boolean).join(' ');

    return (
        <div className={className}>
            <Dropdown
                className="givewp-filter-chip__dropdown"
                popoverProps={{placement: 'bottom-start', role: 'dialog', 'aria-label': accessibleName}}
                contentClassName="givewp-filter-chip__content"
                renderToggle={({isOpen, onToggle}) => (
                    <Button
                        ref={toggleRef}
                        className="givewp-filter-chip__toggle"
                        label={accessibleName}
                        showTooltip
                        aria-expanded={isOpen}
                        aria-haspopup="dialog"
                        onClick={onToggle}
                    >
                        {text}
                    </Button>
                )}
                renderContent={({onClose}) => children(onClose)}
            />
            {canRemove && (
                <Button
                    className="givewp-filter-chip__remove"
                    icon={closeSmall}
                    label={sprintf(
                        /* translators: 1: "Remove" or "Reset", 2: the filter and its value (for example "Status: Failed") */
                        __('%1$s filter: %2$s', 'give'),
                        removeLabel,
                        accessibleName
                    )}
                    onClick={onRemove}
                />
            )}
        </div>
    );
}
