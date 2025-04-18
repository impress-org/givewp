import classNames from 'classnames';

import styles from './style.module.scss';

const Button = ({children, onClick, icon, ...rest}) => {
    const classes = classNames({
        [styles.button]: true,
        [styles.icon]: icon,
    });

    return (
        <button className={classes} onClick={onClick} {...rest}>
            {children}
        </button>
    );
};

export default Button;
