import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import './style.scss';

const Button = ( { icon, children, onClick } ) => {
	return (
		<div className="give-donor-profile-button give-donor-profile-button--primary" onClick={ () => onClick() }>
			{ children }{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
		</div>
	);
};
export default Button;
