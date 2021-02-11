// Import vendor dependencies
import PropTypes from 'prop-types';
import CurrencyInput from 'react-currency-input-field';

// Import utilities
import { toUniqueId, toKebabCase } from '../../utils';

// Import styles
import './style.scss';

const { __ } = wp.i18n;

const CurrencyControl = ( { label, onChange, value, placeholder, currency, min, max, width } ) => {
	const id = toUniqueId( label );
	const name = toKebabCase( label );

	const handleBlur = () => {
		switch ( true ) {
			case ( max && value > max ): {
				onChange( max.toFixed( currency.numberDecimals ) );
				break;
			}
			case ( min && value < min ): {
				onChange( min.toFixed( currency.numberDecimals ) );
				break;
			}
		}
	};

	return (
		<div className="give-donor-profile-currency-control" style={ width ? { maxWidth: width } : null }>
			{ label && ( <label className="give-donor-profile-currency-control__label" htmlFor={ id }>{ label }</label> ) }
			<div className="give-donor-profile-currency-control__input">
				<CurrencyInput
					id={ id }
					name={ name }
					placeholder={ placeholder }
					value={ value }
					onValueChange={ ( val ) => onChange( val ) }
					onBlur={ () => handleBlur() }
					allowNegativeValue={ false }
					decimalsLimit={ currency.numberDecimals }
					decimalScale={ currency.numberDecimals }
					prefix={ currency.currencyPosition === 'before' ? currency.symbol : null }
					suffix={ currency.currencyPosition === 'after' ? currency.symbol : null }
					decimalSeparator={ currency.decimalSeparator }
					groupSeparator={ currency.thousandsSeparator }
				/>
			</div>
		</div>
	);
};

CurrencyControl.propTypes = {
	label: PropTypes.string,
	value: PropTypes.string.isRequired,
	onChange: PropTypes.func,
	placeholder: PropTypes.string,
	width: PropTypes.string,
	currency: PropTypes.object.isRequired,
};

CurrencyControl.defaultProps = {
	label: null,
	value: null,
	onChange: null,
	placeholder: __( 'Enter amount...', 'give' ),
	width: null,
	currency: {
		code: 'USD',
		currencyPosition: 'before',
		decimalSeparator: '.',
		numberDecimals: 2,
		symbol: '$',
		thousandsSeparator: ',',
	},
};

export default CurrencyControl;
