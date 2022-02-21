import cx from 'classnames';

import styles from './Card.module.css';

export const Card = ({as: Element = 'div', className, ...props}) => (
    <Element className={cx(styles.card, className)} {...props} />
);
