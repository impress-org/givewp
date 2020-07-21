import PropTypes from 'prop-types';
import React, { useEffect, useRef } from 'react';
import './style.scss';

import { useStoreValue } from '../../app/store';

import StepNavigation from '../step-navigation';
import Step from '../step';

const Wizard = ( { children } ) => {
	const [ { currentStep } ] = useStoreValue();
	const steps = children;

	const app = useRef( null );

	useEffect( () => {
		// Query all focusable elements inside current step
		const stepInputs = app.current.querySelectorAll( '.give-obw-step button, .give-obw-step input, .give-obw-step select' );

		// Set tabindex for focusable elements in current step
		stepInputs.forEach( ( element ) => {
			element.setAttribute( 'tabindex', 1 );
		} );

		// Query all focusable step link elements
		const stepLinks = app.current.querySelectorAll( '.give-obw-step-link button' );

		// Set tabindex for step links (in step navigation area)
		stepLinks.forEach( ( element ) => {
			element.setAttribute( 'tabindex', 2 );
		} );

		// Set focus to first element in current step
		stepInputs[ 0 ].focus();
	}, [ currentStep ] );

	return (
		<div className="give-obw" ref={ app }>
			{ steps[ currentStep ].props.showInNavigation && (
				<StepNavigation steps={ steps } />
			) }
			{ steps.map( ( step, index ) => {
				if ( currentStep === index ) {
					return step;
				}
			} ) }
		</div>
	);
};

Wizard.propTypes = {
	children: function( props, propName, componentName ) {
		const prop = props[ propName ];

		let error = null;
		React.Children.forEach( prop, function( child ) {
			if ( child.type !== Step ) {
				error = new Error( '`' + componentName + '` children should be of type `Step`.' );
			}
		} );
		return error;
	},
};

Wizard.propTypes = {
	children: PropTypes.node.isRequired,
};

Wizard.defaultProps = {
	children: null,
};

export default Wizard;
