import styles from './style.module.scss';

interface SwitchProps {
    label?: string;
    action?: any;
    selected?: boolean;
}

const Switch = ({label, selected, action}: SwitchProps) => {
    return (
        <label className={styles.container}>
            <input type="checkbox" aria-label="switch" checked={selected} onChange={() => action(!selected)} />
            <span className={styles.switch} />
            <span>{label && label}</span>
        </label>
    );
};

export default Switch;
