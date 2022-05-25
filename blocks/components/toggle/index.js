import {__} from '@wordpress/i18n';
import './style.scss';

export default function ({options, onClick, selected}) {
    return (
        <div className="give-toggle">
            {options.map((value, i) => {
                return  (<div
                            key={i}
                            onClick={() => onClick(value)}
                            className="give-toggle__option" style={ selected === value ? {background: '#007cba', color: 'white'} : {}}>
                            {value}
                        </div>)
            })}
        </div>
    );
}
