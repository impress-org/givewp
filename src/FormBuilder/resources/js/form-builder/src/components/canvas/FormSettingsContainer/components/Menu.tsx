import { __ } from "@wordpress/i18n";
import { chevronLeft, chevronRight } from "@wordpress/icons";
import { Icon } from "@wordpress/components";
import { useFormSettingsContext } from "@givewp/form-builder/components/canvas/FormSettingsContainer";
import {
    navigateBackInMenu,
    updateMenuState
} from "@givewp/form-builder/components/canvas/FormSettingsContainer/formSettingsReducer";
import classnames from "classnames";
import { Route } from "@givewp/form-builder/components/canvas/FormSettings";
import { useFormState } from "@givewp/form-builder/stores/form-state";

/**
 * @since 3.3.0
 */
function MenuItem({item}: {item: Route}) {
    const [state, dispatch] = useFormSettingsContext();
    const {settings} = useFormState();
    const isActive = state.activeMenu === item.path;
    const hasChildren = item.childRoutes && item.childRoutes.length > 0;
    const isActiveParent = hasChildren && state.activeMenu.split('/').includes(item.path);
    const baseClassName = 'givewp-form-settings__menu';

    if (item.showWhen && !item.showWhen({item, settings})) {
        return null;
    }

    const handleItemClick = () => {
        dispatch(updateMenuState(hasChildren, item.path));
    };

    const handleBackClick = () => {
        dispatch(navigateBackInMenu());
    };

    return (
        <li
            className={classnames({
                [`${baseClassName}__item`]: true,
                [`${baseClassName}__item--is-active`]: isActive,
                [`${baseClassName}__item--has-children`]: hasChildren,
            })}
        >
            <button onClick={handleItemClick}>
                {item.name}
                {hasChildren && <Icon icon={chevronRight} />}
            </button>
            {isActiveParent && (
                <ul>
                    <li>
                        <button onClick={handleBackClick} className={`${baseClassName}__back-button`}>
                            <Icon icon={chevronLeft} /> {__('Back to main menu', 'give')}
                        </button>
                    </li>
                    {item.childRoutes.map((child, index) => (
                        <MenuItem item={child} key={index} />
                    ))}
                </ul>
            )}
        </li>
    );
}

/**
 * @since 3.3.0
 */
export default function Menu({routes}: {routes: Route[]}) {
    const [state] = useFormSettingsContext();

    return (
        <div className={'givewp-form-settings__menu'}>
            <ul className={`givewp-form-settings__menu__page-${state.menuPage}`}>
                {routes.map((route, index) => (
                    <MenuItem item={route} key={index} />
                ))}
            </ul>
        </div>
    );
}
