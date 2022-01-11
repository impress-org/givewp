import {Ref, useCallback, useEffect, useRef} from 'react';

/**
 * Let a checkbox control the selection of other items (checkboxes).
 *
 * This only works uncontrolled and the select all checkbox must support refs.
 */
export function useSelectAll(itemName: string): Ref<HTMLInputElement> {
    const inputRef = useRef<HTMLInputElement>(null);

    // For when the select all checkbox is pressed, update item checkboxes.
    const handleSelectAll = useCallback(
        (event: InputEvent) => {
            const input = event.target as HTMLInputElement;
            const items = input.form.elements.namedItem(itemName) as RadioNodeList;
            items.forEach((item: HTMLInputElement) => {
                item.checked = input.checked;
            });
        },
        [itemName]
    );

    // For when an item checkbox is pressed, update select all checkbox.
    const handleSelectItem = useCallback(
        (event: InputEvent) => {
            const input = event.target as HTMLInputElement;

            if (!input.checked) {
                inputRef.current.checked = false;
            } else if (
                Array.from(input.form.elements.namedItem(itemName) as RadioNodeList).every(
                    (item: HTMLInputElement) => item.checked
                )
            ) {
                inputRef.current.checked = true;
            }
        },
        [inputRef, itemName]
    );

    // Setup and tear down event listeners.
    useEffect(() => {
        const selectAllInput = inputRef.current;

        // Set upevent listeners from select all and select item checkboxes.
        selectAllInput.addEventListener('input', handleSelectAll);
        const items = selectAllInput.form.elements.namedItem(itemName) as RadioNodeList;
        items.forEach((item: HTMLInputElement) => item.addEventListener('input', handleSelectItem));

        // Tear down event listeners from select all and select item checkboxes.
        return () => {
            selectAllInput.removeEventListener('input', handleSelectAll);
            items.forEach((item: HTMLInputElement) => item.removeEventListener('input', handleSelectItem));
        };
    }, [inputRef, itemName, handleSelectAll, handleSelectItem]);

    return inputRef;
}
