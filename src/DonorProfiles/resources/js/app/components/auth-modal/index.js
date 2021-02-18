import { useState, Fragment } from 'react';
import { useAccentColor } from '../../hooks';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

import TextControl from '../text-control';
import Button from '../button';

import { loginWithAPI, verifyEmailWithAPI } from './utils';
import { getWindowData } from '../../utils';

import './style.scss';

const AuthModal = () => {
	const accentColor = useAccentColor();

	const [ email, setEmail ] = useState( '' );
	const [ login, setLogin ] = useState( '' );
	const [ password, setPassword ] = useState( '' );
	const [ loginError, setLoginError ] = useState( null );
	const [ loggingIn, setLoggingIn ] = useState( false );
	const [ verifyingEmail, setVerifyingEmail ] = useState( false );
	const [ emailSent, setEmailSent ] = useState( false );
	const [ emailError, setEmailError ] = useState( null );
	const emailAccessEnabled = getWindowData( 'emailAccessEnabled' );

	const handleLogin = async() => {
		if ( login && password ) {
			setLoggingIn( true );
			// eslint-disable-next-line camelcase
			const { status, response, body_response } = await loginWithAPI( {
				login,
				password,
			} );

			if ( status === 200 ) {
				window.location.reload();
			} else {
				setLoggingIn( false );
				setLoginError( body_response.message );
				if ( response === 'unidentified_login' ) {
					setLogin( '' );
					setPassword( '' );
				} else {
					setPassword( '' );
				}
			}
		}
	};

	const handleVerifyEmail = async() => {
		if ( email ) {
			setVerifyingEmail( true );
			// eslint-disable-next-line camelcase
			const { status, body_response } = await verifyEmailWithAPI( {
				email,
			} );

			if ( status === 200 ) {
				setVerifyingEmail( false );
				setEmailSent( true );
			} else {
				setVerifyingEmail( false );
				setEmailError( body_response.message );
				setEmail( '' );
			}
		}
	};

	return (
		<div className="give-donor-profile__auth-modal">
			<div className="give-donor-profile__auth-modal-frame">
				<div className="give-donor-profile__auth-modal-heading" style={ { background: accentColor } }>
					{ __( 'Log in to your donor profile', 'give' ) }
				</div>
				<div className="give-donor-profile__auth-modal-content">
					{ emailAccessEnabled && (
						<Fragment>
							<div className="give-donor-profile__auth-modal-instruction">
								{ __( 'Enter your email below and we\'ll send you a link to access your donor profile', 'give' ) }
							</div>
							<TextControl icon="envelope" value={ email } onChange={ ( value ) => setEmail( value ) } />
							<div className="give-donor-profile__auth-modal-row">
								<Button onClick={ () => handleVerifyEmail() }>
									{ emailSent === false ? __( 'Verify Email', 'give' ) : __( 'Email Sent', 'give' ) }
									{ emailSent === false && <FontAwesomeIcon className={ verifyingEmail ? 'give-donor-profile__auth-modal-spinner' : '' } icon={ verifyingEmail ? 'spinner' : 'chevron-right' } fixedWidth /> }
								</Button>
								{ emailError && (
									<div className="give-donor-profile__auth-modal-error">
										{ emailError }
									</div>
								) }
							</div>
							<div className="give-donor-profile__auth-modal-seperator" />
						</Fragment>
					) }
					<div className="give-donor-profile__auth-modal-instruction">
						{ emailAccessEnabled && (
							<Fragment>
								{ __( 'Already have an account?', 'give' ) } <br />
							</Fragment>
						) }
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
