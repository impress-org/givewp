import cx from 'classnames';
import './styles.scss';

type BadgeProps = {
    variant: 'primary' | 'secondary';
    caption: string;
    iconSrc: string;
    alt: string;
};

/**
 * @since 3.0.0
 */
export default function Badge({variant, caption, iconSrc, alt}: BadgeProps) {
    return (
        <div
            className={cx('givewp-welcome-banner-badge', {
                ['givewp-welcome-banner-badge--primary']: variant === 'primary',
                ['givewp-welcome-banner-badge--secondary']: variant === 'secondary',
            })}
        >
            <img src={iconSrc} alt={alt} />
            {caption}
        </div>
    );
}
