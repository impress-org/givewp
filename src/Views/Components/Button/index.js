import PropTypes from 'prop-types';
import classNames from 'classnames';

import styles from './style.module.scss';

const Button = ( { children, onClick, icon } ) => {
	const classes = classNames( {
		[ styles.button ]: true,
		[ styles.icon ]: icon,
	}	);

	return (
		<button className={ classes } onClick={ onClick }>
			{ children }
		</button>
	);
};

Button.propTypes = {
	// Button children
	children: PropTypes.node,
	// Fired on button click
	onClick: PropTypes.func,
	// Icon
	icon: PropTypes.bool,
};

export default Button;
