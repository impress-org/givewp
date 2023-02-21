import {__, sprintf} from '@wordpress/i18n';
import {useCallback, useContext, useEffect, useState} from 'react';
import {CheckboxContext} from '@givewp/components/ListTable/ListTablePage';

export const BulkActionCheckbox = ({id, name, singleName, data}) => {
    const checkboxRefs = useContext(CheckboxContext);
    // add this element's ref to the list of checkboxes so we can access them imperatively
    const updateCheckboxRefs = useCallback(
        (node) => {
            if (node !== null && ! checkboxRefs?.current.includes(node)) {
                checkboxRefs?.current.push(node);
            }
        },
        [data]
    );

    useEffect(() => {
        // cleanup function to remove the ref checked value when the component unmounts
        return () => {
            checkboxRefs.current.forEach((checkbox) => {
                checkbox.checked = false;
            });
            checkboxRefs.current = checkboxRefs.current.filter((checkbox) => checkbox.dataset.id === id);
        };
    }, []);
    return (
        <>
            <label
                htmlFor={`giveListTableSelect${id}`}
                id={`giveListTableSelect${id}-Label`}
                className="give-visually-hidden"
            >
                {sprintf(__('Select %1s %2s', 'give'), singleName, id)}
            </label>
            <input
                ref={updateCheckboxRefs}
                className="giveListTableSelect"
                data-id={id}
                data-name={name ? name : null}
                id={`giveListTableSelect${id}`}
                aria-labelledby={`giveListTableSelect${id}-Label`}
                type="checkbox"
            />
        </>
    );
};

export const BulkActionCheckboxAll = ({pluralName, data}) => {
    const checkboxRefs = useContext(CheckboxContext);
    const [checked, setChecked] = useState(false);
    // reset the 'Select all' checkbox when table contents change
    useEffect(() => {
        setChecked(false);
    }, [data]);
    return (
        <>
            <label htmlFor="giveListTableSelectAll" id="giveListTableSelectAll-Label" className="give-visually-hidden">
                {sprintf(__('Select all %s', 'give'), pluralName)}
            </label>
            <input
                id="giveListTableSelectAll"
                type="checkbox"
                className="giveListTableSelect"
                aria-labelledby="giveListTableSelectAll-Label"
                onChange={(event) => toggleAllRowCheckboxes(event, checkboxRefs, setChecked, checked)}
                checked={checked}
            />
        </>
    );
};

const toggleAllRowCheckboxes = (event, checkboxRefs, setChecked, checked) => {
    checkboxRefs.current.forEach((checkbox) => {
        checkbox.checked = !checked;
    });
    setChecked(!checked);
};
