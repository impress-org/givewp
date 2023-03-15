import {ActionMenuProps} from './types';

import styles from './style.module.scss';

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
            {menuConfig.map(({action, title}) => {
                return (
                    <li>
                        <button onClick={() => handleClick(action)}>{title}</button>
                    </li>
                );
            })}
        </ul>
    );
}
