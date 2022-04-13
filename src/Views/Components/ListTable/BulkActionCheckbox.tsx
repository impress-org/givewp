import {__, sprintf} from "@wordpress/i18n";
import {useCallback, useContext, useEffect} from "react";
import {CheckboxContext} from "@givewp/components/ListTable/index";

export const BulkActionCheckbox = ({id, name, singleName}) => {
    const checkboxRefs = useContext(CheckboxContext);
    // add this element's ref to the list of checkboxes so we can access them imperatively
    const updateCheckboxRefs = useCallback(node => {
        if (node !== null) {
            checkboxRefs?.current.push(node);
        }
    }, []);

    useEffect(() => {
        // cleanup function to remove the ref when the component unmounts
        return () => {
          checkboxRefs.current = checkboxRefs.current.filter(checkbox => (
              checkbox.dataset.id === id
          ));
        };
    }, []);

    return (
        <>
            <label htmlFor={`giveListTableSelect${id}`} id={`giveListTableSelect${id}-Label`} className='give-visually-hidden'>
                {sprintf(__('Select %1s %2s', 'give'), singleName, id)}
            </label>
            <input
                ref={updateCheckboxRefs}
                className='giveListTableSelect'
                data-id={id}
                data-name={name ? name : null}
                id={`giveListTableSelect${id}`}
                aria-labelledby={`giveListTableSelect${id}-Label`}
                type='checkbox'
            />
        </>
    );
}
