import cx from 'classnames';

export default function Select({children, className = '', ...rest}) {
    return (
            <select className={cx('givewp-select', className)} {...rest}>
                {children}
            </select>
    );
}
