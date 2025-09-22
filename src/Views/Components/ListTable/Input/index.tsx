import cx from 'classnames';

export default function Input({className = '', ...rest}) {
    return <input className={cx(className)} {...rest} />;
}
