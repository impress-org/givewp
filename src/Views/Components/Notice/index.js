import styles from './style.module.scss';

const Notice = ({children}) => {
    return (
        <div className={styles.notice}>
            <div className={styles.card}>{children}</div>
        </div>
    );
};

export default Notice;
