import PropTypes from 'prop-types';
import { useState, useEffect } from 'react';
import { PaymentInputsWrapper, usePaymentInputs } from 'react-payment-inputs';
import images from 'react-payment-inputs/images';
import { useAccentColor } from '../../../../hooks';

import './style.scss';

const CardControl = ( { label, onChange, value } ) => {
	const [ cardNumber, setCardNumber ] = useState( value ? value.card_number : '' );
	const [ cardExpiryDate, setCardExpiryDate ] = useState( value ? `${ value.card_exp_month } \ ${ value.card_exp_year }` : '' );
	const [ cardCVC, setCardCVC ] = useState( value ? value.card_cvc : '' );
	const [ cardZIP, setCardZIP ] = useState( value ? value.card_zip : '' );
	const accentColor = useAccentColor();

	useEffect( () => {
		if ( onChange ) {
			onChange( {
				card_number: cardNumber.replace( /\s+/g, '' ),
				card_exp_month: cardExpiryDate.substr( 0, 2 ),
				card_exp_year: cardExpiryDate.substr( 3, 5 ),
				card_cvc: cardCVC,
				card_zip: cardZIP,
			} );
		}
	}, [ onChange, cardNumber, cardExpiryDate, cardCVC, cardZIP ] );

	const {
		wrapperProps,
		getCardImageProps,
		getCardNumberProps,
		getExpiryDateProps,
		getCVCProps,
		getZIPProps,
	} = usePaymentInputs();

	const inputProps = {
		...wrapperProps,
		styles: {
			fieldWrapper: {
				base: {
					width: '100%',
				},
			},
			inputWrapper: {
				base: {
					display: 'flex',
					alignItems: 'center',
					marginTop: '8px',
					border: '1px solid #b8b8b8',
					overflow: 'hidden',
					padding: '14px 14px',
					boxShadow: `0 0 0 0 ${ accentColor }`,
					transition: 'box-shadow 0.1s ease',
					borderRadius: '4px',
				},
				errored: {
					border: '1px solid #b8b8b8',
					boxShadow: '0 0 0 1px #c9444d',
				},
				focused: {
					border: '1px solid #b8b8b8',
					boxShadow: `0 0 0 1px ${ accentColor }`,
				},
			},
			input: {
				base: {
					fontSize: '14px',
					fontFamily: 'Montserrat, Arial, Helvetica, sans-serif',
					fontWeight: '500',
					color: '#828382',
					lineHeight: '1.2',
				},
				//   errored: css | Object,
				cardNumber: {
					flex: '1',
				},
				//   expiryDate: css | Object,
				//   cvc: css | Object
			},
			// errorText: {
			//   base: css | Object
			// }
		},
	};

	return (
		<div className="give-donor-dashboard-card-control">
			<label className="give-donor-dashboard-card-control__label">{ label }</label>
			<PaymentInputsWrapper { ...inputProps }>
				<svg { ...getCardImageProps( { images } ) } />
				<input { ...getCardNumberProps( { onChange: ( e ) => setCardNumber( e.target.value ), value: cardNumber } ) } />
				<input { ...getExpiryDateProps( { onChange: ( e ) => setCardExpiryDate( e.target.value ), value: cardExpiryDate } ) } />
				<input { ...getCVCProps( { onChange: ( e ) => setCardCVC( e.target.value ), value: cardCVC } ) } />
				<input { ...getZIPProps( { onChange: ( e ) => setCardZIP( e.target.value ), value: cardZIP } ) } />
			</PaymentInputsWrapper>
		</div>
	);
};

CardControl.propTypes = {
	label: PropTypes.string,
	value: PropTypes.object,
	onChange: PropTypes.func,
};

CardControl.defaultProps = {
	label: null,
	value: null,
	onChange: null,
};

export default CardControl;
