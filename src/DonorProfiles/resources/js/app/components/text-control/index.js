import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { toUniqueId } from '../../utils';

import './style.scss';

const TextControl = ( { label, value, onChange, icon, type } ) => {
	const id = toUniqueId( label );

	return (
		<div className="give-donor-dashboard-text-control">
			{ label && ( <label className="give-donor-dashboard-text-control__label" htmlFor={ id }>{ label }</label> ) }
			<div className="give-donor-dashboard-text-control__input">
				{ icon && (
					<FontAwesomeIcon icon={ icon } />
				) }
				<input
					id={ id }
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
