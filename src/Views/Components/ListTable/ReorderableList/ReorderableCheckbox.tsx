import {useCallback, useEffect} from 'react';

export interface ReorderableCheckboxProps {
    label: string;
    id: string;
    visible: boolean;
    reorderableCheckboxRefs;
}
const ReorderableCheckbox = ({reorderableCheckboxRefs, label, id, visible}: ReorderableCheckboxProps) => {
    const updateCheckboxRefs = useCallback((node) => {
        if (node !== null) {
            reorderableCheckboxRefs?.current.push(node);
        }
    }, []);

    useEffect(() => {
        // cleanup function to remove the ref when the component unmounts
        return () => {
            reorderableCheckboxRefs.current = reorderableCheckboxRefs.current.filter((checkbox) => checkbox.dataset.id === id);
        };
    }, []);

    return (
        <label key={id} htmlFor={`giveReorderableSelect${id}`} id={`giveReorderableSelect${id}-Label`}>
            <input ref={updateCheckboxRefs} id={`giveReorderableSelect${id}`} data-id={id} type={'checkbox'} defaultChecked={visible} />
            {label}
        </label>
    );
};

export default ReorderableCheckbox;
