import styles from './style.module.scss';
import {ActionMenuProps} from '@givewp/components/AdminUI/ActionMenu/types';

/**
 *
 * @unreleased
 */

export default function ActionMenu({menuConfig, toggle}: ActionMenuProps) {
    const handleClick = (action) => {
        action();
        toggle();
    };
    return (
        <ul className={styles.navigationMenu}>
            {menuConfig.map((action) => {
                return (
                    <li>
                        <button onClick={() => handleClick(action.action)}>{action.title}</button>
                    </li>
                );
            })}
        </ul>
    );
}
