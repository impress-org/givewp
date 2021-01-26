import { useState } from 'react';
const { __ } = wp.i18n;

import TextControl from '../text-control';
import Button from '../button';

import './style.scss';

const AuthModal = () => {
	const [ email, setEmail ] = useState( '' );
	const [ login, setLogin ] = useState( '' );
	const [ password, setPassword ] = useState( '' );

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
					</Button>
					<div className="give-donor-profile__auth-modal-seperator" />
					<div className="give-donor-profile__auth-modal-instruction">
						{ __( 'Already have an account?', 'give' ) } <br />
						{ __( 'Login below to access your profile', 'give' ) }
					</div>
					<TextControl icon="user" value={ login } onChange={ ( value ) => setLogin( value ) } />
					<TextControl icon="lock" value={ password } onChange={ ( value ) => setPassword( value ) } />
					<Button>
						{ __( 'Login', 'give' ) }
					</Button>
				</div>
			</div>
		</div>
	);
};

export default AuthModal;
