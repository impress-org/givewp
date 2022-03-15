import styles from './RowAction.module.scss';
import cx from 'classnames';

export default function RowAction({onClick, className = '', actionId, displayText, hiddenText = '', disabled = false, highlight = false}) {
    return (
        <button
            type="button"
            onClick={onClick}
            data-actionid={actionId}
            className={cx(styles.action, {[styles.delete]: highlight }, className)}
            disabled={disabled}
        >
            {displayText} {hiddenText && <span className="give-visually-hidden">{hiddenText}</span>}
        </button>
    );
}
