import {CloseIcon, ErrorIcon, InfoIcon, WarningIcon} from '@givewp/admin/components/Notices/Icons';
import {useState} from '@wordpress/element';
import {useInstanceId} from '@wordpress/compose';
import {__} from '@wordpress/i18n';
import styles from './styles.module.scss';

/**
 * @since TBD Add title, ariaLabel and dismissLabel props
 * @since 4.10.0 Add className prop
 * @since 4.8.0
 */
interface Props {
    type: 'info' | 'warning' | 'error';
    className?: string;
    /** A visible heading. It also names the notice. */
    title?: string;
    /** The notice's name when it has no title. */
    ariaLabel?: string;
    /** The accessible name of the dismiss button. */
    dismissLabel?: string;
    children: React.ReactNode;
    dismissHandleClick?: () => void;
}

/**
 * @since TBD Add title, ariaLabel and dismissLabel props; fix the class name showing "undefined"
 * @since 4.10.0 Add className prop
 * @since 4.8.0
 */
const Notice = ({type, children, dismissHandleClick, className, title, ariaLabel, dismissLabel}: Props) => {
    const [isVisible, setIsVisible] = useState(true);
    const titleId = `givewp-notice-title-${useInstanceId(Notice)}`;

    const handleDismiss = () => {
        setIsVisible(false);
        if (dismissHandleClick) {
            dismissHandleClick();
        }
    };

    if (!isVisible) {
        return null;
    }

    const noticeClasses = [
        styles.notice,
        className,
        type === 'warning' ? styles.warning : type === 'error' ? styles.error : styles.info,
    ]
        .filter(Boolean)
        .join(' ');

    const hasName = Boolean(title || ariaLabel);

    const NoticeIcon = ({type}: {type: Props['type']}) => {
        const icons = {
            warning: WarningIcon,
            error: ErrorIcon,
            info: InfoIcon,
        };
        const IconComponent = icons[type] ?? icons.info;
        return <IconComponent />;
    };

    return (
        <div
            className={noticeClasses}
            role={hasName ? 'group' : undefined}
            aria-labelledby={title ? titleId : undefined}
            aria-label={!title && ariaLabel ? ariaLabel : undefined}
        >
            <NoticeIcon type={type} />
            <div className={styles.content}>
                {title && (
                    <h3 id={titleId} className={styles.title}>
                        {title}
                    </h3>
                )}
                {children}
            </div>
            {dismissHandleClick && (
                <button
                    type="button"
                    className={styles.dismissButton}
                    onClick={handleDismiss}
                    aria-label={dismissLabel ?? __('Dismiss notice', 'give')}
                >
                    <CloseIcon />
                </button>
            )}
        </div>
    );
};

export default Notice;
