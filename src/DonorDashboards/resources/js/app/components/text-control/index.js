import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {toUniqueId} from '../../utils';

import './style.scss';

const TextControl = ({
    label = null,
    value = '',
    onChange = null,
    icon = null,
    type = 'text',
    name = null,
    autoComplete = null,
}) => {
    const id = toUniqueId(label);

    return (
        <div className="give-donor-dashboard-text-control">
            {label && (
                <label className="give-donor-dashboard-text-control__label" htmlFor={id}>
                    {label}
                </label>
            )}
            <div className="give-donor-dashboard-text-control__input">
                {icon && <FontAwesomeIcon icon={icon} />}
                <input
                    id={id}
                    name={name}
                    type={type}
                    autoComplete={autoComplete}
                    value={value}
                    onChange={(evt) => onChange && onChange(evt.target.value)}
                />
            </div>
        </div>
    );
};

export default TextControl;
