import styles from './RowAction.module.scss';
import cx from 'classnames';

export default function RowAction({
    onClick = null,
    className = '',
    actionId = null,
    displayText,
    hiddenText = '',
    disabled = false,
    highlight = false,
    href = '',
}) {
    if (href) {
        return (
            <a href={href} className={cx(styles.action, {[styles.delete]: highlight}, className)}>
                {displayText} {hiddenText && <span className="give-visually-hidden">{hiddenText}</span>}
            </a>
        );
    }

    if (!onClick) {
        return null;
    }

    return (
        <button
            type="button"
            onClick={onClick}
            data-actionid={actionId}
            className={cx(styles.action, {[styles.delete]: highlight}, className)}
            disabled={disabled}
        >
            {displayText} {hiddenText && <span className="give-visually-hidden">{hiddenText}</span>}
        </button>
    );
}
