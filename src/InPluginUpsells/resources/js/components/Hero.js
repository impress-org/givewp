import styles from './Hero.module.css';

export const Hero = ({heading, description}) => (
    <div className={styles.hero}>
        <h2 className={styles.title}>{heading}</h2>
        <p className={styles.description}>{description}</p>
    </div>
);
