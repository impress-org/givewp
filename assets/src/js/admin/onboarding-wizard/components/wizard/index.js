// Import vendor dependencies
import React, { useRef, useEffect } from 'react';
import PropTypes from 'prop-types';

// Import store dependencies
import { useStoreValue } from '../../app/store';

// Import components
import StepNavigation from '../step-navigation';
import Step from '../step';

// Import styles
import './style.scss';

const Wizard = ( { children } ) => {
	const [ { currentStep } ] = useStoreValue();
	const steps = children;

	useEffect( () => {
		window.scrollTo( 0, 0 );
	}, [ currentStep ] );

	const app = useRef( null );

	return (
		<div className="give-obw" ref={ app }>
			{ steps.map( ( step, index ) => {
				if ( currentStep === index ) {
					return step;
				}
			} ) }
			{ steps[ currentStep ].props.showInNavigation && (
				<StepNavigation steps={ steps } />
			) }
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
