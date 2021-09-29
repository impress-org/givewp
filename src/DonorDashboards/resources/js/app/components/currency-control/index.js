// Import vendor dependencies
import { useCallback } from 'react';
import PropTypes from 'prop-types';
import CurrencyInput, {formatValue} from 'react-currency-input-field';

// Import utilities
import { toUniqueId, toKebabCase } from '../../utils';

// Import styles
import './style.scss';

import { __, sprintf } from '@wordpress/i18n';

const minorOfFloat = (float, decimals) => Number.parseFloat(float) * Math.pow(10, decimals);

const CurrencyControl = ( {
	clearValidationError,
	currency,
	label,
	max,
	min,
	onChange,
	placeholder,
	setValidationError,
	value,
	width,
} ) => {
	const id = toUniqueId( label );
	const name = toKebabCase( label );

	const formatConfig = {
		decimalScale: currency.numberDecimals,
		decimalsLimit: currency.numberDecimals,
		prefix: currency.currencyPosition === 'before' ? currency.symbol : null,
		suffix: currency.currencyPosition === 'after' ? currency.symbol : null,
		decimalSeparator: currency.decimalSeparator,
		groupSeparator: currency.thousandsSeparator,
	};

	// Ideally, we’d just use the value from the event.target, however, that’s
	// formatted all nicely and we want a float, so we can just use the
	const validate = useCallback(() => {
		if (value) {
			const minorOfValue = minorOfFloat(value, currency.numberDecimals);
			const minorOfMin = minorOfFloat(min, currency.numberDecimals);
			const minorOfMax = minorOfFloat(max, currency.numberDecimals);

			if (minorOfValue > minorOfMax) {
				setValidationError(
					sprintf(
						__('Amount must be less than %s', 'give'),
						formatValue({value: max, ...formatConfig}),
					),
				);
			} else if (minorOfValue < minorOfMin) {
				setValidationError(
					sprintf(
						__('Amount must be more than %s', 'give'),
						formatValue({value: min, ...formatConfig}),
					),
				);
			} else {
				clearValidationError();
			}
		} else {
			setValidationError(
				sprintf(
					__('Please enter an amount between %s and %s or choose a predefined amount', 'give'),
					formatValue({value: min, ...formatConfig}),
					formatValue({value: max, ...formatConfig}),
				),
			)
		}
	}, [currency.numberDecimals, min, max, value]);

	return (
		<div className="give-donor-dashboard-currency-control" style={ width ? { maxWidth: width } : null }>
			{ label && ( <label className="give-donor-dashboard-currency-control__label" htmlFor={ id }>{ label }</label> ) }
			<div className="give-donor-dashboard-currency-control__input">
				<CurrencyInput
					id={ id }
					name={ name }
					placeholder={ placeholder }
					value={ value ?? '' }
					onValueChange={ onChange }
					onBlur={ validate }
					allowNegativeValue={ false }
					{...formatConfig}
				/>
			</div>
		</div>
	);
};

CurrencyControl.propTypes = {
	label: PropTypes.string,
	value: PropTypes.string,
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
