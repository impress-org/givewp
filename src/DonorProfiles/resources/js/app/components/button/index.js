import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import './style.scss';

const Button = ( { icon, children, onClick, href, type } ) => {
	const handleHrefClick = ( e ) => {
		e.preventDefault();
		window.parent.location = href;
	};

	if ( href ) {
		return (
			<a className="give-donor-profile-button give-donor-profile-button--primary" onClick={ ( e ) => handleHrefClick( e ) } href={ href }>
				{ children }{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
			</a>
		);
	}
	return (
		<button className="give-donor-profile-button give-donor-profile-button--primary" onClick={ () => onClick() } type={ type }>
			{ children }{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
		</button>
	);
};
export default Button;
