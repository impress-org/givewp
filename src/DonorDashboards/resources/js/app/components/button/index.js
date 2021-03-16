import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import './style.scss';

const Button = ( { icon, children, onClick, href, type } ) => {
	const handleHrefClick = ( e ) => {
		e.preventDefault();
		window.parent.location = href;
	};

	if ( href ) {
		return (
			<a className="give-donor-dashboard-button give-donor-dashboard-button--primary" onClick={ ( e ) => handleHrefClick( e ) } href={ href }>
				{ children }{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
			</a>
		);
	}
	return (
		<button className="give-donor-dashboard-button give-donor-dashboard-button--primary" onClick={ onClick ? () => onClick() : null } type={ type }>
			{ children }{ icon && ( <FontAwesomeIcon icon={ icon } /> ) }
		</button>
	);
};
export default Button;
