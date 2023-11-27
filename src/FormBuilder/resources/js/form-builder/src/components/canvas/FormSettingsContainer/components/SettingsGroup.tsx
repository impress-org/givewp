import {Children, isValidElement} from 'react';
import classnames from 'classnames';
import {__} from '@wordpress/i18n';
import {chevronLeft, chevronRight} from '@wordpress/icons';
import {useFormSettingsContext} from '@givewp/form-builder/components/canvas/FormSettingsContainer';
import {
    navigateBackInMenu,
    updateMenuState,
} from '@givewp/form-builder/components/canvas/FormSettingsContainer/formSettingsReducer';
import {Icon} from '@wordpress/components';

export default function SettingsGroup({item, title, children}) {
    const [state, dispatch] = useFormSettingsContext();
    const {content, activeMenu} = state;

    const isActive = activeMenu === item;
    const hasNestedMenu = Children.toArray(children).some((child) => {
        return isValidElement(child) && child.type === SettingsGroup;
    });

    const handleItemClick = () => {
        dispatch(updateMenuState(hasNestedMenu, item, children));
    };

    const handleBackClick = () => {
        dispatch(navigateBackInMenu());
    };

    if (isActive && (!content || content !== children)) {
        handleItemClick();
    }

    if (hasNestedMenu) {
        children = Children.toArray(children).filter((child) => {
            return isValidElement(child) && child.type === SettingsGroup;
        });
    }

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
