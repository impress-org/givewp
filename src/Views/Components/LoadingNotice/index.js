import Spinner from 'GiveComponents/Spinner';
import PropTypes from 'prop-types';

import styles from './style.module.scss';

const LoadingNotice = ( { notice } ) => {
	return (
		<div className={ styles.notice }>
			<div className={ styles.card }>
				<Spinner />
				<h2>{ notice }</h2>
			</div>
		</div>
	);
};

export default LoadingNotice;

LoadingNotice.propTypes = {
	// Notice text
	notice: PropTypes.string.isRequired,
};
