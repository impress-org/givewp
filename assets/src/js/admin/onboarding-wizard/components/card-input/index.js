// Import vendor dependencies
import React from 'react';
import PropTypes from 'prop-types';

// Import styles
import './style.scss';

import Card from '../card';

const CardInput = ( { checkMultiple, values, onChange, children } ) => {
	const handleChange = ( value ) => {
		let newValues;
		if ( checkMultiple === true ) {
			newValues = values.includes( value ) ? values.filter( e => e !== value ) : values.concat( [ value ] );
		} else {
			newValues = value;
		}
		onChange( newValues );
	};

	const cards = children.map( ( card, index ) => {
		return (
			<div className="give-obw-card-input__option" key={ index }>
				<input type="checkbox" id={ card.props.value } value={ card.props.value } onChange={ ( evt ) => handleChange( evt.target.value ) } checked={ values.includes( card.props.value ) } />
				<label htmlFor={ card.props.value }>{ card }</label>
			</div>
		);
	} );

	return (
		<div className="give-obw-card-input">
			{ cards }
		</div>
	);
};

CardInput.propTypes = {
	checkMultiple: PropTypes.bool,
	values: PropTypes.array,
	onChange: PropTypes.func,
	children: function( props, propName, componentName ) {
		const prop = props[ propName ];

		let error = null;
		React.Children.forEach( prop, function( child ) {
			if ( child.type !== Card && typeof child.props.value === undefined ) {
				error = new Error( '`' + componentName + '` children should be of type `Card` with a `value` prop.' );
			}
		} );
		return error;
	},
};

CardInput.defaultProps = {
	checkMultiple: true,
	values: [],
	onChange: null,
	children: null,
};

export default CardInput;

