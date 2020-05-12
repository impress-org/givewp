import PropTypes from 'prop-types';
import './style.scss';

const Toggle = ( { label, onChange, value } ) => {
	const name = label.replace( /\s+/g, '-' ).toLowerCase();
	return (
		<div className="givewp-reports-toggle">
			<input type="checkbox" onChange={ onChange } id={ name } checked={ value } />
			<label htmlFor={ name }>{ label }</label>
		</div>
	);
};

Toggle.propTypes = {
	// Label for toggle
	label: PropTypes.string.isRequired,
	// Fired on toggle change
	onChange: PropTypes.func,
	// Value for toggle
	value: PropTypes.bool,
};

export default Toggle;
