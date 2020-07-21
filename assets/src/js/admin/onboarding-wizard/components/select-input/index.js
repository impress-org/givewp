import { Fragment } from 'react';
import './style.scss';

const SelectInput = ( { label, value, onChange, options } ) => {
	const optionElements = options.map( ( option, index ) => {
		return (
			<option value={ option.value } key={ index }>{ option.label }</option>
		);
	} );
	return (
		<Fragment>
			{ label && <p>{ label }</p> }
			<select value={ value } className="give-obw-select-input" onChange={ ( event ) => onChange( event.target.val ) } >
				{ optionElements }
			</select>
		</Fragment>
	);
};

export default SelectInput;
