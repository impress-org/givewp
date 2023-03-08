import styles from './style.module.scss';

/**
 *
 * @unreleased
 */

export default function MoreActionsMenu({actionConfig, toggle}) {
    const handleClick = (action) => {
        action();
        toggle();
    };
    return (
        <ul className={styles.actionMenu}>
            {actionConfig.map((action) => {
                return (
                    <li>
                        <button onClick={() => handleClick(action.action)}>{action.title}</button>
                    </li>
                );
            })}
        </ul>
    );
}
