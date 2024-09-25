import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import cx from 'classnames';
import './style.scss';

const Button = ({icon, children, onClick, href, type, variant, ...rest}) => {
    const handleHrefClick = (e) => {
        e.preventDefault();
        window.parent.location = href;
    };

    if (href) {
        return (
            <a
                className="give-donor-dashboard-button give-donor-dashboard-button--primary"
                onClick={(e) => handleHrefClick(e)}
                href={href}
                {...rest}
            >
                {children}
                {icon && <FontAwesomeIcon icon={icon} />}
            </a>
        );
    }
    return (
        <button
            className={cx('give-donor-dashboard-button', 'give-donor-dashboard-button--primary', {
                'give-donor-dashboard-button--variant': variant,
            })}
            onClick={onClick ? () => onClick() : null}
            type={type}
            {...rest}
        >
            {children}
            {icon && <FontAwesomeIcon icon={icon} />}
        </button>
    );
};
export default Button;
