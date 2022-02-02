import cx from 'classnames';

import styles from './Hero.module.css';

export const Hero = ({heading, description, className = null}) => (
    <div className={cx(styles.hero, className)}>
        <h2 className={styles.title}>{heading}</h2>
        <p className={styles.description}>{description}</p>
    </div>
);
