import {useCallback, useEffect, useRef} from 'react';

/**
 * Let a checkbox control the selection of other items (checkboxes).
 *
 * This only works uncontrolled and the select all checkbox must support refs.
 *
 * @param {string} itemName
 */
export function useSelectAll(itemName) {
    const inputRef = useRef();

    // For when the select all checkbox is pressed, update item checkboxes.
    const handleSelectAll = useCallback(
        (event) => {
            event.target.form.elements.namedItem(itemName).forEach((item) => {
                item.checked = event.target.checked;
            });
        },
        [itemName]
    );

    // For when an item checkbox is pressed, update select all checkbox.
    const handleSelectItem = useCallback(
        (event) => {
            const formElements = event.target.form.elements;
            if (!event.target.checked) {
                inputRef.current.checked = false;
            }

            if (event.target.checked && Array.from(formElements.namedItem(itemName)).every((item) => item.checked)) {
                inputRef.current.checked = true;
            }
        },
        [inputRef, itemName]
    );

    // Setup and tear down event listeners.
    useEffect(() => {
        const selectAllInput = inputRef.current;
        const formElements = selectAllInput.form.elements;

        selectAllInput.addEventListener('input', handleSelectAll);
        formElements.namedItem(itemName).forEach((item) => item.addEventListener('input', handleSelectItem));

        return () => {
            selectAllInput.removeEventListener('input', handleSelectAll);
            formElements.namedItem(itemName).forEach((item) => item.removeEventListener('input', handleSelectItem));
        };
    }, [inputRef, itemName, handleSelectAll, handleSelectItem]);

    return inputRef;
}
