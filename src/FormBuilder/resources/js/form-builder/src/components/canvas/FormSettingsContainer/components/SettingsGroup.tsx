import {Children, cloneElement, isValidElement, useEffect} from 'react';
import classnames from 'classnames';
import {__} from '@wordpress/i18n';
import {chevronLeft, chevronRight} from '@wordpress/icons';
import {useFormSettingsContext} from '@givewp/form-builder/components/canvas/FormSettingsContainer';
import {
    popMenuStack,
    pushMenuStack,
    resetMenuStack,
    setActiveMenu,
    setContent,
    setMenuPage,
} from '@givewp/form-builder/components/canvas/FormSettingsContainer/formSettingsReducer';
import {Icon} from '@wordpress/components';

export default function SettingsGroup({item, title, parentItem = null, children}) {
    const [state, dispatch] = useFormSettingsContext();
    const {content, menuPage, activeMenu, menuStack} = state;

    const isActive = activeMenu === item;
    const hasNestedMenu = Children.toArray(children).some((child) => {
        return isValidElement(child) && child.type === SettingsGroup;
    });

    useEffect(() => {
        if (isActive && !content) {
            handleItemClick();
        }
    }, []);

    useEffect(() => {
        if (isActive && content !== children) {
            dispatch(setContent(children));
        }
    }, [children]);

    if (hasNestedMenu) {
        children = Children.toArray(children).filter((child) => {
            return isValidElement(child) && child.type === SettingsGroup;
        });

        children = Children.map(children, (child) => {
            return cloneElement(child, {
                parentItem: item,
            });
        });
    }

    const handleItemClick = () => {
        if (hasNestedMenu) {
            dispatch(setMenuPage(menuPage + 1));
        } else {
            dispatch(setActiveMenu(item));
            dispatch(setContent(children));
        }

        if (!parentItem) {
            dispatch(resetMenuStack());
        }

        dispatch(pushMenuStack(item));
    };

    const handleBackClick = () => {
        dispatch(setMenuPage(menuPage - 1));
        dispatch(popMenuStack());
    };

    return (
        <li className={classnames({'is-active': isActive, 'has-children': hasNestedMenu})}>
            <button onClick={handleItemClick}>
                {title}
                {hasNestedMenu && <Icon icon={chevronRight} />}
            </button>
            {hasNestedMenu && (
                <ul>
                    <li>
                        <button onClick={handleBackClick} className={'back-menu-button'}>
                            <Icon icon={chevronLeft} /> {__('Back to main menu', 'give')}
                        </button>
                    </li>
                    {children}
                </ul>
            )}
        </li>
    );
}
