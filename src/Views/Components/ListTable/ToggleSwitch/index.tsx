import styles from './style.module.scss';

interface ToggleSwitchProps {
    setToggle: React.Dispatch<React.SetStateAction<boolean>>;
    toggle: boolean;
    label?: string;
}

const ToggleSwitch = ({label, toggle, setToggle}: ToggleSwitchProps) => {
    return (
        <label className={styles.container}>
            <input type="checkbox" aria-label="switch" checked={toggle} onChange={() => setToggle(!toggle)} />
            <span className={styles.switch} />
            <span>{label && label}</span>
        </label>
    );
};

export default ToggleSwitch;
