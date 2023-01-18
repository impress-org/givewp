import styles from './style.module.scss';

interface ToggleSwitchProps {
    onChange: React.Dispatch<React.SetStateAction<boolean>>;
    checked: boolean;
    ariaLabel?: string;
}

const ToggleSwitch = ({ariaLabel, checked, onChange}: ToggleSwitchProps) => {
    return (
        <label className={styles.container}>
            <input type="checkbox" aria-label={ariaLabel} checked={checked} onChange={() => onChange(!checked)} />
            <span className={styles.switch} />
            <span>{ariaLabel && ariaLabel}</span>
        </label>
    );
};

export default ToggleSwitch;
