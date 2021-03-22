import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import './style.scss';

const Badge = ( { icon, label } ) => {
	return (
		<div className="give-donor-dashboard-badge">
			{ label }
			{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
		</div>
	);
};

export default Badge;
