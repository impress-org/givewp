import {__} from '@wordpress/i18n';
import './style.scss';

export default function ({options, onClick, selected}) {
    return (
        <div className="give-toggle">
            {options.map((value, i) => {
                return  <div key={i} className="give-toggle__option"  onClick={onClick}>{value}</div>
            })}
        </div>
    );
}
