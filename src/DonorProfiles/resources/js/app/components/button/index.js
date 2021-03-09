import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import './style.scss';

const Button = ( { icon, children, onClick, href, type } ) => {
	const handleHrefClick = ( e ) => {
		e.preventDefault();
		window.parent.location = href;
	};

	if ( href ) {
		return (
			<a className="give-donor-profile-button give-donor-profile-button--primary" onClick={ onClick ? ( e ) => handleHrefClick( e ) : null } href={ href }>
				{ children }{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
			</a>
		);
	}
	return (
		<button className="give-donor-profile-button give-donor-profile-button--primary" onClick={ onClick ? () => onClick() : null } type={ type }>
			{ children }{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
		</button>
	);
};
export default Button;
