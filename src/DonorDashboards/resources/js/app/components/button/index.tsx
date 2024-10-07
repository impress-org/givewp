import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import cx from 'classnames';

import './style.scss';

type ButtonProps = {
    classnames?: string;
    icon?: any;
    children: React.ReactNode;
    onClick?: () => void;
    href?: string;
    type?: 'button' | 'submit' | 'reset';
    variant?: boolean;
    disabled?: boolean;
};

const Button = ({icon, children, onClick, href, type, variant,classnames, ...rest}: ButtonProps) => {
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
            className={cx('give-donor-dashboard-button', classnames, {
                ['give-donor-dashboard-button--primary']: !variant,
                ['give-donor-dashboard-button--variant']: variant,
            })}
            onClick={onClick ? () => onClick() : null}
            type={type}
            {...rest}
        >
            <span>{children}</span>
            {icon && <FontAwesomeIcon icon={icon} />}
        </button>
    );
};
export default Button;
