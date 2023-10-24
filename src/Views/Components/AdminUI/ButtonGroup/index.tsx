import cx from 'classnames';
import './style.scss';

interface ButtonGroupProps {
    children: JSX.Element | JSX.Element[];
    align?: 'left' | 'right' | 'center' | 'space-between';
}

export default function ButtonGroup({children, align = 'left'}: ButtonGroupProps) {
    return (
        <div className={cx('givewp-button-group', align)}>
            {children}
        </div>
    );
}
