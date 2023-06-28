import cx from 'classnames';
import styles from './style.module.scss';

interface ButtonGroupProps {
    children: JSX.Element | JSX.Element[];
    align?: 'left' | 'right' | 'center' | 'space-between';
}

export default function ButtonGroup({children, align = 'left'}: ButtonGroupProps) {
    return (
        <div className={cx(styles.group, styles[align])}>
            {children}
        </div>
    );
}
