import {useEffect, useRef, type ReactNode} from 'react';
import {Button, Dropdown} from '@wordpress/components';
import {closeSmall} from '@wordpress/icons';
import './styles.scss';

/**
 * @since TBD Add `accessibleName`; `fullText` is now only the tooltip.
 * @since TBD
 */
export interface FilterChipProps {
    /** Short text on the chip, like "Status: 2". */
    text: string;
    /** The whole value. It is the tooltip. */
    fullText: string;
    /** The accessible name. It starts with `text`, so the spoken name holds the visible words. */
    accessibleName: string;
    /** Marks a chip that holds a value. */
    hasValue: boolean;
    /** Shows the remove button. */
    canRemove: boolean;
    /** Name of the remove button: "Remove" (secondary) or "Reset" (primary with a value). */
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
 * @since TBD Name the chip by `accessibleName` and keep `fullText` as the tooltip.
 * @since TBD
 */
export default function FilterChip({
    text,
    fullText,
    accessibleName,
    hasValue,
    canRemove,
    removeLabel,
    defaultOpen = false,
    onRemove,
    children,
}: FilterChipProps) {
    const toggleRef = useRef<HTMLButtonElement>(null);

    // The installed Dropdown has no default-open prop in every WordPress version, so open it once on mount.
    useEffect(() => {
        if (defaultOpen) {
            toggleRef.current?.click();
        }
    }, []);

    const className = ['givewp-filter-chip', hasValue && 'givewp-filter-chip--has-value'].filter(Boolean).join(' ');

    return (
        <div className={className}>
            <Dropdown
                className="givewp-filter-chip__dropdown"
                popoverProps={{placement: 'bottom-start'}}
                contentClassName="givewp-filter-chip__content"
                renderToggle={({isOpen, onToggle}) => (
                    <Button
                        ref={toggleRef}
                        className="givewp-filter-chip__toggle"
                        label={fullText}
                        aria-label={accessibleName}
                        showTooltip
                        aria-expanded={isOpen}
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
                    label={removeLabel}
                    onClick={onRemove}
                />
            )}
        </div>
    );
}
