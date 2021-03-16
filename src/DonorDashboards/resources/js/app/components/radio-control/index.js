import { toKebabCase } from '../../utils';
import './style.scss';

const RadioControl = ( { label, description, options, value, onChange } ) => {
	const optionEls = options.map( ( option, index ) => {
		const id = toKebabCase( option.value );
		return (
			<div className="give-donor-dashboard-radio-control__option" key={ index }>
				<input type="radio" name="format" id={ id } value={ option.value } checked={ option.value === value ? true : false } onChange={ ( evt ) => onChange( evt.target.value ) } />
				<label htmlFor={ id }>{ option.label }</label>
			</div>
		);
	} );
	return (
		<fieldset className="give-donor-dashboard-radio-control">
			{ label && ( <legend className="give-donor-dashboard-radio-control__legend">{ label }</legend> ) }
			{ description && (
				<div className="give-donor-dashboard-radio-control__description">
					{ description }
				</div>
			) }
			{ optionEls }
		</fieldset>
	);
};
export default RadioControl;
