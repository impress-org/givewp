import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import './style.scss';

const Heading = ( { icon, children } ) => {
	return (
		<div className="give-donor-profile-heading">
			{ icon && ( <FontAwesomeIcon icon={ icon } className={ icon === 'spinner' ? 'give-donor-profile-heading__spinner' : '' } /> ) }
			{ children }
		</div>
	);
};
export default Heading;
