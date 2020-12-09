import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { toKebabCase } from '../../utils';

import './style.scss';

const TextControl = ( { label, value, onChange, icon, type } ) => {
	return (
		<div className="give-donor-profile-text-control">
			{ label && ( <label className="give-donor-profile-text-control__label" htmlFor={ toKebabCase( label ) }>{ label }</label> ) }
			<div className="give-donor-profile-text-control__input">
				{ icon && (
					<FontAwesomeIcon icon={ icon } />
				) }
				<input
					id={ label && toKebabCase( `${ label }-${ Math.floor( Math.random() * Math.floor( 1000 ) ) }` ) }
					type={ type }
					value={ value }
					onChange={ ( evt ) => onChange( evt.target.value ) }
				/>
			</div>
		</div>
	);
};

TextControl.defaultProps = {
	label: null,
	value: '',
	onChange: null,
	icon: null,
	type: 'text',
};

export default TextControl;
