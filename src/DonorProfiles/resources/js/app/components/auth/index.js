import { useState, useEffect } from 'react';
import { useSelector } from 'react-redux';

import AuthModal from '../auth-modal';

import './style.scss';

const Auth = ( { children } ) => {
	const [ loggedIn, setLoggedIn ] = useState( false );
	const id = useSelector( ( state ) => state.id );

	useEffect( () => {
		if ( Number( id ) !== 0 ) {
			setLoggedIn( true );
		} else {
			setLoggedIn( false );
		}
	}, [ id ] );

	return (
		<div className="give-donor-profile__auth">
			{ ! loggedIn && <AuthModal /> }
			<div className="give-donor-profile__auth-wrapper">
				{ children }
			</div>
		</div>
	);
};

export default Auth;
