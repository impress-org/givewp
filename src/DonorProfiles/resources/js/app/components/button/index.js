import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useAccentColor } from '../../hooks';
import './style.scss';

const Button = ( { icon, children, onClick } ) => {
	const accentColor = useAccentColor();
	return (
		<button className="give-donor-profile-button give-donor-profile-button--primary" style={ { background: accentColor } } onClick={ () => onClick() }>
			{ children }{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
		</button>
	);
};
export default Button;
