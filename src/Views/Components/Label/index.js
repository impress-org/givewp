import classNames from 'classnames';
import styles from './styles.module.scss';

const Label = ({type, text}) => {
    const labelClasses = classNames(
        styles.label,
        {[styles.error]: type === 'error' || type === 'failed'},
        {[styles.warning]: type === 'warning'},
        {[styles.notice]: type === 'notice'},
        {[styles.success]: type === 'success'},
        {[styles.info]: type === 'info'},
        {[styles.http]: type.toUpperCase() === 'HTTP'}
    );

    const labelText = text && text.length > 0 ? text : type.charAt(0).toUpperCase() + type.slice(1);

    return <div className={labelClasses}>{labelText}</div>;
};

export default Label;
