import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import './style.scss';

const Heading = ( { icon, children } ) => {
	return (
		<div className="give-donor-dashboard-heading">
			{ icon && ( <FontAwesomeIcon icon={ icon } className={ icon === 'spinner' ? 'give-donor-dashboard-heading__spinner' : '' } /> ) }
			{ children }
		</div>
	);
};
export default Heading;
