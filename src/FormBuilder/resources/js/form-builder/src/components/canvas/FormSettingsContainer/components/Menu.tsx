import {__} from '@wordpress/i18n';
import {chevronLeft, chevronRight} from '@wordpress/icons';
import {Icon} from '@wordpress/components';
import {useFormSettingsContext} from '@givewp/form-builder/components/canvas/FormSettingsContainer';
import {
    navigateBackInMenu,
    updateMenuState,
} from '@givewp/form-builder/components/canvas/FormSettingsContainer/formSettingsReducer';
import classnames from 'classnames';

function MenuItem({item}) {
    const [state, dispatch] = useFormSettingsContext();
    const isActive = state.activeMenu === item.path;
    const hasChildren = item.children && item.children.length;

    const handleItemClick = () => {
        dispatch(updateMenuState(hasChildren, item.path));
    };

    const handleBackClick = () => {
        dispatch(navigateBackInMenu());
    };

    return (
        <li className={classnames({'is-active': isActive, 'has-children': hasChildren})}>
            <button onClick={handleItemClick}>
                {item.name}
                {hasChildren && <Icon icon={chevronRight} />}
            </button>
            {hasChildren && (
                <ul>
                    <li>
                        <button onClick={handleBackClick} className={'back-menu-button'}>
                            <Icon icon={chevronLeft} /> {__('Back to main menu', 'give')}
                        </button>
                    </li>
                    {item.children.map((child, index) => (
                        <MenuItem item={child} key={index} />
                    ))}
                </ul>
            )}
        </li>
    );
}

export default function Menu({routes}) {
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
