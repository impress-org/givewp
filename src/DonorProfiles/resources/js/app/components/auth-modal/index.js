import { useState } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

import TextControl from '../text-control';
import Button from '../button';

import { loginWithAPI } from './utils';

import './style.scss';

const AuthModal = () => {
	const [ email, setEmail ] = useState( '' );
	const [ login, setLogin ] = useState( '' );
	const [ password, setPassword ] = useState( '' );
	const [ loginError, setLoginError ] = useState( null );
	const [ loggingIn, setLoggingIn ] = useState( false );

	const handleLogin = async() => {
		setLoggingIn( true );
		const { status, response } = await loginWithAPI( {
			login,
			password,
		} );

		if ( status === 200 ) {
			window.location.reload();
		} else {
			setLoggingIn( false );
			setLoginError( response );
			if ( response === 'unidentified_login' ) {
				setLogin( '' );
				setPassword( '' );
			} else {
				setPassword( '' );
			}
		}
	};

	return (
		<div className="give-donor-profile__auth-modal">
			<div className="give-donor-profile__auth-modal-frame">
				<div className="give-donor-profile__auth-modal-heading">
					{ __( 'Log in to your donor profile', 'give' ) }
				</div>
				<div className="give-donor-profile__auth-modal-content">
					<div className="give-donor-profile__auth-modal-instruction">
						{ __( 'Enter your email below and we\'ll send you a link to access your donor profile', 'give' ) }
					</div>
					<TextControl icon="envelope" value={ email } onChange={ ( value ) => setEmail( value ) } />
					<Button>
						{ __( 'Email Access', 'give' ) }
						<FontAwesomeIcon icon="chevron-right" fixedWidth />
					</Button>
					<div className="give-donor-profile__auth-modal-seperator" />
					<div className="give-donor-profile__auth-modal-instruction">
						{ __( 'Already have an account?', 'give' ) } <br />
						{ __( 'Login below to access your profile', 'give' ) }
					</div>
					<TextControl icon="user" value={ login } onChange={ ( value ) => setLogin( value ) } />
					<TextControl icon="lock" type="password" value={ password } onChange={ ( value ) => setPassword( value ) } />
					<div className="give-donor-profile__auth-modal-row">
						<Button onClick={ () => handleLogin() }>
							{ __( 'Login', 'give' ) }
							<FontAwesomeIcon className={ loggingIn ? 'give-donor-profile__auth-modal-spinner' : '' } icon={ loggingIn ? 'spinner' : 'chevron-right' } fixedWidth />
						</Button>
						{ loginError && (
							<div className="give-donor-profile__auth-modal-error">
								{ loginError }
							</div>
						) }
					</div>
				</div>
			</div>
		</div>
	);
};

export default AuthModal;
