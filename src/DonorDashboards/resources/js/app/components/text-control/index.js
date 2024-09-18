import {FontAwesomeIcon} from '@fortawesome/react-fontawesome';
import {toUniqueId} from '../../utils';

import './style.scss';

const TextControl = ({label = null, value = '', onChange = null, icon = null, type = 'text'}) => {
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
                <input id={id} type={type} value={value} onChange={(evt) => onChange(evt.target.value)} />
            </div>
        </div>
    );
};

export default TextControl;
