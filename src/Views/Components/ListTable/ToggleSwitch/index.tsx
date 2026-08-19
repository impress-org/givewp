import styles from './style.module.scss';
import cx from 'classnames';

interface ToggleSwitchProps {
    onChange: (checked: boolean) => void;
    checked: boolean;
    ariaLabel?: string;
}

/**
 * @since 4.10.0 Remove checkbox to control state visibility.
 * @since 2.24.0
 */
const ToggleSwitch = ({ariaLabel, checked, onChange}: ToggleSwitchProps) => {
    const handleChange = () => {
        onChange(!checked);
    };

    return (
        <button
            className={styles.container}
            onClick={handleChange}
            role="switch"
            aria-checked={checked}
            aria-label={ariaLabel}
        >
            <span className={cx(styles.switch, { [styles.checked]: checked })} />
            {ariaLabel && <span>{ariaLabel}</span>}
        </button>
    );
};

export default ToggleSwitch;
