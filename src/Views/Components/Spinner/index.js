import classNames from 'classnames';
import PropTypes from 'prop-types';

import styles from './style.module.scss';

const Spinner = ( { size, ...rest } ) => {
	const spinnerClasses = classNames( {
		[	styles.spinner ]: true,
		[ styles.large ]: size === 'large',
		[ styles.medium ]: size === 'medium',
		[ styles.small ]: size === 'small',
		[ styles.tiny ]: size === 'tiny',
	} );

	return (
		<div className={ spinnerClasses } { ...rest }> </div>
	);
};

Spinner.propTypes = {
	// Spinner size [small, medium, large ]
	size: PropTypes.string,
};

Spinner.defaultProps = {
	size: 'small',
};

export default Spinner;
