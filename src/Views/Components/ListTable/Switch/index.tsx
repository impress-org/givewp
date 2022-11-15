import styles from './style.module.scss';

const Switch = ({label, toggle, checked}) => {
    return (
        <label className={styles.container}>
            <input type="checkbox" aria-label="switch" checked={checked} onChange={() => toggle(!checked)} />
            <span className={styles.switch} />
            <span>{label && label}</span>
        </label>
    );
};

export default Switch;
