import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import cx from 'classnames';
import './style.scss';

type ButtonProps = {
    icon?: any;
    children: React.ReactNode;
    onClick?: () => void;
    href?: string;
    type?: 'button' | 'submit' | 'reset';
    variant?: boolean;
    disabled?: boolean;
};

const Button = ({icon, children, onClick, href, type, variant, ...rest}: ButtonProps) => {
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
