import React from 'react';
import PropTypes from 'prop-types';
import './style.scss';

import StepLink from '../step-link';
import Step from '../step';

const StepNavigation = ( { steps } ) => {
	const stepLinks = steps.map( ( step, index ) => {
		if ( step.props.showInNavigation === true ) {
			return ( <StepLink title={ step.props.title } step={ index } key={ index } /> );
		}
	} );

	return (
		<div className="give-obw-step-navigation">
			<div className="give-obw-step-navigation__steps-container">
				{ stepLinks }
			</div>
		</div>
	);
};

StepNavigation.propTypes = {
	steps: function( props, propName, componentName ) {
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

StepNavigation.propTypes = {
	steps: PropTypes.array.isRequired,
};

StepNavigation.defaultProps = {
	steps: null,
};

export default StepNavigation;
