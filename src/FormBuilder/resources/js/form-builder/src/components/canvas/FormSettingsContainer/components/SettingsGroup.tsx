import {Children, cloneElement, isValidElement, useEffect} from 'react';
import classnames from 'classnames';
import {__} from '@wordpress/i18n';
import {useFormSettingsContext} from '@givewp/form-builder/components/canvas/FormSettingsContainer';
import {
    popMenuStack,
    pushMenuStack,
    resetMenuStack,
    setContent,
} from '@givewp/form-builder/components/canvas/FormSettingsContainer/formSettingsReducer';

export default function SettingsGroup({item, title, parentItem = null, children}) {
    const [state, dispatch] = useFormSettingsContext();
    const {content, menuStack} = state;

    const isActive = menuStack.includes(item);
    const hasNestedMenu = Children.toArray(children).some((child) => {
        return isValidElement(child) && child.type === SettingsGroup;
    });

    useEffect(() => {
        if (isActive && !content) {
            handleItemClick();
        }
    }, []);

    if (hasNestedMenu) {
        children = Children.map(children, (child) => {
            return cloneElement(child, {
                parentItem: item,
            });
        });
    }

    const handleItemClick = () => {
        if (!hasNestedMenu) {
            dispatch(setContent(children));
        }

        if (!parentItem) {
            dispatch(resetMenuStack());
        }

        dispatch(pushMenuStack(item));
    };

    const handleBackClick = () => {
        dispatch(popMenuStack());
    };

    return (
        <li className={classnames({'is-active': isActive, 'has-children': hasNestedMenu})}>
            <button onClick={handleItemClick}>{title}</button>
            {hasNestedMenu && (
                <ul>
                    <li>
                        <button onClick={handleBackClick}>{__('Back', 'give')}</button>
                    </li>
                    {children}
                </ul>
            )}
        </li>
    );
}
