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
    ariaLabel = '',
}) {
    if (href) {
        return (
            <a
                href={href}
                className={cx(styles.action, {[styles.delete]: highlight}, className)}
                aria-label={ariaLabel || displayText}
            >
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
            aria-label={ariaLabel || displayText}
        >
            {displayText} {hiddenText && <span className="give-visually-hidden">{hiddenText}</span>}
        </button>
    );
}
